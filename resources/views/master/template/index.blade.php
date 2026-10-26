@extends('layouts.app', [
    'title' => 'Kelola Template & Kop Surat Otomatis',
    'subtitle' => 'Atur gambar kop surat resmi, file master dokumen (.docx/.pdf), perihal default, dan draf isi surat',
])

@section('content')
    <div class="mb-5 flex justify-end">
        <a href="{{ route('master.template.create') }}" class="btn-primary btn-sm">+ Tambah Template Surat</a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Nama Template</th>
                    <th>Perihal Default</th>
                    <th>Kop Surat & File Master</th>
                    <th>Digunakan</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($templates as $tpl)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $tpl->nama_template }}</td>
                        <td class="whitespace-normal max-w-md text-xs text-slate-600">{{ $tpl->perihal_default }}</td>
                        <td class="space-x-1">
                            @if($tpl->path_kop)
                                <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-500/20">Ada Gambar Kop</span>
                            @endif
                            @if($tpl->path_file_template)
                                <a href="{{ route('template.unduh', $tpl) }}" class="badge bg-brand-50 text-brand-700 ring-brand-500/20 hover:underline">
                                    {{ $tpl->nama_file_template }} ↓
                                </a>
                            @endif
                            @if(! $tpl->path_kop && ! $tpl->path_file_template)
                                <span class="text-xs text-slate-400">Teks standar</span>
                            @endif
                        </td>
                        <td class="text-xs">{{ $tpl->surat_count }} surat</td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('master.template.edit', $tpl) }}" class="btn-secondary btn-sm">Edit / Upload Kop</a>
                            <form method="POST" action="{{ route('master.template.destroy', $tpl) }}" class="inline" onsubmit="return confirm('Hapus template ini?')">
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
