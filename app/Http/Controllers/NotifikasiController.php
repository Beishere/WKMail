<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $notifikasi = Notifikasi::where('id_pengguna', $request->user()->id_pengguna)
            ->with('surat')
            ->when($request->filter === 'belum', fn ($q) => $q->where('sudah_dibaca', false))
            ->latest('waktu')->latest('id_notifikasi')
            ->paginate(15)->withQueryString();

        return view('notifikasi.index', compact('notifikasi'));
    }

    public function buka(Request $request, Notifikasi $notifikasi): RedirectResponse
    {
        abort_unless($notifikasi->id_pengguna === $request->user()->id_pengguna, 403);

        $notifikasi->update(['sudah_dibaca' => true]);

        return $notifikasi->id_surat
            ? redirect()->route('surat.show', $notifikasi->id_surat)
            : redirect()->route('notifikasi.index');
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        Notifikasi::where('id_pengguna', $request->user()->id_pengguna)
            ->where('sudah_dibaca', false)->update(['sudah_dibaca' => true]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
