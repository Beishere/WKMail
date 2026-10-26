<?php

use App\Http\Controllers\Admin\KategoriSumberController;
use App\Http\Controllers\Admin\KodeBidangController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\TemplateSuratController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\LacakController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RektorController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\VerifikasiController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // ---------------------------------------------------------------- Surat (umum)
    Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
    Route::middleware('role:pengirim,admin')->group(function () {
        Route::get('/surat/buat', [SuratController::class, 'create'])->name('surat.create');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
        Route::get('/surat/{surat}/revisi', [SuratController::class, 'edit'])->name('surat.edit');
        Route::put('/surat/{surat}', [SuratController::class, 'update'])->name('surat.update');
        Route::delete('/surat/{surat}', [SuratController::class, 'destroy'])->name('surat.destroy');
        Route::delete('/lampiran/{lampiran}', [SuratController::class, 'hapusLampiran'])->name('lampiran.destroy');
        Route::get('/template/{template}/render', [SuratController::class, 'renderTemplate'])->name('template.render');
    });
    Route::get('/surat/{surat}', [SuratController::class, 'show'])->name('surat.show');
    Route::get('/lampiran/{lampiran}/unduh', [SuratController::class, 'unduh'])->name('lampiran.unduh');

    // ---------------------------------------------------------------- Admin Surat Rektorat (filter utama)
    Route::middleware('role:admin')->group(function () {
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('/surat/{surat}/validasi', [VerifikasiController::class, 'validasi'])->name('verifikasi.validasi');
        Route::post('/surat/{surat}/tolak', [VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');

        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('pengguna', PenggunaController::class)->except('show')
                ->parameters(['pengguna' => 'pengguna']);
            Route::resource('unit', UnitController::class)->except('show');
            Route::resource('template', TemplateSuratController::class)->except('show')
                ->parameters(['template' => 'template']);
            Route::resource('kategori-sumber', KategoriSumberController::class)->except('show')
                ->parameters(['kategori-sumber' => 'kategori']);
            Route::resource('kode-bidang', KodeBidangController::class)->except('show')
                ->parameters(['kode-bidang' => 'bidang']);
        });
    });

    // ---------------------------------------------------------------- Rektor
    Route::middleware('role:rektor')->group(function () {
        Route::get('/surat-masuk', [RektorController::class, 'index'])->name('rektor.index');
        Route::post('/surat/{surat}/disposisi', [RektorController::class, 'disposisi'])->name('rektor.disposisi');
        Route::post('/surat/{surat}/arsip', [RektorController::class, 'arsip'])->name('rektor.arsip');
    });

    // ---------------------------------------------------------------- Wakil Rektor & Unit Teknis
    Route::middleware('role:wakil_rektor,unit_teknis')->group(function () {
        Route::get('/disposisi', [DisposisiController::class, 'index'])->name('disposisi.index');
        Route::post('/disposisi/{disposisi}/teruskan', [DisposisiController::class, 'teruskan'])
            ->middleware('role:wakil_rektor')->name('disposisi.teruskan');
        Route::post('/disposisi/{disposisi}/progres', [DisposisiController::class, 'progres'])->name('disposisi.progres');
        Route::post('/disposisi/{disposisi}/selesai', [DisposisiController::class, 'selesai'])->name('disposisi.selesai');
    });

    // ---------------------------------------------------------------- Laporan
    Route::middleware('role:admin,rektor')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
        Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    });

    // ---------------------------------------------------------------- Umum
    Route::get('/lacak', LacakController::class)->name('lacak');

    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{notifikasi}/buka', [NotifikasiController::class, 'buka'])->name('notifikasi.buka');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfilController::class, 'password'])->name('profil.password');
});
