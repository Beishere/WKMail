<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Pengguna;
use App\Models\RiwayatStatus;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\Unit;
use App\Services\WordSuratService;
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

        $pengirim = Pengguna::where('email', 'teknik@wkmail.ac.id')->firstOrFail();
        $admin = Pengguna::where('email', 'admin@wkmail.ac.id')->firstOrFail();
        $rektor = Pengguna::where('email', 'rektor@wkmail.ac.id')->firstOrFail();
        $wr1 = Pengguna::where('email', 'wr1@wkmail.ac.id')->firstOrFail();
        $baa = Pengguna::where('email', 'baa@wkmail.ac.id')->firstOrFail();

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

    public function test_rektor_can_unarchive_a_legacy_archived_letter_and_restore_its_previous_status(): void
    {
        $pengirim = Pengguna::where('email', 'hmjsi@wkmail.ac.id')->firstOrFail();
        $rektor = Pengguna::where('email', 'rektor@wkmail.ac.id')->firstOrFail();
        $surat = Surat::create([
            'nomor_surat' => 'SRT/2026/11/0098',
            'perihal' => 'Permohonan Kegiatan HMJ',
            'tanggal_surat' => now()->toDateString(),
            'sifat_surat' => 'Biasa',
            'id_pengirim' => $pengirim->id_pengguna,
            'status_terkini' => Surat::DIARSIPKAN,
        ]);

        foreach ([
            [Surat::DIVALIDASI, now()->subMinutes(2)],
            [Surat::DIBACA, now()->subMinute()],
            [Surat::DIARSIPKAN, now()],
        ] as [$status, $waktu]) {
            RiwayatStatus::create([
                'id_surat' => $surat->id_surat,
                'id_pengguna' => $rektor->id_pengguna,
                'status' => $status,
                'waktu' => $waktu,
                'keterangan' => $status,
            ]);
        }

        $this->actingAs($rektor)->get(route('surat.show', $surat))
            ->assertOk()
            ->assertSee('Batalkan Pengarsipan');

        $this->actingAs($rektor)->post(route('rektor.batal-arsip', $surat), [
            'catatan' => 'Surat perlu ditindaklanjuti.',
        ])->assertRedirect(route('rektor.index'));

        $this->assertSame(Surat::DIBACA, $surat->fresh()->status_terkini);
        $this->assertNull($surat->fresh()->status_sebelum_arsip);
        $this->assertStringContainsString(
            'Alasan: Surat perlu ditindaklanjuti.',
            RiwayatStatus::where('id_surat', $surat->id_surat)->latest('id_riwayat')->value('keterangan')
        );
    }

    public function test_multi_unit_forwarding_keeps_pustik_inbox_open_until_both_units_finish(): void
    {
        $pengirim = Pengguna::where('email', 'hmjsi@wkmail.ac.id')->firstOrFail();
        $rektor = Pengguna::where('email', 'rektor@wkmail.ac.id')->firstOrFail();
        $wr2 = Pengguna::where('email', 'wr2@wkmail.ac.id')->firstOrFail();
        $bau = Pengguna::where('email', 'bau@wkmail.ac.id')->firstOrFail();
        $pustik = Pengguna::where('email', 'pustik@wkmail.ac.id')->firstOrFail();
        $unitWr2 = Unit::where('nama_unit', 'like', 'Wakil Rektor II%')->firstOrFail();
        $unitBau = Unit::where('nama_unit', 'like', 'BAU%')->firstOrFail();
        $unitPustik = Unit::where('nama_unit', 'like', 'PUSTIK%')->firstOrFail();
        $surat = Surat::create([
            'nomor_surat' => 'SRT/2026/11/0099',
            'perihal' => 'Permohonan Koordinasi BAU dan PUSTIK',
            'tanggal_surat' => now()->toDateString(),
            'sifat_surat' => 'Biasa',
            'id_pengirim' => $pengirim->id_pengguna,
            'status_terkini' => Surat::DIBACA,
        ]);

        $this->actingAs($rektor)->post(route('rektor.disposisi', $surat), [
            'unit_tujuan' => [$unitWr2->id_unit],
            'instruksi' => 'Koordinasikan pekerjaan lintas unit.',
        ])->assertRedirect(route('rektor.index'));

        $disposisiWr = Disposisi::where('id_surat', $surat->id_surat)->firstOrFail();
        $this->actingAs($wr2)->post(route('disposisi.teruskan', $disposisiWr), [
            'unit_tujuan' => [$unitBau->id_unit, $unitPustik->id_unit],
            'instruksi' => 'Tindak lanjuti sesuai tugas unit masing-masing.',
        ])->assertRedirect(route('disposisi.index'));

        $disposisiBau = Disposisi::where('id_induk_disposisi', $disposisiWr->id_disposisi)
            ->where('id_unit_tujuan', $unitBau->id_unit)->firstOrFail();
        $disposisiPustik = Disposisi::where('id_induk_disposisi', $disposisiWr->id_disposisi)
            ->where('id_unit_tujuan', $unitPustik->id_unit)->firstOrFail();

        $this->actingAs($pustik)->get(route('disposisi.index'))
            ->assertOk()
            ->assertSee($surat->perihal)
            ->assertSee('1 baru');

        $this->actingAs($bau)->post(route('disposisi.selesai', $disposisiBau), [
            'keterangan' => 'Pekerjaan BAU selesai.',
        ])->assertRedirect(route('disposisi.index'));

        $this->assertSame(Surat::DIPROSES, $surat->fresh()->status_terkini);
        $this->assertSame(Surat::TERKIRIM, $disposisiPustik->fresh()->status);
        $riwayatBau = RiwayatStatus::where('id_surat', $surat->id_surat)
            ->where('status', Surat::SELESAI)->firstOrFail();
        $this->assertStringContainsString('BAU', $riwayatBau->keterangan);
        $this->actingAs($pengirim)->get(route('surat.show', $surat))
            ->assertOk()
            ->assertSee('Status pada riwayat berlaku untuk aktivitas');
        $this->actingAs($pustik)->get(route('disposisi.index'))
            ->assertOk()
            ->assertSee($surat->perihal)
            ->assertSee('1 baru');

        $this->actingAs($pustik)->post(route('disposisi.selesai', $disposisiPustik), [
            'keterangan' => 'Pekerjaan PUSTIK selesai.',
        ])->assertRedirect(route('disposisi.index'));

        $this->assertSame(Surat::SELESAI, $surat->fresh()->status_terkini);
    }

    public function test_edit_dan_sinkron_surat_via_microsoft_word_docx(): void
    {
        Storage::fake('local');

        $pengirim = Pengguna::where('email', 'teknik@wkmail.ac.id')->firstOrFail();
        $surat = Surat::create([
            'nomor_surat' => '098/FT-UKWK/X/2026',
            'perihal' => 'Permohonan Awal Laboratorium',
            'isi_surat' => "Isi awal sebelum diedit di Word.\n\nScientia ad Laborem,\nHormat kami,\nDekan Fakultas Teknik",
            'tanggal_surat' => now()->toDateString(),
            'sifat_surat' => 'Biasa',
            'id_pengirim' => $pengirim->id_pengguna,
            'status_terkini' => Surat::TERKIRIM,
        ]);

        // 1. Unduh surat sebagai berkas .docx
        $resDownload = $this->actingAs($pengirim)->get(route('surat.word', $surat));
        $resDownload->assertOk();

        // 2. Uji WordSuratService generateDocx & extractFromDocx secara langsung
        $wordService = app(WordSuratService::class);
        $docxPath = $wordService->generateDocx([
            'nomor_surat' => '099/FT-UKWK/X/2026',
            'tanggal_surat' => '27 Oktober 2026',
            'sifat_surat' => 'Urgent',
            'perihal' => 'Permohonan Penggunaan Laboratorium Terpadu (diedit di Word)',
            'isi_surat' => "Nomor : 099/FT-UKWK/X/2026\nTanggal : 27 Oktober 2026\nSifat : Urgent\nPerihal : Permohonan Penggunaan Laboratorium Terpadu (diedit di Word)\n\nDengan hormat,\nParagraf pertama yang telah diedit di Microsoft Word.\nTerima kasih.\n\nScientia ad Laborem,\nHormat kami,\nKetua HMJ Sistem Informasi eCommerce\nFakultas Teknik",
        ]);

        $archive = new \ZipArchive;
        $this->assertTrue($archive->open($docxPath));
        $documentXml = $archive->getFromName('word/document.xml');
        $archive->close();
        $this->assertIsString($documentXml);
        $this->assertStringContainsString('<w:pgSz w:w="11906" w:h="16838"/>', $documentXml);
        $this->assertStringContainsString('w:sz w:val="24"', $documentXml);
        $this->assertStringContainsString('w:line="276"', $documentXml);
        $this->assertStringContainsString('<w:jc w:val="both"/>', $documentXml);
        $this->assertStringContainsString('<w:jc w:val="right"/>', $documentXml);
        $this->assertStringContainsString('w:before="1000"', $documentXml);
        $this->assertTrue((new \DOMDocument)->loadXML($documentXml));

        $extracted = $wordService->extractFromDocx($docxPath);
        $this->assertSame('099/FT-UKWK/X/2026', $extracted['nomor_surat']);
        $this->assertSame('Urgent', $extracted['sifat_surat']);
        $this->assertSame('Permohonan Penggunaan Laboratorium Terpadu (diedit di Word)', $extracted['perihal']);
        $this->assertSame(
            "Dengan hormat,\nParagraf pertama yang telah diedit di Microsoft Word.\nTerima kasih.\nScientia ad Laborem,\nHormat kami,\nKetua HMJ Sistem Informasi eCommerce\nFakultas Teknik",
            $extracted['isi_surat']
        );

        $this->actingAs($pengirim)->get(route('surat.show', $surat))
            ->assertOk()
            ->assertSee('class="print-only"', false)
            ->assertSee('class="print-letter"', false)
            ->assertSee('class="print-signature"', false);

        // 3. Uji endpoint AJAX import-word
        $uploadedDocx = new UploadedFile(
            $docxPath,
            'Surat-Revisi-Word.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $resImport = $this->actingAs($pengirim)->postJson(route('surat.import-word'), [
            'file_word' => $uploadedDocx,
        ]);
        $resImport->assertOk()
            ->assertJson([
                'ok' => true,
                'perihal' => 'Permohonan Penggunaan Laboratorium Terpadu (diedit di Word)',
            ]);

        @unlink($docxPath);
    }
}
