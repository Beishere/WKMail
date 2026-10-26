<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profil', ['user' => $request->user()->load('role', 'unit')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150', Rule::unique('pengguna', 'email')->ignore($user->id_pengguna, 'id_pengguna')],
        ]);

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], ['password_lama' => 'password lama', 'password' => 'password baru']);

        $request->user()->update(['password_hash' => $request->password]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
