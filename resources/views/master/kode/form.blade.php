@php
    $isKategori = $jenis === 'kategori';
    $isEdit = $item->exists;
    $kode = $isKategori ? $item->kode_sumber : $item->kode_bidang;
    $nama = $isKategori ? $item->nama_kategori : $item->nama_bidang;
    $action = $isKategori
        ? ($isEdit ? route('master.kategori-sumber.update', $item) : route('master.kategori-sumber.store'))
        : ($isEdit ? route('master.kode-bidang.update', $item) : route('master.kode-bidang.store'));
    $back = $isKategori ? route('master.kategori-sumber.index') : route('master.kode-bidang.index');
@endphp

@extends('layouts.app', [
    'title' => ($isEdit ? 'Edit ' : 'Tambah ') . ($isKategori ? 'Kategori Sumber Surat' : 'Kode Bidang'),
])

@section('content')
    <div class="mx-auto max-w-xl card">
        <div class="card-header">
            <h2 class="card-title">{{ $isEdit ? 'Edit Kode ' . $kode : 'Tambah Kode Baru' }}</h2>
            <a href="{{ $back }}" class="btn-secondary btn-sm">Kembali</a>
        </div>

        <form method="POST" action="{{ $action }}" class="card-body space-y-4">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="form-label">Kode (1 Huruf) <span class="text-rose-600">*</span></label>
                    <input type="text" name="kode" maxlength="1" value="{{ old('kode', $kode) }}"
                           {{ $isEdit ? 'disabled' : 'required' }}
                           class="form-input uppercase font-mono {{ $isEdit ? 'bg-slate-100' : '' }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Nama {{ $isKategori ? 'Kategori Sumber' : 'Bidang' }} <span class="text-rose-600">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $nama) }}" required class="form-input">
                </div>
            </div>

            @if(! $isKategori)
                <div>
                    <label class="form-label">Wakil Rektor Penanggung Jawab (Saran Otomatis)</label>
                    <select name="id_unit_wr" class="form-input">
                        <option value="">— Pilih Wakil Rektor —</option>
                        @foreach($unitWr as $uwr)
                            <option value="{{ $uwr->id_unit }}" @selected(old('id_unit_wr', $item->id_unit_wr) == $uwr->id_unit)>
                                {{ $uwr->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" rows="3" class="form-input">{{ old('keterangan', $item->keterangan) }}</textarea>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ $back }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
