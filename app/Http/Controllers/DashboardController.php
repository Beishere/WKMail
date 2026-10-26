<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\RiwayatStatus;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('role', 'unit');
        $base = fn () => Surat::query()->visibleTo($user);

        // Distribusi status surat yang terlihat oleh pengguna
        $perStatus = $base()->selectRaw('status_terkini, count(*) as jumlah')
            ->groupBy('status_terkini')->pluck('jumlah', 'status_terkini');

        // Tren 6 bulan terakhir
        $tren = collect(range(5, 0))->map(function ($i) use ($base) {
            $bulan = now()->startOfMonth()->subMonths($i);

            return [
                'label' => $bulan->translatedFormat('M Y'),
                'jumlah' => $base()->whereBetween('created_at', [$bulan, $bulan->copy()->endOfMonth()])->count(),
            ];
        });

        $data = compact('user', 'perStatus', 'tren');

        if ($user->isAdmin()) {
            $data['stats'] = [
                ['label' => 'Menunggu Validasi', 'value' => $perStatus[Surat::TERKIRIM] ?? 0, 'color' => 'sky', 'icon' => 'inbox'],
                ['label' => 'Perlu Revisi', 'value' => $perStatus[Surat::PERLU_REVISI] ?? 0, 'color' => 'rose', 'icon' => 'arrow-uturn'],
                ['label' => 'Dalam Proses', 'value' => $base()->whereIn('status_terkini', [Surat::DIVALIDASI, Surat::DIBACA, Surat::DIDISPOSISI, Surat::DIPROSES, Surat::PENDING])->count(), 'color' => 'amber', 'icon' => 'clock'],
                ['label' => 'Selesai', 'value' => ($perStatus[Surat::SELESAI] ?? 0) + ($perStatus[Surat::DIARSIPKAN] ?? 0), 'color' => 'emerald', 'icon' => 'check-circle'],
            ];
            $data['antrian'] = Surat::with('pengirim.unit')->where('status_terkini', Surat::TERKIRIM)
                ->orderByRaw("CASE WHEN sifat_surat = 'Urgent' THEN 0 ELSE 1 END")->oldest()->limit(6)->get();
            $data['antrianJudul'] = 'Antrian Validasi Surat';
            $data['perSumber'] = Surat::whereNotNull('kode_sumber')->selectRaw('kode_sumber, count(*) as jumlah')
                ->groupBy('kode_sumber')->pluck('jumlah', 'kode_sumber');
        } elseif ($user->isRektor()) {
            $data['stats'] = [
                ['label' => 'Menunggu Disposisi', 'value' => $base()->whereIn('status_terkini', [Surat::DIVALIDASI, Surat::DIBACA])->count(), 'color' => 'indigo', 'icon' => 'inbox'],
                ['label' => 'Urgent', 'value' => $base()->whereIn('status_terkini', [Surat::DIVALIDASI, Surat::DIBACA])->where('sifat_surat', 'Urgent')->count(), 'color' => 'rose', 'icon' => 'bolt'],
                ['label' => 'Sedang Ditindaklanjuti', 'value' => $base()->whereIn('status_terkini', [Surat::DIDISPOSISI, Surat::DIPROSES, Surat::PENDING])->count(), 'color' => 'amber', 'icon' => 'clock'],
                ['label' => 'Selesai', 'value' => ($perStatus[Surat::SELESAI] ?? 0) + ($perStatus[Surat::DIARSIPKAN] ?? 0), 'color' => 'emerald', 'icon' => 'check-circle'],
            ];
            $data['antrian'] = Surat::with('pengirim.unit')->whereIn('status_terkini', [Surat::DIVALIDASI, Surat::DIBACA])
                ->orderByRaw("CASE WHEN sifat_surat = 'Urgent' THEN 0 ELSE 1 END")->oldest('tanggal_diterima')->limit(6)->get();
            $data['antrianJudul'] = 'Surat Menunggu Disposisi';
        } elseif ($user->isWakilRektor() || $user->isUnitTeknis()) {
            $disp = fn () => Disposisi::query()->untuk($user);
            $data['stats'] = [
                ['label' => 'Disposisi Baru', 'value' => $disp()->where('status', Surat::TERKIRIM)->count(), 'color' => 'violet', 'icon' => 'inbox'],
                ['label' => 'Sedang Diproses', 'value' => $disp()->whereIn('status', [Surat::DIBACA, Surat::DIPROSES, Surat::DIDISPOSISI])->count(), 'color' => 'amber', 'icon' => 'clock'],
                ['label' => 'Pending', 'value' => $disp()->where('status', Surat::PENDING)->count(), 'color' => 'orange', 'icon' => 'pause'],
                ['label' => 'Selesai', 'value' => $disp()->where('status', Surat::SELESAI)->count(), 'color' => 'emerald', 'icon' => 'check-circle'],
            ];
            $data['disposisiAktif'] = $disp()->with('surat.pengirim.unit', 'pemberi')
                ->whereNotIn('status', [Surat::SELESAI])->orderBy('batas_waktu')->latest('tanggal_disposisi')->limit(6)->get();
        } else {
            $data['stats'] = [
                ['label' => 'Total Surat Saya', 'value' => $base()->count(), 'color' => 'brand', 'icon' => 'document'],
                ['label' => 'Perlu Revisi', 'value' => $perStatus[Surat::PERLU_REVISI] ?? 0, 'color' => 'rose', 'icon' => 'arrow-uturn'],
                ['label' => 'Dalam Proses', 'value' => $base()->whereNotIn('status_terkini', [Surat::PERLU_REVISI, Surat::SELESAI, Surat::DIARSIPKAN])->count(), 'color' => 'amber', 'icon' => 'clock'],
                ['label' => 'Selesai', 'value' => ($perStatus[Surat::SELESAI] ?? 0) + ($perStatus[Surat::DIARSIPKAN] ?? 0), 'color' => 'emerald', 'icon' => 'check-circle'],
            ];
            $data['antrian'] = $base()->with('pengirim.unit')->latest()->limit(6)->get();
            $data['antrianJudul'] = 'Surat Terbaru Saya';
        }

        $data['aktivitas'] = RiwayatStatus::with('surat', 'pengguna')
            ->whereIn('id_surat', $base()->select('id_surat'))
            ->latest('waktu')->latest('id_riwayat')->limit(6)->get();

        return view('dashboard', $data);
    }
}
