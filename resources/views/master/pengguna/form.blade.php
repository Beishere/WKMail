@php $isEdit = $pengguna->exists; @endphp
@extends('layouts.app', [
    'title' => $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru',
])

@section('content')
    <div class="mx-auto max-w-2xl card">
        <div class="card-header">
            <h2 class="card-title">{{ $isEdit ? 'Form Edit Pengguna' : 'Form Tambah Pengguna' }}</h2>
            <a href="{{ route('master.pengguna.index') }}" class="btn-secondary btn-sm">Kembali</a>
        </div>

        <form method="POST" action="{{ $isEdit ? route('master.pengguna.update', $pengguna) : route('master.pengguna.store') }}" class="card-body space-y-4">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-rose-600">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $pengguna->nama) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">NIP / Nomor Identitas</label>
                    <input type="text" name="nip" value="{{ old('nip', $pengguna->nip) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Email <span class="text-rose-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $pengguna->email) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Password {{ $isEdit ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
                    <input type="password" name="password" {{ $isEdit ? '' : 'required' }} class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Peran (Role) <span class="text-rose-600">*</span></label>
                    <select name="id_role" required class="form-input">
                        @foreach($roles as $r)
                            <option value="{{ $r->id_role }}" @selected(old('id_role', $pengguna->id_role) == $r->id_role)>{{ $r->nama_role }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Unit / Organisasi</label>
                    <select name="id_unit" class="form-input">
                        <option value="">— Tanpa Unit —</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id_unit }}" @selected(old('id_unit', $pengguna->id_unit) == $u->id_unit)>
                                [{{ $u->jenis_label }}] {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $pengguna->status_aktif))
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Status Akun Aktif
                </label>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('master.pengguna.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
