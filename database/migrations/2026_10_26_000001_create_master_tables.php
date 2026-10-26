<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel master sesuai ERD: ROLE, UNIT, PENGGUNA, KATEGORI_SUMBER, KODE_BIDANG, TEMPLATE_SURAT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->increments('id_role');
            $table->string('kode_role', 30)->unique(); // slug internal: admin, rektor, wakil_rektor, unit_teknis, pengirim
            $table->string('nama_role', 100);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('unit', function (Blueprint $table) {
            $table->increments('id_unit');
            $table->string('nama_unit', 150);
            $table->enum('jenis_unit', [
                'rektorat', 'wakil_rektor', 'unit_teknis', 'fakultas', 'hmj', 'alumni', 'yayasan', 'eksternal',
            ]);
            $table->unsignedInteger('id_induk')->nullable();
            $table->timestamps();

            $table->foreign('id_induk')->references('id_unit')->on('unit')->nullOnDelete();
        });

        Schema::create('pengguna', function (Blueprint $table) {
            $table->increments('id_pengguna');
            $table->string('nip', 50)->nullable();
            $table->string('nama', 150);
            $table->string('email', 150)->unique();
            $table->string('password_hash');
            $table->unsignedInteger('id_role');
            $table->unsignedInteger('id_unit')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_role')->references('id_role')->on('role');
            $table->foreign('id_unit')->references('id_unit')->on('unit')->nullOnDelete();
        });

        Schema::create('kategori_sumber', function (Blueprint $table) {
            $table->char('kode_sumber', 1)->primary();
            $table->string('nama_kategori', 100);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('kode_bidang', function (Blueprint $table) {
            $table->char('kode_bidang', 1)->primary();
            $table->string('nama_bidang', 100);
            $table->text('keterangan')->nullable();
            // Saran Wakil Rektor penanggung jawab bidang (membantu admin menentukan tujuan disposisi)
            $table->unsignedInteger('id_unit_wr')->nullable();
            $table->timestamps();

            $table->foreign('id_unit_wr')->references('id_unit')->on('unit')->nullOnDelete();
        });

        Schema::create('template_surat', function (Blueprint $table) {
            $table->increments('id_template');
            $table->string('nama_template', 150);
            $table->string('perihal_default', 255);
            $table->text('isi_template');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_surat');
        Schema::dropIfExists('kode_bidang');
        Schema::dropIfExists('kategori_sumber');
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('unit');
        Schema::dropIfExists('role');
    }
};
