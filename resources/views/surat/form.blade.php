@php
    $isEdit = $surat->exists;
    $initialTpl = $surat->template;
@endphp

@extends('layouts.app', [
    'title' => $isEdit ? 'Edit / Revisi Surat' : 'Buat & Kirim Surat Baru',
    'subtitle' => 'Gunakan template surat untuk menampilkan kop surat serta mengisi perihal dan draf isi surat secara otomatis',
])

@section('content')
    <div class="mx-auto max-w-5xl">
        @if($isEdit && $surat->catatan_admin && $surat->status_terkini === \App\Models\Surat::PERLU_REVISI)
            <div class="mb-3 rounded-xl border border-rose-300 bg-rose-50 px-3.5 py-2 text-xs text-rose-900">
                <span class="font-bold">Catatan Revisi dari Admin Surat Rektorat:</span> {{ $surat->catatan_admin }}
            </div>
        @endif

        <div class="card"
             x-data="{
                loadingTpl: false,
                kopUrl: '{{ $initialTpl?->path_kop ? route('template.kop', $initialTpl) : '' }}',
                fileTplUrl: '{{ $initialTpl?->path_file_template ? route('template.unduh', $initialTpl) : '' }}',
                fileTplNama: '{{ $initialTpl?->nama_file_template ?? '' }}',
                pilihTemplate(e) {
                    const id = e.target.value;
                    if (!id) {
                        this.kopUrl = '';
                        this.fileTplUrl = '';
                        this.fileTplNama = '';
                        return;
                    }
                    this.loadingTpl = true;
                    fetch(`{{ url('/template') }}/${id}/render`, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.$refs.perihal.value = data.perihal;
                        this.$refs.isi.value = data.isi;
                        this.kopUrl = data.kop_url || '';
                        this.fileTplUrl = data.file_template_url || '';
                        this.fileTplNama = data.nama_file_template || '';
                    })
                    .finally(() => { this.loadingTpl = false; });
                }
             }">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <h2 class="card-title">{{ $isEdit ? 'Formulir Edit / Revisi Surat (' . $surat->nomor_surat . ')' : 'Formulir Pengajuan Surat Masuk' }}</h2>
                    <template x-if="fileTplUrl">
                        <a :href="fileTplUrl" class="btn-secondary btn-sm text-brand-700">
                            ↓ Unduh Kop Word (<span x-text="fileTplNama"></span>)
                        </a>
                    </template>
                </div>
                <a href="{{ $isEdit ? route('surat.show', $surat) : route('surat.index') }}" class="btn-secondary btn-sm">Kembali</a>
            </div>

            <form method="POST"
                  action="{{ $isEdit ? route('surat.update', $surat) : route('surat.store') }}"
                  enctype="multipart/form-data"
                  class="card-body space-y-3">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                    <div class="sm:col-span-2 lg:col-span-5">
                        <label class="form-label">Template & Kop Surat Otomatis</label>
                        <select name="id_template" @change="pilihTemplate($event)" class="form-input">
                            <option value="">— Pilih Template Isi Otomatis —</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id_template }}" @selected(old('id_template', $surat->id_template) == $tpl->id_template)>
                                    {{ $tpl->nama_template }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="form-label">Nomor Surat</label>
                        <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $surat->nomor_surat) }}"
                               placeholder="Otomatis jika kosong" class="form-input">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="form-label">Tanggal Surat <span class="text-rose-600">*</span></label>
                        <input type="date" name="tanggal_surat"
                               value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d') ?? now()->toDateString()) }}"
                               required class="form-input">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="form-label">Sifat Surat <span class="text-rose-600">*</span></label>
                        <select name="sifat_surat" required class="form-input">
                            <option value="Biasa" @selected(old('sifat_surat', $surat->sifat_surat) === 'Biasa')>Biasa</option>
                            <option value="Urgent" @selected(old('sifat_surat', $surat->sifat_surat) === 'Urgent')>Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <label class="form-label">Perihal Surat <span class="text-rose-600">*</span></label>
                        <input x-ref="perihal" type="text" name="perihal"
                               value="{{ old('perihal', $surat->perihal) }}" required
                               placeholder="Contoh: Permohonan Izin Pelaksanaan Seminar Nasional" class="form-input">
                    </div>

                    <div class="lg:col-span-4">
                        <label class="form-label">Pengirim & Unit Asal</label>
                        <input type="text" disabled
                               value="{{ auth()->user()->nama }} ({{ auth()->user()->unit?->nama_unit ?? '-' }})"
                               class="form-input bg-slate-100 text-slate-600">
                    </div>
                </div>

                <div>
                    <label class="form-label">Isi / Ringkasan Surat</label>
                    <template x-if="kopUrl">
                        <div class="mb-1.5 rounded-t-xl border border-b-0 border-slate-300 bg-white p-2 text-center">
                            <img :src="kopUrl" alt="Kop Surat" class="mx-auto max-h-20 object-contain">
                        </div>
                    </template>
                    <textarea x-ref="isi" name="isi_surat" rows="6"
                              placeholder="Tuliskan isi surat atau pilih template di atas..."
                              class="form-input font-mono text-xs leading-relaxed">{{ old('isi_surat', $surat->isi_surat) }}</textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-2.5">
                    <div class="flex-1 min-w-[260px]">
                        <label class="form-label">Unggah Berkas Lampiran (PDF/Word/Excel/Gambar/ZIP, maks. 10 MB)</label>
                        <input type="file" name="lampiran[]" multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                               class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-1.5 text-xs text-slate-700 file:mr-2.5 file:rounded-md file:border-0 file:bg-brand-600 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700">
                    </div>
                    <div class="flex items-center gap-2 pt-3">
                        <a href="{{ $isEdit ? route('surat.show', $surat) : route('surat.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">
                            {{ $isEdit ? 'Simpan Perubahan Surat' : 'Kirim Surat ke Rektorat' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if($isEdit && $surat->lampiran->isNotEmpty())
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Lampiran Saat Ini ({{ $surat->lampiran->count() }})</h3>
                </div>
                <div class="card-body flex flex-wrap gap-2">
                    @foreach($surat->lampiran as $lamp)
                        <div class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs">
                            <a href="{{ route('lampiran.unduh', $lamp) }}" class="font-semibold text-brand-600 hover:underline">{{ $lamp->nama_file }}</a>
                            <span class="text-slate-400">({{ $lamp->ukuran_label }})</span>
                            <form method="POST" action="{{ route('lampiran.destroy', $lamp) }}" onsubmit="return confirm('Hapus lampiran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 font-bold hover:underline">×</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
