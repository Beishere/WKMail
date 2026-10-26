@extends('layouts.app', [
    'title' => 'Kelola Unit & Struktur Organisasi',
    'subtitle' => 'Rektorat, Wakil Rektor, Unit Teknis (BAA/BAU/HMJ), Fakultas, HMJ, Alumni, Yayasan, dan Pihak Eksternal',
])

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('master.unit.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama unit..." class="form-input py-1.5 text-xs">
            <select name="jenis" class="form-input py-1.5 text-xs w-auto">
                <option value="">Semua Jenis</option>
                @foreach(\App\Models\Unit::JENIS as $k => $lbl)
                    <option value="{{ $k }}" @selected(request('jenis') === $k)>{{ $lbl }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary btn-sm">Filter</button>
        </form>

        <a href="{{ route('master.unit.create') }}" class="btn-primary btn-sm">+ Tambah Unit</a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Nama Unit</th>
                    <th>Jenis Unit</th>
                    <th>Unit Induk</th>
                    <th>Kode Sumber Default</th>
                    <th>Pengguna</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($units as $u)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $u->nama_unit }}</td>
                        <td><span class="badge bg-slate-100 text-slate-700 ring-slate-400/20">{{ $u->jenis_label }}</span></td>
                        <td class="text-xs text-slate-500">{{ $u->induk?->nama_unit ?? '—' }}</td>
                        <td class="font-mono text-xs">Kode {{ $u->kode_sumber_default }}</td>
                        <td class="text-xs">{{ $u->pengguna_count }} akun</td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('master.unit.edit', $u) }}" class="btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('master.unit.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus unit ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($units->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $units->links() }}</div>
        @endif
    </div>
@endsection
