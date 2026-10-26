@extends('layouts.app', [
    'title' => 'Kelola Pengguna & Hak Akses',
    'subtitle' => 'Manajemen akun Pengirim, Admin Rektorat, Rektor, Wakil Rektor, dan Staf Unit Teknis',
])

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('master.pengguna.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email / NIP..." class="form-input py-1.5 text-xs w-60">
            <select name="role" class="form-input py-1.5 text-xs w-auto">
                <option value="">Semua Peran</option>
                @foreach($roles as $r)
                    <option value="{{ $r->id_role }}" @selected(request('role') == $r->id_role)>{{ $r->nama_role }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary btn-sm">Filter</button>
        </form>

        <a href="{{ route('master.pengguna.create') }}" class="btn-primary btn-sm">+ Tambah Pengguna</a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Nama & NIP</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($pengguna as $p)
                    <tr>
                        <td>
                            <p class="font-semibold text-slate-800">{{ $p->nama }}</p>
                            <p class="text-xs text-slate-400">NIP: {{ $p->nip ?: '—' }}</p>
                        </td>
                        <td class="text-xs font-mono">{{ $p->email }}</td>
                        <td><span class="badge bg-brand-50 text-brand-700 ring-brand-500/20">{{ $p->role?->nama_role }}</span></td>
                        <td class="text-xs">{{ $p->unit?->nama_unit ?? '—' }}</td>
                        <td>
                            @if($p->status_aktif)
                                <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-500/20">Aktif</span>
                            @else
                                <span class="badge bg-slate-100 text-slate-500 ring-slate-400/20">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('master.pengguna.edit', $p) }}" class="btn-secondary btn-sm">Edit</a>
                            @if($p->id_pengguna !== auth()->id())
                                <form method="POST" action="{{ route('master.pengguna.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus/nonaktifkan pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($pengguna->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $pengguna->links() }}</div>
        @endif
    </div>
@endsection
