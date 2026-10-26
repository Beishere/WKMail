<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Surat;
use App\Services\DisposisiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Wakil Rektor (meneruskan ke Unit Teknis) & Unit Teknis (menindaklanjuti surat).
 */
class DisposisiController extends Controller
{
    public function __construct(protected DisposisiService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->get('tab', 'aktif');

        $disposisi = Disposisi::query()->untuk($user)
            ->with('surat.pengirim.unit', 'pemberi', 'unitTujuan')
            ->when($tab === 'aktif', fn ($q) => $q->whereNotIn('status', [Surat::SELESAI, Surat::DIDISPOSISI]))
            ->when($tab === 'diteruskan', fn ($q) => $q->where('status', Surat::DIDISPOSISI))
            ->when($tab === 'selesai', fn ($q) => $q->where('status', Surat::SELESAI))
            ->when($request->q, fn ($q, $kw) => $q->whereHas('surat', fn ($s) => $s->cari($kw)))
            ->orderByRaw("CASE status WHEN 'Terkirim' THEN 0 WHEN 'Pending' THEN 1 ELSE 2 END")
            ->latest('tanggal_disposisi')
            ->paginate(10)->withQueryString();

        $jumlahBaru = Disposisi::query()->untuk($user)->where('status', Surat::TERKIRIM)->count();

        return view('disposisi.index', compact('disposisi', 'tab', 'jumlahBaru'));
    }

    public function teruskan(Request $request, Disposisi $disposisi): RedirectResponse
    {
        $this->pastikanMilik($request, $disposisi);
        abort_if(in_array($disposisi->status, [Surat::SELESAI, Surat::DIDISPOSISI], true), 422, 'Disposisi ini sudah diteruskan/selesai.');

        $data = $request->validate([
            'unit_tujuan' => ['required', 'array', 'min:1'],
            'unit_tujuan.*' => [Rule::exists('unit', 'id_unit')->where('jenis_unit', 'unit_teknis')],
            'instruksi' => ['required', 'string', 'max:2000'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'batas_waktu' => ['nullable', 'date', 'after_or_equal:today'],
        ], [], ['unit_tujuan' => 'Unit Teknis tujuan']);

        $this->service->teruskan($disposisi, $request->user(), $data);

        return redirect()->route('disposisi.index')->with('success', 'Disposisi berhasil diteruskan ke Unit Teknis.');
    }

    public function progres(Request $request, Disposisi $disposisi): RedirectResponse
    {
        $this->pastikanMilik($request, $disposisi);
        abort_if(in_array($disposisi->status, [Surat::SELESAI, Surat::DIDISPOSISI], true), 422);

        $data = $request->validate([
            'status' => ['required', Rule::in([Surat::DIPROSES, Surat::PENDING])],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->updateProgres($disposisi, $request->user(), $data['status'], $data['keterangan'] ?? null);

        return back()->with('success', "Status disposisi diperbarui menjadi {$data['status']}.");
    }

    public function selesai(Request $request, Disposisi $disposisi): RedirectResponse
    {
        $this->pastikanMilik($request, $disposisi);
        abort_if(in_array($disposisi->status, [Surat::SELESAI, Surat::DIDISPOSISI], true), 422);

        $request->validate([
            'keterangan' => ['required', 'string', 'max:1000'],
            'bukti' => ['nullable', 'array', 'max:5'],
            'bukti.*' => SuratController::LAMPIRAN_RULES,
        ], [], ['keterangan' => 'laporan tindak lanjut']);

        $this->service->selesaikan($disposisi, $request->user(), $request->keterangan, $request->file('bukti', []));

        return redirect()->route('disposisi.index')->with('success', 'Tindak lanjut disposisi ditandai SELESAI.');
    }

    protected function pastikanMilik(Request $request, Disposisi $disposisi): void
    {
        abort_unless($disposisi->isUntuk($request->user()), 403, 'Disposisi ini bukan untuk Anda.');
    }
}
