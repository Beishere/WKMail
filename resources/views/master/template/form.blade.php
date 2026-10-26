@php $isEdit = $template->exists; @endphp
@extends('layouts.app', [
    'title' => $isEdit ? 'Edit Template Surat' : 'Tambah Template Surat',
])

@section('content')
    <div class="mx-auto max-w-3xl card">
        <div class="card-header">
            <h2 class="card-title">{{ $isEdit ? 'Form Edit Template Surat' : 'Form Tambah Template Surat' }}</h2>
            <a href="{{ route('master.template.index') }}" class="btn-secondary btn-sm">Kembali</a>
        </div>

        <form method="POST" action="{{ $isEdit ? route('master.template.update', $template) : route('master.template.store') }}" class="card-body space-y-4">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Nama Template <span class="text-rose-600">*</span></label>
                    <input type="text" name="nama_template" value="{{ old('nama_template', $template->nama_template) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Perihal Default Otomatis <span class="text-rose-600">*</span></label>
                    <input type="text" name="perihal_default" value="{{ old('perihal_default', $template->perihal_default) }}" required class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Isi Template Surat <span class="text-rose-600">*</span></label>
                <textarea name="isi_template" rows="12" required class="form-input font-mono text-xs leading-relaxed">{{ old('isi_template', $template->isi_template) }}</textarea>
                <p class="form-hint">Placeholder otomatis: <code>{nama_pengirim}</code>, <code>{unit_pengirim}</code>, <code>{nip}</code>, <code>{tanggal}</code>, <code>{perihal}</code>.</p>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('master.template.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Template</button>
            </div>
        </form>
    </div>
@endsection
