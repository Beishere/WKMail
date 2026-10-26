<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Pengguna;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\Unit;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DisposisiAlurTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterSeeder::class);
    }

    public function test_alur_lengkap_disposisi_surat_dari_pengirim_hingga_selesai(): void
    {
        Storage::fake('local');

        $pengirim = Pengguna::where('email', 'teknik@widyakarya.ac.id')->firstOrFail();
        $admin = Pengguna::where('email', 'admin@widyakarya.ac.id')->firstOrFail();
        $rektor = Pengguna::where('email', 'rektor@widyakarya.ac.id')->firstOrFail();
        $wr1 = Pengguna::where('email', 'wr1ukwk@widyakarya.ac.id')->firstOrFail();
        $baa = Pengguna::where('email', 'baa@widyakarya.ac.id')->firstOrFail();

        $unitWr1 = Unit::where('nama_unit', 'like', 'Wakil Rektor I%')->firstOrFail();
        $unitBaa = Unit::where('nama_unit', 'like', 'BAA%')->firstOrFail();
        $tpl = TemplateSurat::firstOrFail();

        // 1. Pengirim membuat surat baru dengan lampiran
        $res = $this->actingAs($pengirim)->post(route('surat.store'), [
            'id_template' => $tpl->id_template,
            'perihal' => 'Permohonan Jadwal Ujian Akhir Semester',
            'isi_surat' => 'Isi permohonan jadwal UAS.',
            'tanggal_surat' => now()->toDateString(),
            'sifat_surat' => 'Urgent',
            'lampiran' => [UploadedFile::fake()->create('surat-uas.pdf', 120, 'application/pdf')],
        ]);
        $res->assertRedirect();

        $surat = Surat::latest('id_surat')->firstOrFail();
        $this->assertSame(Surat::TERKIRIM, $surat->status_terkini);
        $this->assertCount(1, $surat->lampiran);

        // 2. Admin menolak untuk revisi
        $this->actingAs($admin)->post(route('verifikasi.tolak', $surat), [
            'catatan_revisi' => 'Mohon lampirkan daftar mata kuliah.',
        ])->assertRedirect(route('verifikasi.index'));

        $this->assertSame(Surat::PERLU_REVISI, $surat->fresh()->status_terkini);

        // 3. Pengirim merevisi dan mengirim ulang
        $this->actingAs($pengirim)->put(route('surat.update', $surat), [
            'perihal' => 'Permohonan Jadwal Ujian Akhir Semester (Revisi)',
            'isi_surat' => 'Isi permohonan jadwal UAS beserta lampiran.',
            'tanggal_surat' => now()->toDateString(),
            'sifat_surat' => 'Urgent',
        ])->assertRedirect(route('surat.show', $surat));

        $this->assertSame(Surat::TERKIRIM, $surat->fresh()->status_terkini);

        // 4. Admin memvalidasi & mengklasifikasi kode sumber B & bidang Q
        $this->actingAs($admin)->post(route('verifikasi.validasi', $surat), [
            'kode_sumber' => 'B',
            'kode_bidang' => 'Q',
            'id_unit_saran' => $unitWr1->id_unit,
            'catatan_admin' => 'Berkas lengkap.',
        ])->assertRedirect(route('verifikasi.index'));

        $surat->refresh();
        $this->assertSame(Surat::DIVALIDASI, $surat->status_terkini);
        $this->assertSame('B-Q', $surat->kode_klasifikasi);

        // 5. Rektor membaca surat (otomatis berubah ke Dibaca) lalu memberi disposisi ke WR I
        $this->actingAs($rektor)->get(route('surat.show', $surat))->assertOk();
        $this->assertSame(Surat::DIBACA, $surat->fresh()->status_terkini);

        $this->actingAs($rektor)->post(route('rektor.disposisi', $surat), [
            'unit_tujuan' => [$unitWr1->id_unit],
            'instruksi' => 'Koordinasikan dengan BAA segera.',
            'batas_waktu' => now()->addDays(5)->toDateString(),
        ])->assertRedirect(route('rektor.index'));

        $this->assertSame(Surat::DIDISPOSISI, $surat->fresh()->status_terkini);
        $dispWr = Disposisi::where('id_surat', $surat->id_surat)->firstOrFail();

        // 6. Wakil Rektor I meneruskan disposisi ke Unit Teknis BAA
        $this->actingAs($wr1)->post(route('disposisi.teruskan', $dispWr), [
            'unit_tujuan' => [$unitBaa->id_unit],
            'instruksi' => 'Susun jadwal UAS dan terbitkan pengumuman.',
            'batas_waktu' => now()->addDays(3)->toDateString(),
        ])->assertRedirect(route('disposisi.index'));

        $dispBaa = Disposisi::where('id_induk_disposisi', $dispWr->id_disposisi)->firstOrFail();

        // 7. Staf BAA memperbarui status menjadi Diproses, lalu Selesai + Bukti
        $this->actingAs($baa)->post(route('disposisi.progres', $dispBaa), [
            'status' => Surat::DIPROSES,
            'keterangan' => 'Sedang menyusun jadwal.',
        ])->assertRedirect();

        $this->assertSame(Surat::DIPROSES, $surat->fresh()->status_terkini);

        $this->actingAs($baa)->post(route('disposisi.selesai', $dispBaa), [
            'keterangan' => 'Jadwal UAS telah diterbitkan.',
            'bukti' => [UploadedFile::fake()->create('sk-jadwal-uas.pdf', 200, 'application/pdf')],
        ])->assertRedirect(route('disposisi.index'));

        $surat->refresh();
        $this->assertSame(Surat::SELESAI, $surat->status_terkini);
        $this->assertSame(Surat::SELESAI, $dispWr->fresh()->status);
        $this->assertCount(1, $surat->bukti);

        // 8. Uji halaman dashboard, lacak, laporan, dan master data
        foreach ([$pengirim, $admin, $rektor, $wr1, $baa] as $u) {
            $this->actingAs($u)->get(route('dashboard'))->assertOk();
            $this->actingAs($u)->get(route('lacak', ['q' => 'Ujian Akhir']))->assertOk();
        }
        $this->actingAs($admin)->get(route('laporan.index'))->assertOk();
        $this->actingAs($admin)->get(route('master.pengguna.index'))->assertOk();
        $this->actingAs($admin)->get(route('master.unit.index'))->assertOk();
        $this->actingAs($admin)->get(route('master.template.index'))->assertOk();
        $this->actingAs($admin)->get(route('master.kategori-sumber.index'))->assertOk();
        $this->actingAs($admin)->get(route('master.kode-bidang.index'))->assertOk();
    }
}
