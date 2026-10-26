<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pelacakan status surat dari awal hingga akhir.
 */
class LacakController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $hasil = collect();
        $terpilih = null;

        if ($request->filled('q')) {
            $hasil = Surat::query()->visibleTo($user)->cari($request->q)
                ->with('pengirim.unit')->latest()->limit(10)->get();

            $terpilih = $request->filled('id')
                ? $hasil->firstWhere('id_surat', (int) $request->id)
                : $hasil->first();

            $terpilih?->load('riwayat.pengguna.role', 'disposisi.unitTujuan', 'kategoriSumber', 'bidang');
        }

        return view('lacak', compact('hasil', 'terpilih'));
    }
}
