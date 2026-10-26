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
            [Role::ADMIN, 'Admin Surat Rektorat', 'Filter utama: validasi kelengkapan, klasifikasi kode sumber & bidang, menentukan tujuan disposisi, mengelola master data.'],
            [Role::REKTOR, 'Rektor', 'Pemberi disposisi kepada Wakil Rektor.'],
            [Role::WAKIL_REKTOR, 'Wakil Rektor', 'Meninjau disposisi Rektor dan meneruskan ke Unit Teknis.'],
            [Role::UNIT_TEKNIS, 'Staf Unit Teknis', 'Menerima disposisi, menindaklanjuti, memperbarui status, dan mengunggah bukti.'],
            [Role::PENGIRIM, 'Pengirim', 'Fakultas / Unit / HMJ / Alumni / Eksternal / Yayasan yang mengirim surat.'],
        ])->mapWithKeys(fn ($r) => [$r[0] => Role::create(['kode_role' => $r[0], 'nama_role' => $r[1], 'deskripsi' => $r[2]])]);

        // ------------------------------------------------------------ UNIT
        $rektorat = Unit::create(['nama_unit' => 'Rektorat', 'jenis_unit' => 'rektorat']);
        $wr1 = Unit::create(['nama_unit' => 'Wakil Rektor I - Bidang Akademik', 'jenis_unit' => 'wakil_rektor', 'id_induk' => $rektorat->id_unit]);
        $wr2 = Unit::create(['nama_unit' => 'Wakil Rektor II - Bidang Keuangan & Sarpras', 'jenis_unit' => 'wakil_rektor', 'id_induk' => $rektorat->id_unit]);
        $wr3 = Unit::create(['nama_unit' => 'Wakil Rektor III - Bidang Kemahasiswaan', 'jenis_unit' => 'wakil_rektor', 'id_induk' => $rektorat->id_unit]);
        $baa = Unit::create(['nama_unit' => 'BAA - Biro Administrasi Akademik', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr1->id_unit]);
        $bau = Unit::create(['nama_unit' => 'BAU - Biro Administrasi Umum & Keuangan', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr2->id_unit]);
        $hmj = Unit::create(['nama_unit' => 'HMJ - Bagian Kemahasiswaan', 'jenis_unit' => 'unit_teknis', 'id_induk' => $wr3->id_unit]);
        $ft = Unit::create(['nama_unit' => 'Fakultas Teknik', 'jenis_unit' => 'fakultas']);
        $feb = Unit::create(['nama_unit' => 'Fakultas Ekonomi dan Bisnis', 'jenis_unit' => 'fakultas']);
        $hmjTi = Unit::create(['nama_unit' => 'HMJ Teknik Informatika', 'jenis_unit' => 'hmj', 'id_induk' => $ft->id_unit]);
        $alumni = Unit::create(['nama_unit' => 'Ikatan Alumni', 'jenis_unit' => 'alumni']);
        $yayasan = Unit::create(['nama_unit' => 'Yayasan Pendidikan', 'jenis_unit' => 'yayasan']);
        $eksternal = Unit::create(['nama_unit' => 'Pihak Eksternal', 'jenis_unit' => 'eksternal']);

        // ------------------------------------------------------------ KODE SUMBER
        KategoriSumber::insert([
            ['kode_sumber' => 'A', 'nama_kategori' => 'Yayasan', 'keterangan' => 'Surat berasal dari lingkungan Yayasan', 'created_at' => now(), 'updated_at' => now()],
            ['kode_sumber' => 'B', 'nama_kategori' => 'Internal Universitas', 'keterangan' => 'Fakultas, Unit, HMJ, Alumni', 'created_at' => now(), 'updated_at' => now()],
            ['kode_sumber' => 'C', 'nama_kategori' => 'Eksternal', 'keterangan' => 'Surat berasal dari pihak / entitas eksternal', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ------------------------------------------------------------ KODE BIDANG
        foreach ([
            ['B', 'Urusan Keuangan', null, $wr2],
            ['C', 'Urusan Kepegawaian', null, $wr2],
            ['D', 'Urusan Perlengkapan / Sarpras', null, $wr2],
            ['G', 'Kerja Sama', null, $wr1],
            ['Q', 'Akademik', null, $wr1],
            ['K', 'Kemahasiswaan', 'Kode tambahan untuk surat bidang kemahasiswaan (dapat diubah admin).', $wr3],
        ] as [$kode, $nama, $ket, $wr]) {
            KodeBidang::create(['kode_bidang' => $kode, 'nama_bidang' => $nama, 'keterangan' => $ket, 'id_unit_wr' => $wr->id_unit]);
        }

        // ------------------------------------------------------------ TEMPLATE SURAT
        $penutup = "\n\nDemikian surat ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.\n\nHormat kami,\n{nama_pengirim}\n{unit_pengirim}";
        $pembuka = "Kepada Yth.\nRektor Universitas\ndi Tempat\n\nDengan hormat,\n";
        foreach ([
            ['Permohonan Izin Kegiatan', 'Permohonan Izin Pelaksanaan Kegiatan',
                "Sehubungan dengan rencana kegiatan yang akan diselenggarakan oleh {unit_pengirim}, bersama ini kami mengajukan permohonan izin pelaksanaan kegiatan dengan rincian sebagai berikut:\n\nNama Kegiatan : ...\nHari/Tanggal  : ...\nTempat        : ...\nPeserta       : ..."],
            ['Peminjaman Sarana & Prasarana', 'Permohonan Peminjaman Sarana dan Prasarana',
                "Dalam rangka mendukung kegiatan {unit_pengirim}, kami bermaksud meminjam sarana dan prasarana kampus sebagai berikut:\n\nFasilitas     : ...\nHari/Tanggal  : ...\nWaktu         : ...\nKeperluan     : ..."],
            ['Permohonan Kerja Sama', 'Permohonan Kerja Sama Kelembagaan',
                "Bersama surat ini, {unit_pengirim} bermaksud mengajukan kerja sama dengan Universitas dalam bidang ...\n\nRuang lingkup kerja sama meliputi:\n1. ...\n2. ...\n\nProposal kerja sama kami lampirkan bersama surat ini."],
            ['Permohonan Pencairan Dana', 'Permohonan Pencairan Dana Kegiatan',
                "Sehubungan dengan pelaksanaan kegiatan ..., kami mengajukan permohonan pencairan dana sebesar Rp ... sesuai dengan Rencana Anggaran Biaya (RAB) terlampir."],
            ['Permohonan Data Akademik', 'Permohonan Data Akademik',
                "Dalam rangka keperluan ..., kami memohon bantuan penyediaan data akademik sebagai berikut:\n\n1. ...\n2. ...\n\nData tersebut kami perlukan paling lambat tanggal ..."],
            ['Undangan', 'Undangan Kegiatan',
                "Dengan hormat, kami mengundang Bapak/Ibu untuk menghadiri kegiatan yang akan diselenggarakan pada:\n\nHari/Tanggal : ...\nWaktu        : ...\nTempat       : ...\nAcara        : ..."],
        ] as [$nama, $perihal, $isi]) {
            TemplateSurat::create([
                'nama_template' => $nama,
                'perihal_default' => $perihal,
                'isi_template' => "Nomor   : -\nTanggal : {tanggal}\nPerihal : {perihal}\n\n" . $pembuka . "\n" . $isi . $penutup,
            ]);
        }

        // ------------------------------------------------------------ PENGGUNA DEMO (password: password)
        $akun = [
            ['admin@univ.ac.id', 'Siti Rahmawati', '198801012015042001', Role::ADMIN, $rektorat],
            ['rektor@univ.ac.id', 'Prof. Dr. Ahmad Santoso', '196505101990031001', Role::REKTOR, $rektorat],
            ['wr1@univ.ac.id', 'Dr. Budi Hartono', '197002021995031002', Role::WAKIL_REKTOR, $wr1],
            ['wr2@univ.ac.id', 'Dr. Ratna Dewi', '197103031996032001', Role::WAKIL_REKTOR, $wr2],
            ['wr3@univ.ac.id', 'Dr. Hendra Wijaya', '197204041997031003', Role::WAKIL_REKTOR, $wr3],
            ['baa@univ.ac.id', 'Rina Marlina', '198505052010042002', Role::UNIT_TEKNIS, $baa],
            ['bau@univ.ac.id', 'Agus Setiawan', '198606062011041001', Role::UNIT_TEKNIS, $bau],
            ['hmj@univ.ac.id', 'Dimas Pratama', '199007072015041002', Role::UNIT_TEKNIS, $hmj],
            ['ft@univ.ac.id', 'Dekanat Fakultas Teknik', null, Role::PENGIRIM, $ft],
            ['feb@univ.ac.id', 'Dekanat Fakultas Ekonomi dan Bisnis', null, Role::PENGIRIM, $feb],
            ['hmjti@univ.ac.id', 'Ketua HMJ Teknik Informatika', null, Role::PENGIRIM, $hmjTi],
            ['alumni@univ.ac.id', 'Pengurus Ikatan Alumni', null, Role::PENGIRIM, $alumni],
            ['yayasan@univ.ac.id', 'Sekretariat Yayasan', null, Role::PENGIRIM, $yayasan],
            ['mitra@perusahaan.co.id', 'PT Mitra Teknologi Nusantara', null, Role::PENGIRIM, $eksternal],
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
