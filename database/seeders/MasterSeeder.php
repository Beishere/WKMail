<?php

namespace Database\Seeders;

use App\Models\KategoriSumber;
use App\Models\KodeBidang;
use App\Models\Pengguna;
use App\Models\Role;
use App\Models\TemplateSurat;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Data master sesuai diagram: peran, struktur unit, kode sumber (A/B/C), kode bidang, template & akun demo.
 */
class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------ ROLE
        $roles = collect([
            [Role::ADMIN, 'Admin Surat Rektorat', 'Filter utama Rektorat UKWK: validasi kelengkapan, klasifikasi kode sumber (A/B/C) & bidang, menentukan saran disposisi, serta mengelola master data WKMail.'],
            [Role::REKTOR, 'Rektor UKWK', 'Pimpinan Tertinggi Universitas Katolik Widya Karya (Periode 2024–2028) pemberi disposisi kepada Wakil Rektor.'],
            [Role::WAKIL_REKTOR, 'Wakil Rektor UKWK', 'Pimpinan Koordinasi Bidang (Akademik, Administrasi Umum & Keuangan, Kemahasiswaan & Alumni) yang meneruskan disposisi ke Biro & Unit Pendukung.'],
            [Role::UNIT_TEKNIS, 'Biro & Unit Pendukung', 'Pelaksana teknis UKWK (BAA, BAU, BKA, LPPM, LPM, PUSTIK) yang menindaklanjuti disposisi dan mengunggah bukti penyelesaian.'],
            [Role::PENGIRIM, 'Pengirim Surat', 'Yayasan PTK Adisucipto, Fakultas (FEB, FT, FH, FP), Prodi, HMJ, Ikatan Alumni, dan Mitra Kerjasama.'],
        ])->mapWithKeys(fn ($r) => [$r[0] => Role::create(['kode_role' => $r[0], 'nama_role' => $r[1], 'deskripsi' => $r[2]])]);

        // ------------------------------------------------------------ UNIT (Sesuai Struktur Organisasi widyakarya.ac.id)
        $rektorat = Unit::create(['nama_unit' => 'Rektorat UKWK', 'jenis_unit' => 'rektorat']);
        $wr1 = Unit::create(['nama_unit' => 'Wakil Rektor I - Bidang Akademik', 'jenis_unit' => 'wakil_rektor', 'id_induk' => $rektorat->id_unit]);
        $wr2 = Unit::create(['nama_unit' => 'Wakil Rektor II - Bidang Administrasi Umum & Keuangan', 'jenis_unit' => 'wakil_rektor', 'id_induk' => $rektorat->id_unit]);
        $wr3 = Unit::create(['nama_unit' => 'Wakil Rektor III - Bidang Kemahasiswaan & Alumni', 'jenis_unit' => 'wakil_rektor', 'id_induk' => $rektorat->id_unit]);
        $baa = Unit::create(['nama_unit' => 'BAA - Biro Administrasi Akademik', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr1->id_unit]);
        $bau = Unit::create(['nama_unit' => 'BAU - Biro Administrasi dan Umum', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr2->id_unit]);
        $bka = Unit::create(['nama_unit' => 'BKA - Biro Administrasi Kemahasiswaan dan Alumni', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr3->id_unit]);
        $lppm = Unit::create(['nama_unit' => 'LPPM - Lembaga Penelitian dan Pengabdian Masyarakat', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr1->id_unit]);
        $lpm = Unit::create(['nama_unit' => 'LPM - Lembaga Penjaminan Mutu', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr1->id_unit]);
        $pustik = Unit::create(['nama_unit' => 'PUSTIK - Pusat Teknologi Informasi dan Komunikasi', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr2->id_unit]);
        $feb = Unit::create(['nama_unit' => 'Fakultas Ekonomi dan Bisnis (FEB)', 'jenis_unit' => 'fakultas']);
        $ft = Unit::create(['nama_unit' => 'Fakultas Teknik (FT)', 'jenis_unit' => 'fakultas']);
        $fh = Unit::create(['nama_unit' => 'Fakultas Hukum (FH)', 'jenis_unit' => 'fakultas']);
        $fp = Unit::create(['nama_unit' => 'Fakultas Pertanian (FP)', 'jenis_unit' => 'fakultas']);
        $hmjSi = Unit::create(['nama_unit' => 'HMJ Sistem Informasi eCommerce', 'jenis_unit' => 'hmj', 'id_induk' => $ft->id_unit]);
        $alumni = Unit::create(['nama_unit' => 'Ikatan Alumni UKWK (IKA UKWK)', 'jenis_unit' => 'alumni']);
        $yayasan = Unit::create(['nama_unit' => 'Yayasan PTK Adisucipto Malang', 'jenis_unit' => 'yayasan']);
        $eksternal = Unit::create(['nama_unit' => 'Mitra Kerjasama Institusi & Industri', 'jenis_unit' => 'eksternal']);

        // ------------------------------------------------------------ KODE SUMBER
        KategoriSumber::insert([
            ['kode_sumber' => 'A', 'nama_kategori' => 'Yayasan PTK Adisucipto', 'keterangan' => 'Surat berasal dari Yayasan Perguruan Tinggi Katolik Adisucipto Malang', 'created_at' => now(), 'updated_at' => now()],
            ['kode_sumber' => 'B', 'nama_kategori' => 'Internal UKWK', 'keterangan' => 'Fakultas (FEB, FT, FH, FP), Biro, Lembaga, HMJ, & Ikatan Alumni UKWK', 'created_at' => now(), 'updated_at' => now()],
            ['kode_sumber' => 'C', 'nama_kategori' => 'Mitra & Eksternal', 'keterangan' => 'Surat berasal dari mitra kerjasama nasional/internasional, instansi, & pihak luar', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ------------------------------------------------------------ KODE BIDANG
        foreach ([
            ['B', 'Urusan Keuangan & Anggaran', null, $wr2],
            ['C', 'Urusan Kepegawaian & SDM', null, $wr2],
            ['D', 'Urusan Perlengkapan & Sarpras', null, $wr2],
            ['G', 'Kerjasama Nasional & Internasional', null, $wr1],
            ['Q', 'Akademik, Penelitian & Penjaminan Mutu', null, $wr1],
            ['K', 'Kemahasiswaan & Alumni (BKA)', 'Kode surat bidang kemahasiswaan, beasiswa, organisasi mahasiswa, dan alumni.', $wr3],
        ] as [$kode, $nama, $ket, $wr]) {
            KodeBidang::create(['kode_bidang' => $kode, 'nama_bidang' => $nama, 'keterangan' => $ket, 'id_unit_wr' => $wr->id_unit]);
        }

        // ------------------------------------------------------------ TEMPLATE SURAT
        $penutup = "\n\nDemikian surat ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.\n\nScientia ad Laborem,\nHormat kami,\n{nama_pengirim}\n{unit_pengirim}";
        $pembuka = "Kepada Yth.\nRektor Universitas Katolik Widya Karya Malang\nProf. Dr. Ir. Kukuk Yudiono, M.S.\nJl. Bondowoso No. 2 Malang\n\nDengan hormat,\n";
        foreach ([
            ['Permohonan Izin Kegiatan', 'Permohonan Izin Pelaksanaan Kegiatan',
                "Sehubungan dengan rencana kegiatan yang akan diselenggarakan oleh {unit_pengirim}, bersama ini kami mengajukan permohonan izin pelaksanaan kegiatan dengan rincian sebagai berikut:\n\nNama Kegiatan : ...\nHari/Tanggal  : ...\nTempat        : Kampus UKWK Jl. Bondowoso No. 2 Malang\nPeserta       : ..."],
            ['Peminjaman Sarana & Prasarana', 'Permohonan Peminjaman Sarana dan Prasarana Kampus',
                "Dalam rangka mendukung kegiatan {unit_pengirim}, kami bermaksud meminjam sarana dan prasarana kampus UKWK sebagai berikut:\n\nFasilitas     : ...\nHari/Tanggal  : ...\nWaktu         : ...\nKeperluan     : ..."],
            ['Permohonan Kerja Sama', 'Permohonan Kerja Sama Kelembagaan',
                "Bersama surat ini, {unit_pengirim} bermaksud mengajukan kerja sama dengan Universitas Katolik Widya Karya Malang dalam bidang ...\n\nRuang lingkup kerja sama meliputi:\n1. ...\n2. ...\n\nProposal kerja sama kami lampirkan bersama surat ini."],
            ['Permohonan Pencairan Dana', 'Permohonan Pencairan Dana Kegiatan',
                "Sehubungan dengan pelaksanaan kegiatan ..., kami mengajukan permohonan pencairan dana sebesar Rp ... sesuai dengan Rencana Anggaran Biaya (RAB) terlampir."],
            ['Permohonan Data Akademik', 'Permohonan Data Akademik & Akreditasi',
                "Dalam rangka keperluan ..., kami memohon bantuan penyediaan data akademik melalui BAA / LPM sebagai berikut:\n\n1. ...\n2. ...\n\nData tersebut kami perlukan paling lambat tanggal ..."],
            ['Undangan', 'Undangan Kegiatan Resmi',
                "Dengan hormat, kami mengundang Bapak/Ibu Pimpinan Universitas Katolik Widya Karya Malang untuk menghadiri kegiatan yang akan diselenggarakan pada:\n\nHari/Tanggal : ...\nWaktu        : ...\nTempat       : ...\nAcara        : ..."],
        ] as [$nama, $perihal, $isi]) {
            TemplateSurat::create([
                'nama_template' => $nama,
                'perihal_default' => $perihal,
                'isi_template' => "Nomor   : -\nTanggal : {tanggal}\nPerihal : {perihal}\n\n" . $pembuka . "\n" . $isi . $penutup,
                'path_kop' => 'public/kop-ukwk.jpeg',
                'nama_file_template' => 'Kop-Surat-UKWK.docx',
                'path_file_template' => 'public/kop-ukwk.docx',
            ]);
        }

        // ------------------------------------------------------------ PENGGUNA RESMI UKWK (password: password)
        $akun = [
            ['admin@widyakarya.ac.id', 'Sekretariat Rektorat UKWK', '198801012015042001', Role::ADMIN, $rektorat],
            ['rektor@widyakarya.ac.id', 'Prof. Dr. Ir. Kukuk Yudiono, M.S.', '196505101990031001', Role::REKTOR, $rektorat],
            ['wr1ukwk@widyakarya.ac.id', 'Harsa Dhani, S.T., M.T., Ph.D.', '197002021995031002', Role::WAKIL_REKTOR, $wr1],
            ['wr2ukwk@widyakarya.ac.id', 'Galuh Budi Astuti, S.E., M.M.', '197103031996032001', Role::WAKIL_REKTOR, $wr2],
            ['wr3ukwk@widyakarya.ac.id', 'Dr. Celina Tri Siwi, S.H., M.Hum.', '197204041997031003', Role::WAKIL_REKTOR, $wr3],
            ['baa@widyakarya.ac.id', 'Kepala Biro Administrasi Akademik (BAA)', '198505052010042002', Role::UNIT_TEKNIS, $baa],
            ['bau@widyakarya.ac.id', 'Kepala Biro Administrasi dan Umum (BAU)', '198606062011041001', Role::UNIT_TEKNIS, $bau],
            ['bka@widyakarya.ac.id', 'Kepala Biro Kemahasiswaan & Alumni (BKA)', '199007072015041002', Role::UNIT_TEKNIS, $bka],
            ['lppm@widyakarya.ac.id', 'Ketua LPPM UKWK', '198304122009041001', Role::UNIT_TEKNIS, $lppm],
            ['lpm@widyakarya.ac.id', 'Ketua LPM UKWK', '198408192010042003', Role::UNIT_TEKNIS, $lpm],
            ['pustik@widyakarya.ac.id', 'Kepala PUSTIK UKWK', '198911032014041002', Role::UNIT_TEKNIS, $pustik],
            ['yayasan@widyakarya.ac.id', 'Budi Santoso Djadja, S.H., M.M. (Yayasan PTK Adisucipto)', null, Role::PENGIRIM, $yayasan],
            ['feb@widyakarya.ac.id', 'Dekan Fakultas Ekonomi dan Bisnis (FEB)', null, Role::PENGIRIM, $feb],
            ['teknik@widyakarya.ac.id', 'Dekan Fakultas Teknik (FT)', null, Role::PENGIRIM, $ft],
            ['fh@widyakarya.ac.id', 'Dr. Diah Imaningrum Susanti, S.H., M.Hum. (Dekan FH)', null, Role::PENGIRIM, $fh],
            ['pertanian@widyakarya.ac.id', 'Dekan Fakultas Pertanian (FP)', null, Role::PENGIRIM, $fp],
            ['hmjsi@widyakarya.ac.id', 'Ketua HMJ Sistem Informasi eCommerce', null, Role::PENGIRIM, $hmjSi],
            ['alumni@widyakarya.ac.id', 'Pengurus Ikatan Alumni UKWK', null, Role::PENGIRIM, $alumni],
            ['mitra@institusi.co.id', 'Mitra Kerjasama Nasional & Internasional', null, Role::PENGIRIM, $eksternal],
        ];

        foreach ($akun as [$email, $nama, $nip, $role, $unit]) {
            Pengguna::create([
                'email' => $email,
                'nama' => $nama,
                'nip' => $nip,
                'password_hash' => 'password',
                'id_role' => $roles[$role]->id_role,
                'id_unit' => $unit->id_unit,
                'status_aktif' => true,
            ]);
        }
    }
}
