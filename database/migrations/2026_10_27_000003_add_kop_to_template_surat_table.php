<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_surat', function (Blueprint $table) {
            $table->string('path_kop', 255)->nullable()->after('isi_template');
            $table->string('nama_file_template', 255)->nullable()->after('path_kop');
            $table->string('path_file_template', 255)->nullable()->after('nama_file_template');
        });
    }

    public function down(): void
    {
        Schema::table('template_surat', function (Blueprint $table) {
            $table->dropColumn(['path_kop', 'nama_file_template', 'path_file_template']);
        });
    }
};
