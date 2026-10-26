<?php

namespace App\Http\Controllers;

use App\Models\KategoriSumber;
use App\Models\KodeBidang;
use App\Models\Surat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Rekap & laporan surat per periode.
 */
class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        return view('laporan.index', $this->data($request));
    }

    public function cetak(Request $request): View
    {
        return view('laporan.cetak', $this->data($request, false));
    }

    public function export(Request $request): StreamedResponse
    {
        $d = $this->data($request, false);
        $nama = 'rekap-surat-' . $d['dari']->format('Ymd') . '-' . $d['sampai']->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($d) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar terbaca benar di Excel
            fputcsv($out, ['No', 'Nomor Surat', 'Tanggal Surat', 'Perihal', 'Pengirim', 'Unit Pengirim', 'Sifat',
                'Kode Sumber', 'Kode Bidang', 'Status', 'Tanggal Masuk Sistem'], ';');
            foreach ($d['surat'] as $i => $s) {
                fputcsv($out, [
                    $i + 1, $s->nomor_surat, $s->tanggal_surat?->format('d/m/Y'), $s->perihal, $s->pengirim?->nama,
                    $s->pengirim?->unit?->nama_unit, $s->sifat_surat, $s->kode_sumber, $s->kode_bidang,
                    $s->status_terkini, $s->created_at?->format('d/m/Y H:i'),
                ], ';');
            }
            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function data(Request $request, bool $paginate = true): array
    {
        $dari = $request->filled('dari') ? Carbon::parse($request->dari)->startOfDay() : now()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->sampai)->endOfDay() : now()->endOfDay();

        $base = fn (): Builder => Surat::query()
            ->whereBetween('created_at', [$dari, $sampai])
            ->when($request->status, fn ($q, $v) => $q->where('status_terkini', $v))
            ->when($request->kode_sumber, fn ($q, $v) => $q->where('kode_sumber', $v))
            ->when($request->kode_bidang, fn ($q, $v) => $q->where('kode_bidang', $v));

        $query = $base()->with('pengirim.unit')->orderBy('created_at');

        return [
            'dari' => $dari,
            'sampai' => $sampai,
            'total' => $base()->count(),
            'urgent' => $base()->where('sifat_surat', 'Urgent')->count(),
            'perStatus' => $base()->selectRaw('status_terkini, count(*) as jumlah')->groupBy('status_terkini')->pluck('jumlah', 'status_terkini'),
            'perSumber' => $base()->selectRaw('kode_sumber, count(*) as jumlah')->groupBy('kode_sumber')->pluck('jumlah', 'kode_sumber'),
            'perBidang' => $base()->selectRaw('kode_bidang, count(*) as jumlah')->groupBy('kode_bidang')->pluck('jumlah', 'kode_bidang'),
            'surat' => $paginate ? $query->paginate(15)->withQueryString() : $query->get(),
            'kategori' => KategoriSumber::orderBy('kode_sumber')->get(),
            'bidang' => KodeBidang::orderBy('kode_bidang')->get(),
        ];
    }
}
