@extends('layouts.app', [
    'title' => 'Pelacakan Status Surat (Online Tracking)',
    'subtitle' => 'Lacak posisi surat dari pengiriman, validasi Rektorat, disposisi Rektor & WR, hingga penyelesaian Unit Teknis',
])

@section('content')
    <div class="card card-body mb-6">
        <form method="GET" action="{{ route('lacak') }}" class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="q" value="{{ request('q') }}" required
                   placeholder="Masukkan Nomor Surat (mis. SRT/2026/...) atau kata kunci Perihal..."
                   class="form-input flex-1">
            <button type="submit" class="btn-primary">Lacak Status Surat</button>
        </form>
    </div>

    @if(request()->filled('q'))
        @if($hasil->isEmpty())
            <div class="card card-body py-12 text-center text-sm text-slate-500">
                Tidak ditemukan surat dengan kata kunci <span class="font-semibold text-slate-800">"{{ request('q') }}"</span> yang dapat diakses oleh akun Anda.
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Daftar Hasil Pencarian -->
                <div class="lg:col-span-4 space-y-2">
                    <h3 class="px-1 text-xs font-bold uppercase tracking-wider text-slate-400">Hasil Pencarian ({{ $hasil->count() }})</h3>
                    @foreach($hasil as $item)
                        <a href="{{ route('lacak', ['q' => request('q'), 'id' => $item->id_surat]) }}"
                           class="block rounded-xl border p-3.5 transition {{ $terpilih && $terpilih->id_surat === $item->id_surat ? 'border-brand-500 bg-brand-50/50 ring-1 ring-brand-500' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-xs font-semibold text-slate-600">{{ $item->nomor_surat }}</span>
                                <x-status-badge :status="$item->status_terkini" />
                            </div>
                            <p class="mt-1 text-xs font-bold text-slate-900">{{ $item->perihal }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">{{ $item->pengirim?->nama }} • {{ $item->tanggal_surat?->format('d/m/Y') }}</p>
                        </a>
                    @endforeach
                </div>

                <!-- Detail Pelacakan Surat Terpilih -->
                @if($terpilih)
                    <div class="lg:col-span-8 space-y-6">
                        <x-progress-tracker :surat="$terpilih" />

                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <span class="font-mono text-xs text-slate-500">{{ $terpilih->nomor_surat }}</span>
                                    <h3 class="text-base font-bold text-slate-900">{{ $terpilih->perihal }}</h3>
                                </div>
                                <a href="{{ route('surat.show', $terpilih) }}" class="btn-primary btn-sm">Buka Halaman Surat →</a>
                            </div>
                            <div class="card-body">
                                <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Kronologi & Riwayat Status</h4>
                                <ol class="relative border-l-2 border-brand-200 ml-2 space-y-4">
                                    @foreach($terpilih->riwayat as $rw)
                                        <li class="ml-4">
                                            <span class="absolute -left-2 mt-1 h-3.5 w-3.5 rounded-full border-2 border-white bg-brand-600"></span>
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <x-status-badge :status="$rw->status" />
                                                <span class="text-xs font-mono text-slate-400">{{ $rw->waktu?->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-800">{{ $rw->keterangan }}</p>
                                            @if($rw->pengguna)
                                                <p class="text-[11px] text-slate-500">Aktor: {{ $rw->pengguna->nama }} ({{ $rw->pengguna->role?->nama_role }})</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif
@endsection
