@php
    $isEdit = $surat->exists;
@endphp

@extends('layouts.app', [
    'title' => $isEdit ? 'Revisi & Kirim Ulang Surat' : 'Buat & Kirim Surat Baru',
    'subtitle' => 'Gunakan template surat untuk pengisian perihal dan draf isi surat secara otomatis',
])

@section('content')
    <div class="mx-auto max-w-4xl">
        @if($isEdit && $surat->catatan_admin)
            <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900">
                <p class="font-bold">Catatan Revisi dari Admin Surat Rektorat:</p>
                <p class="mt-1">{{ $surat->catatan_admin }}</p>
            </div>
        @endif

        <div class="card"
             x-data="{
                loadingTpl: false,
                pilihTemplate(e) {
                    const id = e.target.value;
                    if (!id) return;
                    this.loadingTpl = true;
                    fetch(`{{ url('/template') }}/${id}/render`, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.$refs.perihal.value = data.perihal;
                        this.$refs.isi.value = data.isi;
                    })
                    .finally(() => { this.loadingTpl = false; });
                }
             }">
            <div class="card-header">
                <h2 class="card-title">{{ $isEdit ? 'Formulir Revisi Surat (' . $surat->nomor_surat . ')' : 'Formulir Pengajuan Surat Masuk' }}</h2>
                <a href="{{ $isEdit ? route('surat.show', $surat) : route('surat.index') }}" class="btn-secondary btn-sm">Kembali</a>
            </div>

            <form method="POST"
                  action="{{ $isEdit ? route('surat.update', $surat) : route('surat.store') }}"
                  enctype="multipart/form-data"
                  class="card-body space-y-5">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Template Surat Otomatis (Opsional)</label>
                        <select name="id_template" @change="pilihTemplate($event)" class="form-input">
                            <option value="">— Pilih Template untuk Isi Otomatis —</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id_template }}" @selected(old('id_template', $surat->id_template) == $tpl->id_template)>
                                    {{ $tpl->nama_template }}
                                </option>
                            @endforeach
                        </select>
                        <p class="form-hint" x-show="!loadingTpl">Memilih template akan mengisi perihal & isi surat secara otomatis.</p>
                        <p class="form-hint text-brand-600 font-medium" x-show="loadingTpl" x-cloak>Memuat template surat...</p>
                    </div>

                    @if(! $isEdit)
                        <div>
                            <label class="form-label">Nomor Surat</label>
                            <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}"
                                   placeholder="Kosongkan untuk nomor otomatis (SRT/YYYY/MM/XXXX)" class="form-input">
                            <p class="form-hint">Isi jika surat dari unit/instansi Anda telah memiliki nomor resmi.</p>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="form-label">Perihal Surat <span class="text-rose-600">*</span></label>
                        <input x-ref="perihal" type="text" name="perihal"
                               value="{{ old('perihal', $surat->perihal) }}" required
                               placeholder="Contoh: Permohonan Izin Pelaksanaan Seminar Nasional" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Sifat Surat <span class="text-rose-600">*</span></label>
                        <select name="sifat_surat" required class="form-input">
                            <option value="Biasa" @selected(old('sifat_surat', $surat->sifat_surat) === 'Biasa')>Biasa</option>
                            <option value="Urgent" @selected(old('sifat_surat', $surat->sifat_surat) === 'Urgent')>Urgent (Prioritas)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="form-label">Tanggal Surat <span class="text-rose-600">*</span></label>
                        <input type="date" name="tanggal_surat"
                               value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d') ?? now()->toDateString()) }}"
                               required class="form-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Pengirim & Unit Asal</label>
                        <input type="text" disabled
                               value="{{ auth()->user()->nama }} ({{ auth()->user()->unit?->nama_unit ?? '-' }})"
                               class="form-input bg-slate-100 text-slate-600">
                    </div>
                </div>

                <div>
                    <label class="form-label">Isi / Ringkasan Surat</label>
                    <textarea x-ref="isi" name="isi_surat" rows="10"
                              placeholder="Tuliskan isi surat atau pilih template di atas..."
                              class="form-input font-mono text-xs leading-relaxed">{{ old('isi_surat', $surat->isi_surat) }}</textarea>
                </div>

                <div>
                    <label class="form-label">Unggah Berkas Lampiran (PDF / Word / Excel / Gambar / ZIP, maks. 10 MB per berkas)</label>
                    <input type="file" name="lampiran[]" multiple
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                           class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2 text-xs text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700">
                    <p class="form-hint">Dapat memilih hingga 5 berkas sekaligus (surat resmi bertanda tangan, proposal, RAB, dll.).</p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ $isEdit ? route('surat.show', $surat) : route('surat.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        {{ $isEdit ? 'Kirim Ulang Revisi Surat' : 'Kirim Surat ke Rektorat' }}
                    </button>
                </div>
            </form>
        </div>

        @if($isEdit && $surat->lampiran->isNotEmpty())
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="card-title">Lampiran Saat Ini</h3>
                </div>
                <div class="card-body divide-y divide-slate-100">
                    @foreach($surat->lampiran as $lamp)
                        <div class="flex items-center justify-between py-2.5 text-xs">
                            <div>
                                <a href="{{ route('lampiran.unduh', $lamp) }}" class="font-semibold text-brand-600 hover:underline">{{ $lamp->nama_file }}</a>
                                <span class="ml-2 text-slate-400">({{ $lamp->ukuran_label }})</span>
                            </div>
                            <form method="POST" action="{{ route('lampiran.destroy', $lamp) }}" onsubmit="return confirm('Hapus lampiran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 font-semibold hover:underline">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
