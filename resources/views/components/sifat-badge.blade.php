@props(['sifat'])
@if($sifat === 'Urgent')
    <span {{ $attributes->merge(['class' => 'badge bg-rose-100 text-rose-700 ring-rose-600/30 font-bold']) }}>
        <svg class="h-3.5 w-3.5 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
        </svg>
        URGENT
    </span>
@else
    <span {{ $attributes->merge(['class' => 'badge bg-slate-100 text-slate-600 ring-slate-400/20']) }}>
        Biasa
    </span>
@endif
