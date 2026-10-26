@extends('layouts.app', [
    'title' => 'Notifikasi Sistem',
    'subtitle' => 'Pemberitahuan otomatis setiap perubahan status, revisi, dan disposisi surat',
])

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex rounded-xl bg-white p-1 shadow-xs ring-1 ring-slate-200 text-xs font-semibold">
            <a href="{{ route('notifikasi.index') }}"
               class="rounded-lg px-3.5 py-2 {{ ! request('filter') ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Semua Notifikasi
            </a>
            <a href="{{ route('notifikasi.index', ['filter' => 'belum']) }}"
               class="rounded-lg px-3.5 py-2 {{ request('filter') === 'belum' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Belum Dibaca
            </a>
        </div>

        <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
            @csrf
            <button type="submit" class="btn-secondary btn-sm">Tandai Semua Sudah Dibaca</button>
        </form>
    </div>

    <div class="card divide-y divide-slate-100">
        @forelse($notifikasi as $n)
            <div class="flex items-center justify-between gap-4 px-5 py-4 {{ ! $n->sudah_dibaca ? 'bg-brand-50/40' : '' }}">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        @if(! $n->sudah_dibaca)
                            <span class="h-2 w-2 rounded-full bg-brand-600"></span>
                        @endif
                        <p class="text-sm {{ ! $n->sudah_dibaca ? 'font-semibold text-slate-900' : 'text-slate-700' }}">{{ $n->pesan }}</p>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">{{ $n->waktu?->translatedFormat('d F Y H:i') }} ({{ $n->waktu?->diffForHumans() }})</p>
                </div>
                <a href="{{ route('notifikasi.buka', $n) }}" class="btn-secondary btn-sm shrink-0">Buka Surat</a>
            </div>
        @empty
            <div class="py-12 text-center text-sm text-slate-400">Tidak ada notifikasi.</div>
        @endforelse
    </div>

    @if($notifikasi->hasPages())
        <div class="mt-4">{{ $notifikasi->links() }}</div>
    @endif
@endsection
