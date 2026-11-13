<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->string('status_sebelum_arsip', 30)->nullable()->after('status_terkini');
        });
    }

    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropColumn('status_sebelum_arsip');
        });
    }
};
