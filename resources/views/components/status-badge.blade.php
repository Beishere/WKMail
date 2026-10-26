@props(['status'])
@php
    $cls = \App\Models\Surat::statusColor($status);
@endphp
<span {{ $attributes->merge(['class' => "badge $cls"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ $status }}
</span>
