@php $isEdit = $unit->exists; @endphp
@extends('layouts.app', [
    'title' => $isEdit ? 'Edit Unit' : 'Tambah Unit Baru',
])

@section('content')
    <div class="mx-auto max-w-xl card">
        <div class="card-header">
            <h2 class="card-title">{{ $isEdit ? 'Form Edit Unit' : 'Form Tambah Unit' }}</h2>
            <a href="{{ route('master.unit.index') }}" class="btn-secondary btn-sm">Kembali</a>
        </div>

        <form method="POST" action="{{ $isEdit ? route('master.unit.update', $unit) : route('master.unit.store') }}" class="card-body space-y-4">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div>
                <label class="form-label">Nama Unit <span class="text-rose-600">*</span></label>
                <input type="text" name="nama_unit" value="{{ old('nama_unit', $unit->nama_unit) }}" required class="form-input">
            </div>

            <div>
                <label class="form-label">Jenis Unit <span class="text-rose-600">*</span></label>
                <select name="jenis_unit" required class="form-input">
                    @foreach(\App\Models\Unit::JENIS as $k => $lbl)
                        <option value="{{ $k }}" @selected(old('jenis_unit', $unit->jenis_unit) === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Unit Induk (Hierarki)</label>
                <select name="id_induk" class="form-input">
                    <option value="">— Tidak Ada Induk —</option>
                    @foreach($indukOptions as $opt)
                        <option value="{{ $opt->id_unit }}" @selected(old('id_induk', $unit->id_induk) == $opt->id_unit)>
                            {{ $opt->nama_unit }}
                        </option>
                    @endforeach
                </select>
                <p class="form-hint">Contoh: BAA berinduk ke Wakil Rektor I; HMJ TI berinduk ke Fakultas Teknik.</p>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('master.unit.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
