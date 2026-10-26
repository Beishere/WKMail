<?php

namespace App\Providers;

use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->header('X-Forwarded-Proto') === 'https' || request()->isSecure()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Carbon::setLocale(config('app.locale', 'id'));
        Paginator::useTailwind();

        // Data global untuk layout: jumlah & daftar notifikasi terbaru
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            if (! $user) {
                return;
            }

            $view->with('notifBelumDibaca', Notifikasi::where('id_pengguna', $user->id_pengguna)
                ->where('sudah_dibaca', false)->count());
            $view->with('notifTerbaru', Notifikasi::where('id_pengguna', $user->id_pengguna)
                ->latest('waktu')->latest('id_notifikasi')->limit(6)->get());
        });
    }
}
