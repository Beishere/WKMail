<?php

use App\Models\Surat;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel transaksi sesuai ERD: SURAT, LAMPIRAN, DISPOSISI, RIWAYAT_STATUS, NOTIFIKASI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table) {
            $table->increments('id_surat');
            $table->string('nomor_surat', 100)->unique();
            $table->string('perihal', 255);
            $table->text('isi_surat')->nullable();
            $table->date('tanggal_surat');
            $table->dateTime('tanggal_diterima')->nullable();
            $table->enum('sifat_surat', ['Biasa', 'Urgent'])->default('Biasa');
            $table->char('kode_sumber', 1)->nullable();
            $table->char('kode_bidang', 1)->nullable();
            $table->unsignedInteger('id_pengirim');
            $table->unsignedInteger('id_template')->nullable();
            $table->unsignedInteger('id_unit_saran')->nullable(); // tujuan disposisi yang disarankan admin
            $table->text('catatan_admin')->nullable();
            $table->enum('status_terkini', Surat::STATUSES)->default(Surat::TERKIRIM);
            $table->timestamps();

            $table->foreign('kode_sumber')->references('kode_sumber')->on('kategori_sumber')->nullOnDelete();
            $table->foreign('kode_bidang')->references('kode_bidang')->on('kode_bidang')->nullOnDelete();
            $table->foreign('id_pengirim')->references('id_pengguna')->on('pengguna');
            $table->foreign('id_template')->references('id_template')->on('template_surat')->nullOnDelete();
            $table->foreign('id_unit_saran')->references('id_unit')->on('unit')->nullOnDelete();
        });

        Schema::create('disposisi', function (Blueprint $table) {
            $table->increments('id_disposisi');
            $table->unsignedInteger('id_surat');
            $table->unsignedInteger('id_induk_disposisi')->nullable(); // disposisi lanjutan (WR -> Unit Teknis)
            $table->unsignedInteger('id_pemberi');
            $table->unsignedInteger('id_penerima')->nullable();
            $table->unsignedInteger('id_unit_tujuan');
            $table->text('instruksi');
            $table->text('catatan')->nullable();
            $table->dateTime('tanggal_disposisi');
            $table->date('batas_waktu')->nullable();
            $table->enum('status', Surat::STATUSES)->default(Surat::TERKIRIM);
            $table->timestamps();

            $table->foreign('id_surat')->references('id_surat')->on('surat')->cascadeOnDelete();
            $table->foreign('id_induk_disposisi')->references('id_disposisi')->on('disposisi')->cascadeOnDelete();
            $table->foreign('id_pemberi')->references('id_pengguna')->on('pengguna');
            $table->foreign('id_penerima')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $table->foreign('id_unit_tujuan')->references('id_unit')->on('unit');
        });

        Schema::create('lampiran', function (Blueprint $table) {
            $table->increments('id_lampiran');
            $table->unsignedInteger('id_surat');
            $table->unsignedInteger('id_disposisi')->nullable(); // terisi bila lampiran = bukti penyelesaian
            $table->enum('jenis', ['lampiran', 'bukti'])->default('lampiran');
            $table->string('nama_file', 255);
            $table->string('path_file', 255);
            $table->unsignedInteger('ukuran_kb')->default(0);
            $table->timestamps();

            $table->foreign('id_surat')->references('id_surat')->on('surat')->cascadeOnDelete();
            $table->foreign('id_disposisi')->references('id_disposisi')->on('disposisi')->nullOnDelete();
        });

        Schema::create('riwayat_status', function (Blueprint $table) {
            $table->increments('id_riwayat');
            $table->unsignedInteger('id_surat');
            $table->unsignedInteger('id_pengguna')->nullable();
            $table->enum('status', Surat::STATUSES);
            $table->dateTime('waktu');
            $table->text('keterangan')->nullable();

            $table->foreign('id_surat')->references('id_surat')->on('surat')->cascadeOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->increments('id_notifikasi');
            $table->unsignedInteger('id_pengguna');
            $table->unsignedInteger('id_surat')->nullable();
            $table->text('pesan');
            $table->string('tipe_notifikasi', 50);
            $table->boolean('sudah_dibaca')->default(false);
            $table->dateTime('waktu');

            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
            $table->foreign('id_surat')->references('id_surat')->on('surat')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('riwayat_status');
        Schema::dropIfExists('lampiran');
        Schema::dropIfExists('disposisi');
        Schema::dropIfExists('surat');
    }
};
