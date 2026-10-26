@php $isKategori = $jenis === 'kategori'; @endphp
@extends('layouts.app', [
    'title' => $judul,
    'subtitle' => 'Pengelolaan kode klasifikasi sumber surat (A/B/C) dan kode bidang departemen (B, C, D, G, Q)',
])

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-200 text-xs font-semibold gap-1">
            <a href="{{ route('master.kategori-sumber.index') }}"
               class="rounded-lg px-3 py-1.5 {{ $isKategori ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Kategori Sumber Surat (A / B / C)
            </a>
            <a href="{{ route('master.kode-bidang.index') }}"
               class="rounded-lg px-3 py-1.5 {{ ! $isKategori ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Kode Bidang Departemen (B, C, D, G, Q, K)
            </a>
        </div>

        <a href="{{ $isKategori ? route('master.kategori-sumber.create') : route('master.kode-bidang.create') }}" class="btn-primary btn-sm">
            + Tambah {{ $isKategori ? 'Kategori Sumber' : 'Kode Bidang' }}
        </a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    @if(! $isKategori)
                        <th>Saran Wakil Rektor</th>
                    @endif
                    <th>Keterangan</th>
                    <th>Jumlah Surat</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($items as $it)
                    @php
                        $kode = $isKategori ? $it->kode_sumber : $it->kode_bidang;
                        $nama = $isKategori ? $it->nama_kategori : $it->nama_bidang;
                    @endphp
                    <tr>
                        <td><span class="badge bg-brand-50 text-brand-700 ring-brand-500/20 font-mono text-sm">Kode {{ $kode }}</span></td>
                        <td class="font-semibold text-slate-800">{{ $nama }}</td>
                        @if(! $isKategori)
                            <td class="text-xs text-slate-600">{{ $it->unitWr?->nama_unit ?? '—' }}</td>
                        @endif
                        <td class="whitespace-normal max-w-md text-xs text-slate-500">{{ $it->keterangan ?: '—' }}</td>
                        <td class="text-xs">{{ $it->surat_count }} surat</td>
                        <td class="text-right space-x-2">
                            <a href="{{ $isKategori ? route('master.kategori-sumber.edit', $it) : route('master.kode-bidang.edit', $it) }}" class="btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="{{ $isKategori ? route('master.kategori-sumber.destroy', $it) : route('master.kode-bidang.destroy', $it) }}" class="inline" onsubmit="return confirm('Hapus kode ini?')">
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
    </div>
@endsection
