@props(['surat'])
@php
    $tahapan = [
        ['label' => 'Terkirim', 'sub' => 'Pengirim'],
        ['label' => 'Divalidasi', 'sub' => 'Rektorat'],
        ['label' => 'Dibaca', 'sub' => 'Rektor'],
        ['label' => 'Didisposisi', 'sub' => 'Rektor/WR'],
        ['label' => 'Diproses', 'sub' => 'Unit Teknis'],
        ['label' => 'Selesai', 'sub' => 'Arsip'],
    ];
    $aktif = $surat->tahap_index;
    $revisi = $surat->status_terkini === \App\Models\Surat::PERLU_REVISI;
    $pending = $surat->status_terkini === \App\Models\Surat::PENDING;
    $selesai = in_array($surat->status_terkini, [\App\Models\Surat::SELESAI, \App\Models\Surat::DIARSIPKAN], true);
@endphp

<div class="card px-3.5 py-2.5">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-1.5 flex-1">
            @foreach($tahapan as $idx => $tahap)
                @php
                    $done = $idx < $aktif || $selesai;
                    $current = $idx === $aktif && ! $selesai;
                @endphp
                <div class="flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[11px]
                    {{ $done ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : ($current ? ($revisi ? 'border-rose-300 bg-rose-50 text-rose-800 font-bold' : ($pending ? 'border-orange-300 bg-orange-50 text-orange-800 font-bold' : 'border-brand-300 bg-brand-50 text-brand-800 font-bold')) : 'border-slate-200 bg-slate-50/60 text-slate-400') }}">
                    <span class="flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-bold
                        {{ $done ? 'bg-emerald-600 text-white' : ($current ? ($revisi ? 'bg-rose-600 text-white' : ($pending ? 'bg-orange-500 text-white' : 'bg-brand-600 text-white')) : 'bg-slate-200 text-slate-500') }}">
                        {{ $done ? '✓' : $idx + 1 }}
                    </span>
                    <span>{{ $tahap['label'] }}</span>
                </div>
                @if(! $loop->last)
                    <span class="text-slate-300 text-xs">→</span>
                @endif
            @endforeach
        </div>

        <div class="flex items-center gap-1.5">
            <x-sifat-badge :sifat="$surat->sifat_surat" />
            <x-status-badge :status="$surat->status_terkini" />
        </div>
    </div>
</div>
