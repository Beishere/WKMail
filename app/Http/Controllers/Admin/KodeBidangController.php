<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KodeBidang;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KodeBidangController extends Controller
{
    public function index(): View
    {
        return view('master.kode.index', [
            'jenis' => 'bidang',
            'judul' => 'Kode Bidang (Departemen)',
            'items' => KodeBidang::with('unitWr')->withCount('surat')->orderBy('kode_bidang')->get(),
        ]);
    }

    public function create(): View
    {
        return view('master.kode.form', ['jenis' => 'bidang', 'item' => new KodeBidang(), 'unitWr' => $this->unitWr()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'alpha', 'size:1', 'unique:kode_bidang,kode_bidang'],
            'nama' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'id_unit_wr' => ['nullable', Rule::exists('unit', 'id_unit')->where('jenis_unit', 'wakil_rektor')],
        ]);

        KodeBidang::create([
            'kode_bidang' => strtoupper($data['kode']),
            'nama_bidang' => $data['nama'],
            'keterangan' => $data['keterangan'] ?? null,
            'id_unit_wr' => $data['id_unit_wr'] ?? null,
        ]);

        return redirect()->route('master.kode-bidang.index')->with('success', 'Kode bidang ditambahkan.');
    }

    public function edit(KodeBidang $bidang): View
    {
        return view('master.kode.form', ['jenis' => 'bidang', 'item' => $bidang, 'unitWr' => $this->unitWr()]);
    }

    public function update(Request $request, KodeBidang $bidang): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'id_unit_wr' => ['nullable', Rule::exists('unit', 'id_unit')->where('jenis_unit', 'wakil_rektor')],
        ]);

        $bidang->update([
            'nama_bidang' => $data['nama'],
            'keterangan' => $data['keterangan'] ?? null,
            'id_unit_wr' => $data['id_unit_wr'] ?? null,
        ]);

        return redirect()->route('master.kode-bidang.index')->with('success', 'Kode bidang diperbarui.');
    }

    public function destroy(KodeBidang $bidang): RedirectResponse
    {
        if ($bidang->surat()->exists()) {
            return back()->with('error', 'Kode bidang sudah digunakan pada surat sehingga tidak dapat dihapus.');
        }
        $bidang->delete();

        return back()->with('success', 'Kode bidang dihapus.');
    }

    protected function unitWr()
    {
        return Unit::where('jenis_unit', 'wakil_rektor')->orderBy('nama_unit')->get();
    }
}
