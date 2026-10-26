<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Services\DisposisiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Rektor — pemberi disposisi ke Wakil Rektor.
 */
class RektorController extends Controller
{
    public function __construct(protected DisposisiService $service)
    {
    }

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'menunggu');

        $surat = Surat::with('pengirim.unit', 'bidang', 'unitSaran')
            ->when($tab === 'menunggu', fn ($q) => $q->whereIn('status_terkini', [Surat::DIVALIDASI, Surat::DIBACA]))
            ->when($tab === 'didisposisi', fn ($q) => $q->whereIn('status_terkini', [Surat::DIDISPOSISI, Surat::DIPROSES, Surat::PENDING]))
            ->when($tab === 'selesai', fn ($q) => $q->whereIn('status_terkini', [Surat::SELESAI, Surat::DIARSIPKAN]))
            ->cari($request->q)
            ->orderByRaw("CASE WHEN sifat_surat = 'Urgent' THEN 0 ELSE 1 END")
            ->latest('tanggal_diterima')
            ->paginate(10)->withQueryString();

        $jumlahMenunggu = Surat::whereIn('status_terkini', [Surat::DIVALIDASI, Surat::DIBACA])->count();

        return view('rektor.index', compact('surat', 'tab', 'jumlahMenunggu'));
    }

    public function disposisi(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless(in_array($surat->status_terkini, [Surat::DIVALIDASI, Surat::DIBACA], true), 422,
            'Surat ini tidak sedang menunggu disposisi.');

        $data = $request->validate([
            'unit_tujuan' => ['required', 'array', 'min:1'],
            'unit_tujuan.*' => [Rule::exists('unit', 'id_unit')->where('jenis_unit', 'wakil_rektor')],
            'instruksi' => ['required', 'string', 'max:2000'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'batas_waktu' => ['nullable', 'date', 'after_or_equal:today'],
        ], [], ['unit_tujuan' => 'Wakil Rektor tujuan']);

        $this->service->disposisiRektor($surat, $request->user(), $data);

        return redirect()->route('rektor.index')->with('success', "Disposisi surat {$surat->nomor_surat} berhasil dikirim.");
    }

    public function arsip(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless(in_array($surat->status_terkini, [Surat::DIVALIDASI, Surat::DIBACA], true), 422);

        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);
        $this->service->arsipkan($surat, $request->user(), $data['catatan'] ?? null);

        return redirect()->route('rektor.index')->with('success', "Surat {$surat->nomor_surat} diarsipkan sebagai informasi.");
    }
}
