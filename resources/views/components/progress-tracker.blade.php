@props(['surat'])
@php
    $tahapan = [
        ['label' => 'Terkirim', 'sub' => 'Pengirim'],
        ['label' => 'Divalidasi', 'sub' => 'Admin Rektorat'],
        ['label' => 'Dibaca', 'sub' => 'Rektor'],
        ['label' => 'Didisposisi', 'sub' => 'Rektor / WR'],
        ['label' => 'Diproses', 'sub' => 'Unit Teknis'],
        ['label' => 'Selesai', 'sub' => 'Arsip & Bukti'],
    ];
    $aktif = $surat->tahap_index;
    $revisi = $surat->status_terkini === \App\Models\Surat::PERLU_REVISI;
    $arsip = $surat->status_terkini === \App\Models\Surat::DIARSIPKAN;
    $pending = $surat->status_terkini === \App\Models\Surat::PENDING;
    $selesai = in_array($surat->status_terkini, [\App\Models\Surat::SELESAI, \App\Models\Surat::DIARSIPKAN], true);
@endphp

<div class="card card-body">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Pelacakan Alur Disposisi Surat</h3>
            <p class="text-xs text-slate-500">Status saat ini: <span class="font-semibold text-slate-800">{{ $surat->status_terkini }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <x-sifat-badge :sifat="$surat->sifat_surat" />
            <x-status-badge :status="$surat->status_terkini" />
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-6">
        @foreach($tahapan as $idx => $tahap)
            @php
                $done = $idx < $aktif || $selesai;
                $current = $idx === $aktif && ! $selesai;
            @endphp
            <div class="relative flex flex-col rounded-xl border p-3 transition
                {{ $done ? 'border-emerald-200 bg-emerald-50/60' : ($current ? ($revisi ? 'border-rose-300 bg-rose-50' : ($pending ? 'border-orange-300 bg-orange-50' : 'border-brand-300 bg-brand-50')) : 'border-slate-200 bg-slate-50/60 opacity-60') }}">
                <div class="mb-1.5 flex items-center justify-between">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold
                        {{ $done ? 'bg-emerald-600 text-white' : ($current ? ($revisi ? 'bg-rose-600 text-white' : ($pending ? 'bg-orange-500 text-white' : 'bg-brand-600 text-white')) : 'bg-slate-200 text-slate-600') }}">
                        @if($done)
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </span>
                    @if($current)
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $revisi ? 'bg-rose-100 text-rose-700' : ($pending ? 'bg-orange-100 text-orange-700' : 'bg-brand-100 text-brand-700') }}">
                            {{ $revisi ? 'Revisi' : ($pending ? 'Pending' : 'Saat Ini') }}
                        </span>
                    @elseif($arsip && $idx === 5)
                        <span class="rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-700">Arsip</span>
                    @endif
                </div>
                <p class="text-xs font-bold text-slate-800">{{ $tahap['label'] }}</p>
                <p class="text-[11px] text-slate-500">{{ $tahap['sub'] }}</p>
            </div>
        @endforeach
    </div>
</div>
