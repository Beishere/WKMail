@extends('layouts.app', [
    'title' => auth()->user()->isPengirim() ? 'Surat Saya' : 'Daftar & Arsip Surat',
    'subtitle' => 'Pencarian, pemfilteran klasifikasi kode, dan pelacakan status surat',
])

@section('content')
    <!-- Filter Bar -->
    <div class="card card-body mb-6">
        <form method="GET" action="{{ route('surat.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="form-label">Cari Nomor / Perihal / Pengirim</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik kata kunci..." class="form-input">
            </div>

            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Surat::STATUSES as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Sifat Surat</label>
                <select name="sifat" class="form-input">
                    <option value="">Semua Sifat</option>
                    <option value="Biasa" @selected(request('sifat') === 'Biasa')>Biasa</option>
                    <option value="Urgent" @selected(request('sifat') === 'Urgent')>Urgent</option>
                </select>
            </div>

            <div>
                <label class="form-label">Kode Sumber</label>
                <select name="kode_sumber" class="form-input">
                    <option value="">Semua Sumber</option>
                    @foreach($kategori as $k)
                        <option value="{{ $k->kode_sumber }}" @selected(request('kode_sumber') === $k->kode_sumber)>
                            Kode {{ $k->kode_sumber }} — {{ $k->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Kode Bidang</label>
                <select name="kode_bidang" class="form-input">
                    <option value="">Semua Bidang</option>
                    @foreach($bidang as $b)
                        <option value="{{ $b->kode_bidang }}" @selected(request('kode_bidang') === $b->kode_bidang)>
                            {{ $b->kode_bidang }} — {{ $b->nama_bidang }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-6 flex flex-wrap items-center justify-between gap-2 pt-1">
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span>Periode:</span>
                    <input type="date" name="dari" value="{{ request('dari') }}" class="form-input py-1 text-xs w-auto">
                    <span>s/d</span>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-input py-1 text-xs w-auto">
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('surat.index') }}" class="btn-secondary btn-sm">Reset</a>
                    <button type="submit" class="btn-primary btn-sm">Terapkan Filter</button>
                    @if(auth()->user()->isPengirim())
                        <a href="{{ route('surat.create') }}" class="btn-success btn-sm">+ Buat Surat Baru</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Nomor & Tanggal</th>
                    <th>Perihal</th>
                    <th>Pengirim</th>
                    <th>Klasifikasi</th>
                    <th>Status Terkini</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($surat as $s)
                    <tr>
                        <td>
                            <p class="font-mono text-xs font-semibold text-slate-800">{{ $s->nomor_surat }}</p>
                            <p class="text-xs text-slate-400">{{ $s->tanggal_surat?->format('d M Y') }}</p>
                        </td>
                        <td class="whitespace-normal max-w-md">
                            <div class="flex items-center gap-2">
                                <x-sifat-badge :sifat="$s->sifat_surat" />
                                <a href="{{ route('surat.show', $s) }}" class="font-semibold text-slate-800 hover:text-brand-600">
                                    {{ $s->perihal }}
                                </a>
                            </div>
                        </td>
                        <td class="text-xs">
                            <p class="font-medium text-slate-800">{{ $s->pengirim?->nama }}</p>
                            <p class="text-slate-400">{{ $s->pengirim?->unit?->nama_unit }}</p>
                        </td>
                        <td>
                            @if($s->kode_klasifikasi)
                                <span class="badge bg-slate-100 text-slate-700 ring-slate-400/20 font-mono">
                                    {{ $s->kode_klasifikasi }}
                                </span>
                                <p class="mt-0.5 text-[11px] text-slate-400">{{ $s->bidang?->nama_bidang }}</p>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td><x-status-badge :status="$s->status_terkini" /></td>
                        <td class="text-right">
                            <a href="{{ route('surat.show', $s) }}" class="btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-sm text-slate-400">Tidak ditemukan data surat yang sesuai kriteria.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($surat->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">
                {{ $surat->links() }}
            </div>
        @endif
    </div>
@endsection
