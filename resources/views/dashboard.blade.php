@extends('layouts.app', [
    'title' => 'Dashboard ' . ($user->role?->nama_role ?? ''),
    'subtitle' => $user->unit?->nama_unit ?? 'Ringkasan aktivitas dan status disposisi surat',
])

@section('content')
    <!-- Banner Alur sesuai Peran -->
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-brand-950 to-brand-900 p-5 text-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-medium text-brand-200 ring-1 ring-white/15">
                    {{ $user->role?->nama_role }} {{ $user->unit ? '• ' . $user->unit->nama_unit : '' }}
                </span>
                <h2 class="mt-2 text-lg font-bold sm:text-xl">Selamat datang, {{ $user->nama }}</h2>
                <p class="mt-0.5 text-xs text-slate-300 max-w-2xl">{{ $user->role?->deskripsi }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($user->isPengirim())
                    <a href="{{ route('surat.create') }}" class="btn bg-white text-slate-900 hover:bg-slate-100">
                        + Buat Surat Baru
                    </a>
                @elseif($user->isAdmin())
                    <a href="{{ route('verifikasi.index') }}" class="btn bg-white text-slate-900 hover:bg-slate-100">
                        Buka Antrian Validasi
                    </a>
                @elseif($user->isRektor())
                    <a href="{{ route('rektor.index') }}" class="btn bg-white text-slate-900 hover:bg-slate-100">
                        Buka Surat Menunggu Disposisi
                    </a>
                @elseif($user->isWakilRektor() || $user->isUnitTeknis())
                    <a href="{{ route('disposisi.index') }}" class="btn bg-white text-slate-900 hover:bg-slate-100">
                        Buka Disposisi Masuk
                    </a>
                @endif
                <a href="{{ route('lacak') }}" class="btn bg-white/10 text-white ring-1 ring-white/20 hover:bg-white/20">
                    Lacak Surat
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        @php
            $colorMap = [
                'sky' => 'bg-sky-50 text-sky-600 ring-sky-500/20',
                'rose' => 'bg-rose-50 text-rose-600 ring-rose-500/20',
                'amber' => 'bg-amber-50 text-amber-600 ring-amber-500/20',
                'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-500/20',
                'indigo' => 'bg-indigo-50 text-indigo-600 ring-indigo-500/20',
                'violet' => 'bg-violet-50 text-violet-600 ring-violet-500/20',
                'orange' => 'bg-orange-50 text-orange-600 ring-orange-500/20',
                'brand' => 'bg-brand-50 text-brand-600 ring-brand-500/20',
            ];
        @endphp
        @foreach($stats as $st)
            <div class="card card-body flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">{{ $st['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $st['value'] }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl ring-1 {{ $colorMap[$st['color']] ?? $colorMap['brand'] }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Left 8 cols: Priority Queue Table -->
        <div class="lg:col-span-8 space-y-6">
            @if(isset($disposisiAktif))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Disposisi Aktif Unit Anda</h3>
                        <a href="{{ route('disposisi.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Lihat Semua →</a>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Surat</th>
                                <th>Dari</th>
                                <th>Instruksi</th>
                                <th>Batas Waktu</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @forelse($disposisiAktif as $d)
                                <tr>
                                    <td class="whitespace-normal max-w-xs">
                                        <div class="flex items-center gap-1.5">
                                            <x-sifat-badge :sifat="$d->surat->sifat_surat" />
                                            <span class="font-mono text-xs text-slate-500">{{ $d->surat->nomor_surat }}</span>
                                        </div>
                                        <p class="mt-0.5 font-semibold text-slate-800">{{ $d->surat->perihal }}</p>
                                    </td>
                                    <td class="text-xs">
                                        <p class="font-medium text-slate-800">{{ $d->pemberi?->nama }}</p>
                                        <p class="text-slate-400">{{ $d->tanggal_disposisi?->format('d/m/Y H:i') }}</p>
                                    </td>
                                    <td class="whitespace-normal max-w-xs text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($d->instruksi, 80) }}</td>
                                    <td class="text-xs">
                                        @if($d->batas_waktu)
                                            <span class="{{ $d->terlambat ? 'font-bold text-rose-600' : 'text-slate-600' }}">
                                                {{ $d->batas_waktu->format('d/m/Y') }}
                                                @if($d->terlambat) (Terlambat) @endif
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td><x-status-badge :status="$d->status" /></td>
                                    <td class="text-right">
                                        <a href="{{ route('surat.show', $d->id_surat) }}" class="btn-primary btn-sm">Tindak Lanjuti</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-xs text-slate-400">Tidak ada disposisi aktif yang menunggu tindak lanjut.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $antrianJudul ?? 'Daftar Surat' }}</h3>
                        <a href="{{ route('surat.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Lihat Semua →</a>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Nomor & Perihal</th>
                                <th>Pengirim</th>
                                <th>Kode</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @forelse($antrian as $s)
                                <tr>
                                    <td class="whitespace-normal max-w-xs">
                                        <div class="flex items-center gap-1.5">
                                            <x-sifat-badge :sifat="$s->sifat_surat" />
                                            <span class="font-mono text-xs text-slate-500">{{ $s->nomor_surat }}</span>
                                        </div>
                                        <p class="mt-0.5 font-semibold text-slate-800">{{ $s->perihal }}</p>
                                    </td>
                                    <td class="text-xs">
                                        <p class="font-medium text-slate-800">{{ $s->pengirim?->nama }}</p>
                                        <p class="text-slate-400">{{ $s->pengirim?->unit?->nama_unit }}</p>
                                    </td>
                                    <td>
                                        @if($s->kode_klasifikasi)
                                            <span class="badge bg-slate-100 text-slate-700 ring-slate-400/20 font-mono">{{ $s->kode_klasifikasi }}</span>
                                        @else
                                            <span class="text-xs text-slate-400">Belum diklasifikasi</span>
                                        @endif
                                    </td>
                                    <td><x-status-badge :status="$s->status_terkini" /></td>
                                    <td class="text-right">
                                        <a href="{{ route('surat.show', $s) }}" class="btn-secondary btn-sm">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-slate-400">Tidak ada surat pada daftar ini.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Tren 6 Bulan & Sebaran Status -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="card card-body">
                    <h3 class="card-title mb-4">Tren Volume Surat (6 Bulan Terakhir)</h3>
                    @php $maxTren = max(1, $tren->max('jumlah')); @endphp
                    <div class="space-y-2.5">
                        @foreach($tren as $item)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-600 font-medium">{{ $item['label'] }}</span>
                                    <span class="font-bold text-slate-800">{{ $item['jumlah'] }} surat</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-brand-600" style="width: {{ round(($item['jumlah'] / $maxTren) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card card-body">
                    <h3 class="card-title mb-4">Distribusi Status Surat</h3>
                    <div class="space-y-2">
                        @foreach(\App\Models\Surat::STATUSES as $st)
                            @php $jml = $perStatus[$st] ?? 0; @endphp
                            @if($jml > 0)
                                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                    <x-status-badge :status="$st" />
                                    <span class="text-sm font-bold text-slate-800">{{ $jml }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 4 cols: Live Activity Timeline -->
        <div class="lg:col-span-4 space-y-6">
            @isset($perSumber)
                <div class="card card-body">
                    <h3 class="card-title mb-3">Klasifikasi Sumber Surat</h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl bg-purple-50 p-3">
                            <p class="text-[11px] font-bold text-purple-700">Kode A (Yayasan)</p>
                            <p class="mt-1 text-xl font-bold text-purple-900">{{ $perSumber['A'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-xl bg-sky-50 p-3">
                            <p class="text-[11px] font-bold text-sky-700">Kode B (Internal)</p>
                            <p class="mt-1 text-xl font-bold text-sky-900">{{ $perSumber['B'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-3">
                            <p class="text-[11px] font-bold text-amber-700">Kode C (Eksternal)</p>
                            <p class="mt-1 text-xl font-bold text-amber-900">{{ $perSumber['C'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            @endisset

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Aktivitas Terbaru</h3>
                </div>
                <div class="card-body">
                    <ol class="relative border-l border-slate-200 ml-2 space-y-4">
                        @forelse($aktivitas as $akt)
                            <li class="ml-4">
                                <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-brand-600"></span>
                                <div class="flex items-center justify-between gap-2">
                                    <x-status-badge :status="$akt->status" />
                                    <span class="text-[11px] text-slate-400">{{ $akt->waktu?->diffForHumans() }}</span>
                                </div>
                                @if($akt->surat)
                                    <a href="{{ route('surat.show', $akt->id_surat) }}" class="mt-1 block text-xs font-semibold text-slate-800 hover:text-brand-600">
                                        {{ $akt->surat->perihal }}
                                    </a>
                                @endif
                                <p class="mt-0.5 text-xs text-slate-600">{{ $akt->keterangan }}</p>
                            </li>
                        @empty
                            <p class="text-xs text-slate-400">Belum ada riwayat aktivitas.</p>
                        @endforelse
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
