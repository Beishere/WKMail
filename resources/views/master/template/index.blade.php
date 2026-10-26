@extends('layouts.app', [
    'title' => 'Kelola Template Surat Otomatis',
    'subtitle' => 'Template surat mempermudah pengirim mengisi perihal dan draf isi surat secara otomatis',
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
                    <th>Digunakan</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($templates as $tpl)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $tpl->nama_template }}</td>
                        <td class="whitespace-normal max-w-md text-xs text-slate-600">{{ $tpl->perihal_default }}</td>
                        <td class="text-xs">{{ $tpl->surat_count }} surat</td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('master.template.edit', $tpl) }}" class="btn-secondary btn-sm">Edit</a>
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
