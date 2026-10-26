<?php

namespace Database\Seeders;

use App\Models\Disposisi;
use App\Models\Pengguna;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\Unit;
use App\Services\DisposisiService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Contoh surat pada berbagai tahap alur agar dashboard & tracking langsung terisi saat demo.
 */
class DemoSuratSeeder extends Seeder
{
    protected DisposisiService $svc;

    public function run(DisposisiService $svc): void
    {
        $this->svc = $svc;

        $u = fn (string $email) => Pengguna::where('email', $email)->firstOrFail();
        $unit = fn (string $prefix) => Unit::where('nama_unit', 'like', $prefix . '%')->firstOrFail()->id_unit;

        $admin = $u('admin@widyakarya.ac.id');
        $rektor = $u('rektor@widyakarya.ac.id');
        $wr1 = $u('wr1ukwk@widyakarya.ac.id');
        $wr2 = $u('wr2ukwk@widyakarya.ac.id');
        $wr3 = $u('wr3ukwk@widyakarya.ac.id');
        $bau = $u('bau@widyakarya.ac.id');
        $bka = $u('bka@widyakarya.ac.id');
        $baa = $u('baa@widyakarya.ac.id');
        $tpl = fn (string $nama) => TemplateSurat::where('nama_template', $nama)->first();

        // 1) Selesai penuh: FT -> Admin -> Rektor -> WR II -> BAU -> Selesai
        $s = $this->kirim($u('teknik@widyakarya.ac.id'), 'Permohonan Peminjaman Aula St. Thomas Aquinas untuk Yudisium Fakultas Teknik', 'Urgent', $tpl('Peminjaman Sarana & Prasarana'), now()->subMonths(2)->setTime(8, 30));
        $this->at(now()->subMonths(2)->setTime(10, 0), fn () => $svc->validasi($s, $admin, ['kode_sumber' => 'B', 'kode_bidang' => 'D', 'id_unit_saran' => $unit('Wakil Rektor II')]));
        $this->at(now()->subMonths(2)->setTime(13, 0), fn () => $svc->tandaiDibaca($s->fresh(), $rektor));
        $this->at(now()->subMonths(2)->setTime(13, 15), fn () => $svc->disposisiRektor($s->fresh(), $rektor, ['unit_tujuan' => [$unit('Wakil Rektor II')], 'instruksi' => 'Mohon ditindaklanjuti dan dikoordinasikan dengan BAU.']));
        $d = Disposisi::where('id_surat', $s->id_surat)->first();
        $this->at(now()->subMonths(2)->addDay()->setTime(9, 0), fn () => $svc->bacaDisposisi($d, $wr2));
        $this->at(now()->subMonths(2)->addDay()->setTime(9, 30), fn () => $svc->teruskan($d->fresh(), $wr2, ['unit_tujuan' => [$unit('BAU')], 'instruksi' => 'Siapkan aula dan perlengkapan sesuai permohonan.']));
        $d2 = Disposisi::where('id_induk_disposisi', $d->id_disposisi)->first();
        $this->at(now()->subMonths(2)->addDays(2)->setTime(8, 0), fn () => $svc->updateProgres($d2, $bau, Surat::DIPROSES, 'Pengecekan jadwal aula.'));
        $this->at(now()->subMonths(2)->addDays(3)->setTime(15, 0), fn () => $svc->selesaikan($d2->fresh(), $bau, 'Aula telah dijadwalkan dan perlengkapan disiapkan.'));

        // 2) Diproses: HMJ SI eCommerce -> ... -> WR III -> BKA Diproses
        $s = $this->kirim($u('hmjsi@widyakarya.ac.id'), 'Permohonan Izin Seminar Nasional Sistem Informasi eCommerce', 'Biasa', $tpl('Permohonan Izin Kegiatan'), now()->subDays(6)->setTime(9, 0));
        $this->at(now()->subDays(6)->setTime(11, 0), fn () => $svc->validasi($s, $admin, ['kode_sumber' => 'B', 'kode_bidang' => 'K', 'id_unit_saran' => $unit('Wakil Rektor III')]));
        $this->at(now()->subDays(5)->setTime(8, 0), fn () => $svc->tandaiDibaca($s->fresh(), $rektor));
        $this->at(now()->subDays(5)->setTime(8, 20), fn () => $svc->disposisiRektor($s->fresh(), $rektor, ['unit_tujuan' => [$unit('Wakil Rektor III')], 'instruksi' => 'Setujui dan dampingi pelaksanaan.', 'batas_waktu' => now()->addDays(7)->toDateString()]));
        $d = Disposisi::where('id_surat', $s->id_surat)->first();
        $this->at(now()->subDays(4)->setTime(10, 0), fn () => $svc->teruskan($d, $wr3, ['unit_tujuan' => [$unit('BKA')], 'instruksi' => 'Fasilitasi perizinan dan pendampingan kegiatan kemahasiswaan.', 'batas_waktu' => now()->addDays(5)->toDateString()]));
        $d2 = Disposisi::where('id_induk_disposisi', $d->id_disposisi)->first();
        $this->at(now()->subDays(3)->setTime(13, 0), fn () => $svc->updateProgres($d2, $bka, Surat::DIPROSES, 'Sedang menyiapkan surat izin kegiatan kemahasiswaan.'));

        // 3) Divalidasi, menunggu Rektor (urgent)
        $s = $this->kirim($u('yayasan@widyakarya.ac.id'), 'Undangan Rapat Koordinasi Rencana Kerja & Anggaran Tahunan Yayasan PTK Adisucipto', 'Urgent', $tpl('Undangan'), now()->subDay()->setTime(14, 0));
        $this->at(now()->subDay()->setTime(15, 0), fn () => $svc->validasi($s, $admin, ['kode_sumber' => 'A', 'kode_bidang' => 'B', 'id_unit_saran' => $unit('Wakil Rektor II'), 'catatan_admin' => 'Rapat dijadwalkan minggu depan bersama Pembina Yayasan.']));

        // 4) Terkirim, menunggu validasi admin
        $this->kirim($u('mitra@institusi.co.id'), 'Penawaran Kerja Sama Program Magang & MBKM Mahasiswa UKWK', 'Biasa', $tpl('Permohonan Kerja Sama'), now()->subHours(5));
        $this->kirim($u('fh@widyakarya.ac.id'), 'Permohonan Narasumber Kuliah Tamu Fakultas Hukum UKWK', 'Urgent', $tpl('Undangan'), now()->subHours(2));

        // 5) Perlu revisi
        $s = $this->kirim($u('alumni@widyakarya.ac.id'), 'Permohonan Dukungan Reuni Akbar Ikatan Alumni UKWK', 'Biasa', null, now()->subDays(2)->setTime(10, 0));
        $this->at(now()->subDays(2)->setTime(13, 0), fn () => $svc->tolak($s, $admin, 'Mohon lampirkan proposal kegiatan dan rincian anggaran (RAB).'));

        // 6) Diarsipkan sebagai informasi
        $s = $this->kirim($u('feb@widyakarya.ac.id'), 'Laporan Pelaksanaan Kuliah Umum Manajemen & Akuntansi FEB UKWK', 'Biasa', null, now()->subMonth()->setTime(9, 0));
        $this->at(now()->subMonth()->setTime(11, 0), fn () => $svc->validasi($s, $admin, ['kode_sumber' => 'B', 'kode_bidang' => 'Q']));
        $this->at(now()->subMonth()->setTime(14, 0), fn () => $svc->tandaiDibaca($s->fresh(), $rektor));
        $this->at(now()->subMonth()->setTime(14, 5), fn () => $svc->arsipkan($s->fresh(), $rektor, 'Sebagai informasi pimpinan.'));

        // 7) Didisposisi ke WR I, belum diteruskan
        $s = $this->kirim($u('pertanian@widyakarya.ac.id'), 'Permohonan Data Akademik untuk Akreditasi Prodi Agribisnis & Teknologi Pangan', 'Urgent', $tpl('Permohonan Data Akademik'), now()->subDays(3)->setTime(8, 0));
        $this->at(now()->subDays(3)->setTime(9, 0), fn () => $svc->validasi($s, $admin, ['kode_sumber' => 'B', 'kode_bidang' => 'Q', 'id_unit_saran' => $unit('Wakil Rektor I')]));
        $this->at(now()->subDays(2)->setTime(8, 0), fn () => $svc->tandaiDibaca($s->fresh(), $rektor));
        $this->at(now()->subDays(2)->setTime(8, 10), fn () => $svc->disposisiRektor($s->fresh(), $rektor, ['unit_tujuan' => [$unit('Wakil Rektor I')], 'instruksi' => 'Prioritaskan, koordinasikan dengan BAA dan LPM.', 'batas_waktu' => now()->addDays(3)->toDateString()]));

        // 8) Pending di BAU
        $s = $this->kirim($u('hmjsi@widyakarya.ac.id'), 'Permohonan Pencairan Dana Kompetisi Inovasi Digital Mahasiswa', 'Biasa', $tpl('Permohonan Pencairan Dana'), now()->subDays(10)->setTime(9, 0));
        $this->at(now()->subDays(10)->setTime(10, 0), fn () => $svc->validasi($s, $admin, ['kode_sumber' => 'B', 'kode_bidang' => 'B', 'id_unit_saran' => $unit('Wakil Rektor II')]));
        $this->at(now()->subDays(9)->setTime(8, 0), fn () => $svc->tandaiDibaca($s->fresh(), $rektor));
        $this->at(now()->subDays(9)->setTime(8, 30), fn () => $svc->disposisiRektor($s->fresh(), $rektor, ['unit_tujuan' => [$unit('Wakil Rektor II')], 'instruksi' => 'Proses sesuai ketentuan anggaran.']));
        $d = Disposisi::where('id_surat', $s->id_surat)->first();
        $this->at(now()->subDays(8)->setTime(9, 0), fn () => $svc->teruskan($d, $wr2, ['unit_tujuan' => [$unit('BAU')], 'instruksi' => 'Verifikasi RAB lalu cairkan.', 'batas_waktu' => now()->subDay()->toDateString()]));
        $d2 = Disposisi::where('id_induk_disposisi', $d->id_disposisi)->first();
        $this->at(now()->subDays(7)->setTime(10, 0), fn () => $svc->updateProgres($d2, $bau, Surat::PENDING, 'Menunggu kelengkapan kuitansi dari HMJ.'));

        // 9) Riwayat bulan-bulan sebelumnya (tren dashboard)
        foreach ([[4, 'Penyusunan Kalender Akademik Semester Genap UKWK', 'Q', 'Wakil Rektor I', 'BAA', $wr1, $baa],
                  [3, 'Permohonan Pembaruan Jaringan & Server SIAKAD', 'D', 'Wakil Rektor II', 'BAU', $wr2, $bau],
                  [3, 'Permohonan Rekomendasi Beasiswa Mahasiswa UKWK', 'K', 'Wakil Rektor III', 'BKA', $wr3, $bka],
                  [1, 'Permohonan Pengangkatan Dosen Tamu Praktisi', 'C', 'Wakil Rektor II', 'BAU', $wr2, $bau]] as [$bln, $perihal, $bid, $wrNama, $utNama, $wrUser, $utUser]) {
            $t = now()->subMonths($bln)->startOfMonth()->addDays(9)->setTime(9, 0);
            $s = $this->kirim($u('teknik@widyakarya.ac.id'), $perihal, 'Biasa', null, $t);
            $this->at($t->copy()->addHours(2), fn () => $svc->validasi($s, $admin, ['kode_sumber' => 'B', 'kode_bidang' => $bid, 'id_unit_saran' => $unit($wrNama)]));
            $this->at($t->copy()->addHours(5), fn () => $svc->tandaiDibaca($s->fresh(), $rektor));
            $this->at($t->copy()->addHours(5)->addMinutes(10), fn () => $svc->disposisiRektor($s->fresh(), $rektor, ['unit_tujuan' => [$unit($wrNama)], 'instruksi' => 'Tindak lanjuti.']));
            $d = Disposisi::where('id_surat', $s->id_surat)->first();
            $this->at($t->copy()->addDay(), fn () => $svc->teruskan($d, $wrUser, ['unit_tujuan' => [$unit($utNama)], 'instruksi' => 'Mohon diproses.']));
            $d2 = Disposisi::where('id_induk_disposisi', $d->id_disposisi)->first();
            $this->at($t->copy()->addDays(3), fn () => $svc->selesaikan($d2, $utUser, 'Telah diselesaikan.'));
        }

        Carbon::setTestNow();
    }

    protected function kirim(Pengguna $p, string $perihal, string $sifat, ?TemplateSurat $tpl, Carbon $waktu): Surat
    {
        return $this->at($waktu, fn () => $this->svc->kirimSurat($p->load('unit'), [
            'perihal' => $perihal,
            'isi_surat' => $tpl ? $tpl->render($p, $perihal) : "Dengan hormat,\n\nBersama ini kami sampaikan {$perihal}.\n\nHormat kami,\n{$p->nama}",
            'tanggal_surat' => $waktu->toDateString(),
            'sifat_surat' => $sifat,
            'id_template' => $tpl?->id_template,
        ]));
    }

    protected function at(Carbon $waktu, callable $fn): mixed
    {
        Carbon::setTestNow($waktu);
        $hasil = $fn();
        Carbon::setTestNow();

        return $hasil;
    }
}
