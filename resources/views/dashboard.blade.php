@php
    $theme = $user->theme();
@endphp

@extends('layouts.app', [
    'title' => 'Dashboard ' . ($user->role?->nama_role ?? ''),
    'subtitle' => $user->unit?->nama_unit ?? 'Ringkasan aktivitas dan status disposisi surat',
])

@section('content')
    <!-- Responsive Role-Themed Banner -->
    <div class="mb-3.5 rounded-xl {{ $theme['banner_bg'] }} px-4 py-3 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold ring-1 {{ $theme['level_badge'] }}">
                    {{ $theme['level'] }}
                </span>
                <span class="text-xs text-white/85 font-medium">{{ $user->unit?->nama_unit }}</span>
            </div>
            <p class="text-sm sm:text-base font-bold leading-snug break-words">Selamat datang, {{ $user->nama }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('surat.create') }}" class="btn-sm rounded-lg bg-[#ed6623] px-3 py-1.5 text-xs font-bold text-white shadow-sm ring-1 ring-white/30 hover:bg-[#d95316]">
                + Buat Surat
            </a>
            @if($user->isAdmin())
                <a href="{{ route('verifikasi.index') }}" class="btn-sm rounded-lg bg-white/15 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/25">
                    Antrian Validasi
                </a>
            @elseif($user->isRektor())
                <a href="{{ route('rektor.index') }}" class="btn-sm rounded-lg bg-white/15 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/25">
                    Disposisi Rektor
                </a>
            @elseif($user->isWakilRektor() || $user->isUnitTeknis())
                <a href="{{ route('disposisi.index') }}" class="btn-sm rounded-lg bg-white/15 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/25">
                    Disposisi Masuk
                </a>
            @endif
        </div>
    </div>

    <!-- Responsive Stats Cards with Role Accent Border -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3.5">
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
            <div class="card {{ $theme['stat_border'] }} px-4 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-500 leading-snug">{{ $st['label'] }}</p>
                    <p class="mt-0.5 text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">{{ $st['value'] }}</p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 {{ $colorMap[$st['color']] ?? $colorMap['brand'] }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 gap-3.5 xl:grid-cols-12">
        <!-- Left 8 cols: Priority Queue Table -->
        <div class="xl:col-span-8">
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
                                <th class="text-right">Aksi</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @forelse($disposisiAktif as $d)
                                <tr>
                                    <td class="min-w-[200px] max-w-xs">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <x-sifat-badge :sifat="$d->surat->sifat_surat" />
                                            <span class="font-mono text-[11px] text-slate-500">{{ $d->surat->nomor_surat }}</span>
                                        </div>
                                        <a href="{{ route('surat.show', $d->id_surat) }}" class="mt-0.5 block font-semibold text-slate-800 hover:text-brand-600 leading-snug">
                                            {{ $d->surat->perihal }}
                                        </a>
                                    </td>
                                    <td class="min-w-[140px]">
                                        <p class="font-medium text-slate-800 leading-snug">{{ $d->pemberi?->nama }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $d->tanggal_disposisi?->format('d/m/Y') }}</p>
                                    </td>
                                    <td class="min-w-[160px] max-w-xs text-slate-600 leading-snug">{{ \Illuminate\Support\Str::limit($d->instruksi, 70) }}</td>
                                    <td class="whitespace-nowrap">
                                        @if($d->batas_waktu)
                                            <span class="{{ $d->terlambat ? 'font-bold text-rose-600' : 'text-slate-600' }}">
                                                {{ $d->batas_waktu->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td><x-status-badge :status="$d->status" /></td>
                                    <td class="text-right whitespace-nowrap">
                                        <a href="{{ route('surat.show', $d->id_surat) }}" class="btn-primary btn-sm">Proses</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada disposisi aktif.</td>
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
                                <th class="text-right">Aksi</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @forelse($antrian as $s)
                                <tr>
                                    <td class="min-w-[200px] max-w-xs">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <x-sifat-badge :sifat="$s->sifat_surat" />
                                            <span class="font-mono text-[11px] text-slate-500">{{ $s->nomor_surat }}</span>
                                        </div>
                                        <a href="{{ route('surat.show', $s) }}" class="mt-0.5 block font-semibold text-slate-800 hover:text-brand-600 leading-snug">
                                            {{ $s->perihal }}
                                        </a>
                                    </td>
                                    <td class="min-w-[150px]">
                                        <p class="font-medium text-slate-800 leading-snug">{{ $s->pengirim?->nama }}</p>
                                        <p class="text-[11px] text-slate-400 leading-snug">{{ $s->pengirim?->unit?->nama_unit }}</p>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        @if($s->kode_klasifikasi)
                                            <span class="badge bg-slate-100 text-slate-700 ring-slate-400/20 font-mono">{{ $s->kode_klasifikasi }}</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td><x-status-badge :status="$s->status_terkini" /></td>
                                    <td class="text-right">
                                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                            <a href="{{ route('surat.show', $s) }}" class="btn-secondary btn-sm">Buka</a>
                                            @if($user->isAdmin() || $user->isRektor() || $s->id_pengirim === $user->id_pengguna)
                                                <form method="POST" action="{{ route('surat.destroy', $s) }}" class="inline"
                                                      onsubmit="return confirm('Hapus surat {{ $s->nomor_surat }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger btn-sm">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Tidak ada surat pada daftar ini.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right 4 cols: Compact Tabbed Panel (Aktivitas / Statistik / Tren) -->
        <div class="xl:col-span-4" x-data="{ rtab: 'aktivitas' }">
            <div class="card">
                <div class="card-header">
                    <div class="flex rounded-lg bg-slate-100 p-0.5 text-[11px] font-semibold w-full">
                        <button type="button" @click="rtab = 'aktivitas'"
                                :class="rtab === 'aktivitas' ? 'bg-white text-brand-700 shadow-2xs' : 'text-slate-600'"
                                class="flex-1 rounded-md py-1">Aktivitas</button>
                        <button type="button" @click="rtab = 'status'"
                                :class="rtab === 'status' ? 'bg-white text-brand-700 shadow-2xs' : 'text-slate-600'"
                                class="flex-1 rounded-md py-1">Status</button>
                        <button type="button" @click="rtab = 'tren'"
                                :class="rtab === 'tren' ? 'bg-white text-brand-700 shadow-2xs' : 'text-slate-600'"
                                class="flex-1 rounded-md py-1">Tren 6 Bln</button>
                    </div>
                </div>

                <!-- Tab 1: Aktivitas -->
                <div x-show="rtab === 'aktivitas'" class="card-body max-h-72 overflow-y-auto">
                    <ol class="relative border-l border-slate-200 ml-2 space-y-2.5">
                        @forelse($aktivitas as $akt)
                            <li class="ml-3.5">
                                <span class="absolute -left-1.5 mt-1 h-2.5 w-2.5 rounded-full border border-white bg-brand-600"></span>
                                <div class="flex items-center justify-between gap-1">
                                    <x-status-badge :status="$akt->status" />
                                    <span class="text-[10px] text-slate-400">{{ $akt->waktu?->diffForHumans() }}</span>
                                </div>
                                @if($akt->surat)
                                    <a href="{{ route('surat.show', $akt->id_surat) }}" class="mt-0.5 block font-semibold text-slate-800 hover:text-brand-600 truncate">
                                        {{ $akt->surat->perihal }}
                                    </a>
                                @endif
                                <p class="text-[11px] text-slate-500 line-clamp-1">{{ $akt->keterangan }}</p>
                            </li>
                        @empty
                            <p class="text-slate-400">Belum ada riwayat aktivitas.</p>
                        @endforelse
                    </ol>
                </div>

                <!-- Tab 2: Status & Sumber -->
                <div x-show="rtab === 'status'" x-cloak class="card-body space-y-2 max-h-72 overflow-y-auto">
                    @isset($perSumber)
                        <div class="grid grid-cols-3 gap-1.5 text-center mb-2">
                            <div class="rounded-lg bg-purple-50 p-2">
                                <p class="text-[10px] font-bold text-purple-700">Kode A</p>
                                <p class="text-base font-bold text-purple-900">{{ $perSumber['A'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg bg-sky-50 p-2">
                                <p class="text-[10px] font-bold text-sky-700">Kode B</p>
                                <p class="text-base font-bold text-sky-900">{{ $perSumber['B'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg bg-amber-50 p-2">
                                <p class="text-[10px] font-bold text-amber-700">Kode C</p>
                                <p class="text-base font-bold text-amber-900">{{ $perSumber['C'] ?? 0 }}</p>
                            </div>
                        </div>
                    @endisset
                    @foreach(\App\Models\Surat::STATUSES as $st)
                        @php $jml = $perStatus[$st] ?? 0; @endphp
                        @if($jml > 0)
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 px-2.5 py-1.5">
                                <x-status-badge :status="$st" />
                                <span class="font-bold text-slate-800">{{ $jml }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Tab 3: Tren 6 Bulan -->
                <div x-show="rtab === 'tren'" x-cloak class="card-body space-y-2 max-h-72 overflow-y-auto">
                    @php $maxTren = max(1, $tren->max('jumlah')); @endphp
                    @foreach($tren as $item)
                        <div>
                            <div class="flex justify-between text-[11px] mb-0.5">
                                <span class="text-slate-600 font-medium">{{ $item['label'] }}</span>
                                <span class="font-bold text-slate-800">{{ $item['jumlah'] }} surat</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full bg-brand-600" style="width: {{ round(($item['jumlah'] / $maxTren) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
