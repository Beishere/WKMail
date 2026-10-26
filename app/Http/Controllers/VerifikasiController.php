<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Services\DisposisiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Surat Rektorat — filter utama: validasi kelengkapan, klasifikasi kode, menentukan tujuan disposisi.
 */
class VerifikasiController extends Controller
{
    public function __construct(protected DisposisiService $service)
    {
    }

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'antrian');

        $surat = Surat::with('pengirim.unit', 'lampiran')
            ->when($tab === 'antrian', fn ($q) => $q->where('status_terkini', Surat::TERKIRIM))
            ->when($tab === 'revisi', fn ($q) => $q->where('status_terkini', Surat::PERLU_REVISI))
            ->when($tab === 'tervalidasi', fn ($q) => $q->whereNotIn('status_terkini', [Surat::TERKIRIM, Surat::PERLU_REVISI]))
            ->cari($request->q)
            ->orderByRaw("CASE WHEN sifat_surat = 'Urgent' THEN 0 ELSE 1 END")
            ->when($tab === 'antrian', fn ($q) => $q->oldest(), fn ($q) => $q->latest('updated_at'))
            ->paginate(10)->withQueryString();

        $jumlah = [
            'antrian' => Surat::where('status_terkini', Surat::TERKIRIM)->count(),
            'revisi' => Surat::where('status_terkini', Surat::PERLU_REVISI)->count(),
        ];

        return view('verifikasi.index', compact('surat', 'tab', 'jumlah'));
    }

    public function validasi(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($surat->status_terkini === Surat::TERKIRIM, 422, 'Surat ini tidak dalam antrian validasi.');

        $data = $request->validate([
            'kode_sumber' => ['required', 'exists:kategori_sumber,kode_sumber'],
            'kode_bidang' => ['required', 'exists:kode_bidang,kode_bidang'],
            'id_unit_saran' => ['nullable', 'exists:unit,id_unit'],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->validasi($surat, $request->user(), $data);

        return redirect()->route('verifikasi.index')
            ->with('success', "Surat {$surat->nomor_surat} divalidasi dan diteruskan ke Rektor.");
    }

    public function tolak(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($surat->status_terkini === Surat::TERKIRIM, 422, 'Surat ini tidak dalam antrian validasi.');

        $data = $request->validate([
            'catatan_revisi' => ['required', 'string', 'max:1000'],
        ]);

        $this->service->tolak($surat, $request->user(), $data['catatan_revisi']);

        return redirect()->route('verifikasi.index')
            ->with('success', "Surat {$surat->nomor_surat} dikembalikan ke pengirim untuk revisi.");
    }
}
