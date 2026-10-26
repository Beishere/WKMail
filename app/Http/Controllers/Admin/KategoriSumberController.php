<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriSumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriSumberController extends Controller
{
    public function index(): View
    {
        return view('master.kode.index', [
            'jenis' => 'kategori',
            'judul' => 'Kategori Sumber Surat',
            'items' => KategoriSumber::withCount('surat')->orderBy('kode_sumber')->get(),
        ]);
    }

    public function create(): View
    {
        return view('master.kode.form', ['jenis' => 'kategori', 'item' => new KategoriSumber()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'alpha', 'size:1', 'unique:kategori_sumber,kode_sumber'],
            'nama' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ]);

        KategoriSumber::create([
            'kode_sumber' => strtoupper($data['kode']),
            'nama_kategori' => $data['nama'],
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('master.kategori-sumber.index')->with('success', 'Kategori sumber ditambahkan.');
    }

    public function edit(KategoriSumber $kategori): View
    {
        return view('master.kode.form', ['jenis' => 'kategori', 'item' => $kategori]);
    }

    public function update(Request $request, KategoriSumber $kategori): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $kategori->update(['nama_kategori' => $data['nama'], 'keterangan' => $data['keterangan'] ?? null]);

        return redirect()->route('master.kategori-sumber.index')->with('success', 'Kategori sumber diperbarui.');
    }

    public function destroy(KategoriSumber $kategori): RedirectResponse
    {
        if ($kategori->surat()->exists()) {
            return back()->with('error', 'Kategori sudah digunakan pada surat sehingga tidak dapat dihapus.');
        }
        $kategori->delete();

        return back()->with('success', 'Kategori sumber dihapus.');
    }
}
