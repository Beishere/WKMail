<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Role;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $pengguna = Pengguna::with('role', 'unit')
            ->when($request->q, fn ($q, $kw) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$kw}%")
                ->orWhere('email', 'like', "%{$kw}%")->orWhere('nip', 'like', "%{$kw}%")))
            ->when($request->role, fn ($q, $v) => $q->where('id_role', $v))
            ->orderBy('id_role')->orderBy('nama')
            ->paginate(15)->withQueryString();

        return view('master.pengguna.index', ['pengguna' => $pengguna, 'roles' => Role::all()]);
    }

    public function create(): View
    {
        return $this->form(new Pengguna(['status_aktif' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        Pengguna::create($this->validated($request));

        return redirect()->route('master.pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(Pengguna $pengguna): View
    {
        return $this->form($pengguna);
    }

    public function update(Request $request, Pengguna $pengguna): RedirectResponse
    {
        $pengguna->update($this->validated($request, $pengguna));

        return redirect()->route('master.pengguna.index')->with('success', 'Data pengguna diperbarui.');
    }

    public function destroy(Request $request, Pengguna $pengguna): RedirectResponse
    {
        if ($pengguna->id_pengguna === $request->user()->id_pengguna) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($pengguna->surat()->exists()) {
            $pengguna->update(['status_aktif' => false]);

            return back()->with('success', 'Pengguna memiliki riwayat surat, sehingga hanya dinonaktifkan.');
        }

        $pengguna->delete();

        return back()->with('success', 'Pengguna dihapus.');
    }

    protected function form(Pengguna $pengguna): View
    {
        return view('master.pengguna.form', [
            'pengguna' => $pengguna,
            'roles' => Role::all(),
            'units' => Unit::orderBy('jenis_unit')->orderBy('nama_unit')->get(),
        ]);
    }

    protected function validated(Request $request, ?Pengguna $pengguna = null): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150',
                Rule::unique('pengguna', 'email')->ignore($pengguna?->id_pengguna, 'id_pengguna')],
            'id_role' => ['required', 'exists:role,id_role'],
            'id_unit' => ['nullable', 'exists:unit,id_unit'],
            'password' => [$pengguna ? 'nullable' : 'required', Password::min(8)],
        ]);

        $data['status_aktif'] = $request->boolean('status_aktif');

        if (! empty($data['password'])) {
            $data['password_hash'] = $data['password'];
        }
        unset($data['password']);

        return $data;
    }
}
