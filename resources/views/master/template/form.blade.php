@php $isEdit = $template->exists; @endphp
@extends('layouts.app', [
    'title' => $isEdit ? 'Edit Template & Kop Surat' : 'Tambah Template & Kop Surat',
])

@section('content')
    <div class="mx-auto max-w-3xl card">
        <div class="card-header">
            <h2 class="card-title">{{ $isEdit ? 'Form Edit Template & Kop Surat' : 'Form Tambah Template & Kop Surat' }}</h2>
            <a href="{{ route('master.template.index') }}" class="btn-secondary btn-sm">Kembali</a>
        </div>

        <form method="POST"
              action="{{ $isEdit ? route('master.template.update', $template) : route('master.template.store') }}"
              enctype="multipart/form-data"
              class="card-body space-y-5">
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

            <!-- Upload Gambar Kop Surat -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                <div>
                    <label class="form-label">1. Upload Gambar Kop Surat Resmi (PNG / JPG / WEBP, maks. 5 MB)</label>
                    <p class="form-hint mb-2">Gambar kop surat akan otomatis tampil di bagian paling atas surat pada halaman detail & saat cetak surat.</p>
                    <input type="file" name="file_kop" accept=".png,.jpg,.jpeg,.webp"
                           class="block w-full rounded-lg border border-slate-300 bg-white p-2 text-xs text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700">
                </div>

                <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer">
                    <input type="checkbox" name="terapkan_semua_kop" value="1" checked
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Terapkan gambar kop surat ini ke seluruh template surat lainnya sekaligus
                </label>

                @if($isEdit && $template->path_kop)
                    <div class="mt-2 rounded-lg border border-slate-200 bg-white p-3">
                        <div class="mb-2 flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-700">Preview Kop Surat Saat Ini:</span>
                            <label class="inline-flex items-center gap-1.5 text-rose-600 cursor-pointer">
                                <input type="checkbox" name="hapus_kop" value="1" class="rounded border-slate-300 text-rose-600">
                                Hapus kop surat
                            </label>
                        </div>
                        <img src="{{ route('template.kop', $template) }}" alt="Kop Surat" class="max-h-36 w-full object-contain border-b border-slate-200 pb-2">
                    </div>
                @endif
            </div>

            <!-- Upload File Master Dokumen Template (.docx / .pdf) -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                <label class="form-label">2. Upload File Dokumen Template Kop Surat (Word .docx / .doc / .pdf, maks. 10 MB — Opsional)</label>
                <p class="form-hint">Jika diunggah, pengirim surat dapat mengunduh file Word/PDF kop surat ini saat membuat surat baru.</p>
                <input type="file" name="file_template" accept=".doc,.docx,.pdf"
                       class="block w-full rounded-lg border border-slate-300 bg-white p-2 text-xs text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-700 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-slate-800">

                @if($isEdit && $template->path_file_template)
                    <div class="flex items-center justify-between pt-2 text-xs">
                        <a href="{{ route('template.unduh', $template) }}" class="font-semibold text-brand-600 hover:underline">
                            Berkas saat ini: {{ $template->nama_file_template }} ↓
                        </a>
                        <label class="inline-flex items-center gap-1.5 text-rose-600 cursor-pointer">
                            <input type="checkbox" name="hapus_file_template" value="1" class="rounded border-slate-300 text-rose-600">
                            Hapus file dokumen
                        </label>
                    </div>
                @endif
            </div>

            <div>
                <label class="form-label">3. Isi Draf Template Surat <span class="text-rose-600">*</span></label>
                <textarea name="isi_template" rows="12" required class="form-input font-mono text-xs leading-relaxed">{{ old('isi_template', $template->isi_template) }}</textarea>
                <p class="form-hint">Placeholder otomatis: <code>{nama_pengirim}</code>, <code>{unit_pengirim}</code>, <code>{nip}</code>, <code>{tanggal}</code>, <code>{perihal}</code>.</p>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('master.template.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Template & Kop Surat</button>
            </div>
        </form>
    </div>
@endsection
