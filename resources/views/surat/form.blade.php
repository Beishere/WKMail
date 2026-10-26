@php
    $isEdit = $surat->exists;
    $initialTpl = $surat->template;
@endphp

@extends('layouts.app', [
    'title' => $isEdit ? 'Edit / Revisi Surat' : 'Buat & Kirim Surat Baru',
    'subtitle' => 'Ketik langsung di web, gunakan template otomatis, atau unduh & edit surat di Microsoft Word (.docx)',
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
                importingWord: false,
                wordStatus: '',
                wordError: '',
                kopUrl: '{{ $initialTpl?->path_kop ? route('template.kop', $initialTpl) : asset('kop-ukwk.jpeg') }}',
                fileTplUrl: '{{ $initialTpl?->path_file_template ? route('template.unduh', $initialTpl) : asset('kop-ukwk.docx') }}',
                fileTplNama: '{{ $initialTpl?->nama_file_template ?? 'Kop-Surat-UKWK.docx' }}',
                pilihTemplate(e) {
                    const id = e.target.value;
                    if (!id) {
                        this.kopUrl = '{{ asset('kop-ukwk.jpeg') }}';
                        this.fileTplUrl = '{{ asset('kop-ukwk.docx') }}';
                        this.fileTplNama = 'Kop-Surat-UKWK.docx';
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
                        this.kopUrl = data.kop_url || '{{ asset('kop-ukwk.jpeg') }}';
                        this.fileTplUrl = data.file_template_url || '{{ asset('kop-ukwk.docx') }}';
                        this.fileTplNama = data.nama_file_template || 'Kop-Surat-UKWK.docx';
                    })
                    .finally(() => { this.loadingTpl = false; });
                },
                unduhDrafWord() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('surat.word-draft') }}';
                    const fields = {
                        _token: '{{ csrf_token() }}',
                        nomor_surat: this.$refs.nomor ? this.$refs.nomor.value : '',
                        tanggal_surat: this.$refs.tanggal ? this.$refs.tanggal.value : '',
                        sifat_surat: this.$refs.sifat ? this.$refs.sifat.value : 'Biasa',
                        perihal: this.$refs.perihal ? this.$refs.perihal.value : '',
                        isi_surat: this.$refs.isi ? this.$refs.isi.value : '',
                    };
                    for (const [k, v] of Object.entries(fields)) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = k;
                        input.value = v;
                        form.appendChild(input);
                    }
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                },
                imporDariWord(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.importingWord = true;
                    this.wordStatus = '';
                    this.wordError = '';

                    const fd = new FormData();
                    fd.append('_token', '{{ csrf_token() }}');
                    fd.append('file_word', file);

                    fetch('{{ route('surat.import-word') }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: fd,
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok || !data.ok) {
                            throw new Error(data.message || 'Gagal membaca berkas Word (.docx).');
                        }
                        if (data.perihal && this.$refs.perihal) {
                            this.$refs.perihal.value = data.perihal;
                        }
                        if (data.nomor_surat && this.$refs.nomor && !this.$refs.nomor.value) {
                            this.$refs.nomor.value = data.nomor_surat;
                        }
                        if (data.sifat_surat && this.$refs.sifat) {
                            this.$refs.sifat.value = data.sifat_surat;
                        }
                        if (typeof data.isi_surat === 'string' && this.$refs.isi) {
                            this.$refs.isi.value = data.isi_surat;
                        }
                        this.wordStatus = `Isi surat berhasil disinkronkan dari berkas Word '${data.nama_file}' dan akan dilampirkan otomatis saat disimpan.`;
                    })
                    .catch(err => {
                        this.wordError = err.message || 'Pastikan berkas berformat Microsoft Word (.docx).';
                    })
                    .finally(() => {
                        this.importingWord = false;
                    });
                }
             }">
            <div class="card-header">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="card-title">{{ $isEdit ? 'Formulir Edit / Revisi Surat (' . $surat->nomor_surat . ')' : 'Formulir Pengajuan Surat Masuk' }}</h2>
                    <button type="button" @click="unduhDrafWord()" class="btn-secondary btn-sm text-brand-700 border-brand-200 bg-brand-50/60 hover:bg-brand-100/70">
                        ↓ Edit Surat Ini di Microsoft Word (.docx)
                    </button>
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
                        <input x-ref="nomor" type="text" name="nomor_surat" value="{{ old('nomor_surat', $surat->nomor_surat) }}"
                               placeholder="Otomatis jika kosong" class="form-input">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="form-label">Tanggal Surat <span class="text-rose-600">*</span></label>
                        <input x-ref="tanggal" type="date" name="tanggal_surat"
                               value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d') ?? now()->toDateString()) }}"
                               required class="form-input">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="form-label">Sifat Surat <span class="text-rose-600">*</span></label>
                        <select x-ref="sifat" name="sifat_surat" required class="form-input">
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

                <!-- Panel Integrasi Microsoft Word (.docx) -->
                <div class="rounded-xl border border-brand-200 bg-brand-50/40 p-3">
                    <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-brand-800">
                                Mode Edit dengan Microsoft Word (.docx) Ber-Kop Resmi UKWK
                            </p>
                            <p class="text-[11px] text-slate-600 leading-snug">
                                1) Klik <span class="font-semibold text-brand-700">Unduh Draf .docx</span> untuk mengedit surat di Microsoft Word →
                                2) Simpan di Word lalu pilih berkasnya pada tombol <span class="font-semibold text-brand-700">Impor Hasil Edit Word</span> agar isi surat di web otomatis diperbarui.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <button type="button" @click="unduhDrafWord()"
                                    class="btn-secondary btn-sm border-brand-300 bg-white text-brand-700 hover:bg-brand-50">
                                ↓ 1. Unduh Draf .docx
                            </button>
                            <label class="btn-primary btn-sm cursor-pointer">
                                <span x-show="!importingWord">↑ 2. Impor Hasil Edit Word (.docx)</span>
                                <span x-show="importingWord" x-cloak>Membaca Word...</span>
                                <input type="file" name="file_word_edit" accept=".docx" class="hidden" @change="imporDariWord($event)">
                            </label>
                        </div>
                    </div>

                    <template x-if="wordStatus">
                        <div class="mt-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-800" x-text="'✓ ' + wordStatus"></div>
                    </template>
                    <template x-if="wordError">
                        <div class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-800" x-text="'⚠ ' + wordError"></div>
                    </template>
                </div>

                <div>
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                        <label class="form-label mb-0">Isi / Ringkasan Surat (Otomatis Sinkron dengan Word)</label>
                        <template x-if="fileTplUrl">
                            <a :href="fileTplUrl" class="text-[11px] font-semibold text-brand-600 hover:underline">
                                Unduh Kop Kosong (<span x-text="fileTplNama"></span>)
                            </a>
                        </template>
                    </div>
                    <template x-if="kopUrl">
                        <div class="mb-1.5 rounded-t-xl border border-b-0 border-slate-300 bg-white p-2 text-center">
                            <img :src="kopUrl" alt="Kop Surat UKWK" class="mx-auto max-h-20 object-contain">
                        </div>
                    </template>
                    <textarea x-ref="isi" name="isi_surat" rows="7"
                              placeholder="Tuliskan isi surat di sini, pilih template di atas, atau impor dari berkas Microsoft Word (.docx)..."
                              class="form-input font-mono text-xs leading-relaxed">{{ old('isi_surat', $surat->isi_surat) }}</textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-2.5">
                    <div class="flex-1 min-w-[260px]">
                        <label class="form-label">Lampiran Tambahan (PDF/Word/Excel/Gambar/ZIP, maks. 10 MB)</label>
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
