<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\KategoriSumber;
use App\Models\KodeBidang;
use App\Models\Lampiran;
use App\Models\Notifikasi;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\Unit;
use App\Services\DisposisiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuratController extends Controller
{
    public const LAMPIRAN_RULES = ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip'];

    public function __construct(protected DisposisiService $service)
    {
    }

    /** Daftar surat sesuai hak akses (arsip / surat saya). */
    public function index(Request $request): View
    {
        $user = $request->user();

        $surat = Surat::query()->visibleTo($user)
            ->with('pengirim.unit', 'bidang')
            ->cari($request->q)
            ->when($request->status, fn ($q, $v) => $q->where('status_terkini', $v))
            ->when($request->sifat, fn ($q, $v) => $q->where('sifat_surat', $v))
            ->when($request->kode_sumber, fn ($q, $v) => $q->where('kode_sumber', $v))
            ->when($request->kode_bidang, fn ($q, $v) => $q->where('kode_bidang', $v))
            ->when($request->dari, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->sampai, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('surat.index', [
            'surat' => $surat,
            'kategori' => KategoriSumber::orderBy('kode_sumber')->get(),
            'bidang' => KodeBidang::orderBy('kode_bidang')->get(),
        ]);
    }

    public function create(): View
    {
        return view('surat.form', [
            'surat' => new Surat(['tanggal_surat' => now(), 'sifat_surat' => 'Biasa']),
            'templates' => TemplateSurat::orderBy('nama_template')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_template' => ['nullable', 'exists:template_surat,id_template'],
            'nomor_surat' => ['nullable', 'string', 'max:100', 'unique:surat,nomor_surat'],
            'perihal' => ['required', 'string', 'max:255'],
            'isi_surat' => ['nullable', 'string'],
            'tanggal_surat' => ['required', 'date'],
            'sifat_surat' => ['required', Rule::in(['Biasa', 'Urgent'])],
            'lampiran' => ['nullable', 'array', 'max:5'],
            'lampiran.*' => self::LAMPIRAN_RULES,
        ]);

        $surat = $this->service->kirimSurat($request->user(), $data, $request->file('lampiran', []));

        return redirect()->route('surat.show', $surat)
            ->with('success', "Surat berhasil dikirim dengan nomor {$surat->nomor_surat}. Status: Terkirim.");
    }

    public function show(Request $request, Surat $surat): View
    {
        $user = $request->user();
        abort_unless($surat->isVisibleTo($user), 403, 'Anda tidak memiliki akses ke surat ini.');

        // Efek samping "membuka surat"
        if ($user->isRektor()) {
            $this->service->tandaiDibaca($surat, $user);
        }
        $disposisiSaya = collect();
        if ($user->isWakilRektor() || $user->isUnitTeknis()) {
            $disposisiSaya = $surat->disposisi()->untuk($user)->with('pemberi', 'unitTujuan', 'turunan.unitTujuan', 'bukti')->get();
            $disposisiSaya->each(fn (Disposisi $d) => $this->service->bacaDisposisi($d->setRelation('surat', $surat), $user));
            $disposisiSaya = $disposisiSaya->map->fresh(['pemberi', 'unitTujuan', 'turunan.unitTujuan', 'bukti']);
        }
        Notifikasi::where('id_pengguna', $user->id_pengguna)->where('id_surat', $surat->id_surat)
            ->update(['sudah_dibaca' => true]);

        $surat->refresh()->load([
            'pengirim.unit', 'kategoriSumber', 'bidang', 'template', 'unitSaran', 'lampiran', 'bukti.disposisi.unitTujuan',
            'riwayat.pengguna.role',
        ]);

        $pohonDisposisi = $surat->disposisi()
            ->whereNull('id_induk_disposisi')
            ->with('pemberi', 'penerima', 'unitTujuan', 'bukti', 'turunan.pemberi', 'turunan.unitTujuan', 'turunan.bukti')
            ->orderBy('tanggal_disposisi')->get();

        $unitWr = Unit::where('jenis_unit', 'wakil_rektor')->orderBy('nama_unit')->get();

        return view('surat.show', [
            'surat' => $surat,
            'pohonDisposisi' => $pohonDisposisi,
            'disposisiSaya' => $disposisiSaya,
            'kategori' => KategoriSumber::orderBy('kode_sumber')->get(),
            'bidang' => KodeBidang::with('unitWr')->orderBy('kode_bidang')->get(),
            'unitWr' => $unitWr,
            'unitTeknis' => Unit::where('jenis_unit', 'unit_teknis')->orderBy('nama_unit')->get(),
        ]);
    }

    public function edit(Request $request, Surat $surat): View
    {
        $this->pastikanBisaRevisi($request, $surat);

        return view('surat.form', [
            'surat' => $surat->load('lampiran'),
            'templates' => TemplateSurat::orderBy('nama_template')->get(),
        ]);
    }

    public function update(Request $request, Surat $surat): RedirectResponse
    {
        $this->pastikanBisaRevisi($request, $surat);

        $data = $request->validate([
            'nomor_surat' => ['nullable', 'string', 'max:100', Rule::unique('surat', 'nomor_surat')->ignore($surat->id_surat, 'id_surat')],
            'perihal' => ['required', 'string', 'max:255'],
            'isi_surat' => ['nullable', 'string'],
            'tanggal_surat' => ['required', 'date'],
            'sifat_surat' => ['required', Rule::in(['Biasa', 'Urgent'])],
            'lampiran' => ['nullable', 'array', 'max:5'],
            'lampiran.*' => self::LAMPIRAN_RULES,
        ]);

        if ($request->user()->isAdmin() && $surat->status_terkini !== Surat::PERLU_REVISI) {
            $surat->update(collect($data)->only(['nomor_surat', 'perihal', 'isi_surat', 'tanggal_surat', 'sifat_surat'])->filter(fn ($v) => $v !== null)->all());
            return redirect()->route('surat.show', $surat)->with('success', 'Data surat berhasil diperbarui.');
        }

        $this->service->kirimUlang($surat, $request->user(), $data, $request->file('lampiran', []));

        return redirect()->route('surat.show', $surat)->with('success', 'Surat berhasil diperbarui dan dikirim ke Rektorat.');
    }

    public function destroy(Request $request, Surat $surat): RedirectResponse
    {
        $this->pastikanBisaRevisi($request, $surat);

        foreach ($surat->semuaLampiran as $lamp) {
            Storage::disk('local')->delete($lamp->path_file);
        }
        $surat->delete();

        return redirect()->route('surat.index')->with('success', 'Surat berhasil dihapus.');
    }

    public function hapusLampiran(Request $request, Lampiran $lampiran): RedirectResponse
    {
        $this->pastikanBisaRevisi($request, $lampiran->surat);
        abort_unless($lampiran->jenis === 'lampiran', 403);

        Storage::disk('local')->delete($lampiran->path_file);
        $lampiran->delete();

        return back()->with('success', 'Lampiran dihapus.');
    }

    public function unduh(Request $request, Lampiran $lampiran): StreamedResponse
    {
        abort_unless($lampiran->surat->isVisibleTo($request->user()), 403);
        abort_unless(Storage::disk('local')->exists($lampiran->path_file), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->download($lampiran->path_file, $lampiran->nama_file);
    }

    /** Pengisian perihal & isi surat otomatis dari template. */
    public function renderTemplate(Request $request, TemplateSurat $template): JsonResponse
    {
        $user = $request->user()->load('unit');

        return response()->json([
            'perihal' => $template->perihal_default,
            'isi' => $template->render($user),
            'kop_url' => $template->path_kop ? route('template.kop', $template) : null,
            'file_template_url' => $template->path_file_template ? route('template.unduh', $template) : null,
            'nama_file_template' => $template->nama_file_template,
        ]);
    }

    public function kop(TemplateSurat $template)
    {
        abort_unless($template->path_kop && Storage::disk('local')->exists($template->path_kop), 404);

        return Storage::disk('local')->response($template->path_kop);
    }

    public function unduhTemplate(TemplateSurat $template): StreamedResponse
    {
        abort_unless($template->path_file_template && Storage::disk('local')->exists($template->path_file_template), 404);

        return Storage::disk('local')->download($template->path_file_template, $template->nama_file_template ?? 'template-surat.docx');
    }

    protected function pastikanBisaRevisi(Request $request, Surat $surat): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }

        abort_unless(
            $surat->id_pengirim === $user->id_pengguna
                && in_array($surat->status_terkini, [Surat::TERKIRIM, Surat::PERLU_REVISI], true),
            403,
            'Surat hanya dapat diubah/dihapus oleh pengirim sebelum divalidasi atau saat berstatus Perlu Revisi.'
        );
    }
}
