@extends('layouts.app', [
    'title' => 'Profil & Keamanan Akun',
    'subtitle' => 'Perbarui informasi identitas dan kata sandi akun Anda',
])

@section('content')
    <div class="mx-auto max-w-4xl grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Informasi Profil</h2>
            </div>
            <form method="POST" action="{{ route('profil.update') }}" class="card-body space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama', $user->nama) }}" required class="form-input">
                </div>

                <div>
                    <label class="form-label">NIP / Nomor Induk</label>
                    <input type="text" name="nip" value="{{ old('nip', $user->nip) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Peran</label>
                        <input type="text" disabled value="{{ $user->role?->nama_role }}" class="form-input bg-slate-100 text-slate-600">
                    </div>
                    <div>
                        <label class="form-label">Unit</label>
                        <input type="text" disabled value="{{ $user->unit?->nama_unit ?? '-' }}" class="form-input bg-slate-100 text-slate-600">
                    </div>
                </div>

                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Ubah Password</h2>
            </div>
            <form method="POST" action="{{ route('profil.password') }}" class="card-body space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">Password Saat Ini</label>
                    <input type="password" name="password_lama" required class="form-input">
                </div>

                <div>
                    <label class="form-label">Password Baru (min. 8 karakter)</label>
                    <input type="password" name="password" required class="form-input">
                </div>

                <div>
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required class="form-input">
                </div>

                <button type="submit" class="btn-primary">Perbarui Password</button>
            </form>
        </div>
    </div>
@endsection
