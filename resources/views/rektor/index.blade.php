@extends('layouts.app', [
    'title' => 'Surat Masuk Pimpinan (Rektor)',
    'subtitle' => 'Pemberian disposisi surat tervalidasi kepada Wakil Rektor I, II, atau III',
])

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-200 text-xs font-semibold gap-1">
            <a href="{{ route('rektor.index', ['tab' => 'menunggu']) }}"
               class="rounded-lg px-3 py-1.5 {{ $tab === 'menunggu' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Menunggu Disposisi ({{ $jumlahMenunggu }})
            </a>
            <a href="{{ route('rektor.index', ['tab' => 'didisposisi']) }}"
               class="rounded-lg px-3 py-1.5 {{ $tab === 'didisposisi' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Sedang Ditindaklanjuti
            </a>
            <a href="{{ route('rektor.index', ['tab' => 'selesai']) }}"
               class="rounded-lg px-3 py-1.5 {{ $tab === 'selesai' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Selesai / Diarsipkan
            </a>
        </div>

        <form method="GET" action="{{ route('rektor.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari surat..." class="form-input flex-1 sm:w-56">
            <button type="submit" class="btn-secondary btn-sm">Cari</button>
        </form>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Nomor & Perihal</th>
                    <th>Pengirim</th>
                    <th>Klasifikasi & Saran Tujuan</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($surat as $s)
                    <tr>
                        <td class="whitespace-normal max-w-md">
                            <div class="flex items-center gap-2">
                                <x-sifat-badge :sifat="$s->sifat_surat" />
                                <span class="font-mono text-xs text-slate-500">{{ $s->nomor_surat }}</span>
                            </div>
                            <a href="{{ route('surat.show', $s) }}" class="mt-0.5 block font-semibold text-slate-800 hover:text-brand-600">{{ $s->perihal }}</a>
                        </td>
                        <td class="text-xs">
                            <p class="font-medium text-slate-800">{{ $s->pengirim?->nama }}</p>
                            <p class="text-slate-400">{{ $s->pengirim?->unit?->nama_unit }}</p>
                        </td>
                        <td class="text-xs">
                            <span class="badge bg-slate-100 text-slate-700 ring-slate-400/20 font-mono">{{ $s->kode_klasifikasi }}</span>
                            <span class="ml-1 text-slate-600">{{ $s->bidang?->nama_bidang }}</span>
                            @if($s->unitSaran)
                                <p class="mt-0.5 text-[11px] font-medium text-brand-600">Saran: {{ $s->unitSaran->nama_unit }}</p>
                            @endif
                        </td>
                        <td><x-status-badge :status="$s->status_terkini" /></td>
                        <td class="text-right space-x-1.5">
                            <a href="{{ route('surat.show', $s) }}" class="{{ $tab === 'menunggu' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                                {{ $tab === 'menunggu' ? 'Baca & Disposisi' : 'Detail' }}
                            </a>
                            <form method="POST" action="{{ route('surat.destroy', $s) }}" class="inline"
                                  onsubmit="return confirm('Hapus surat {{ $s->nomor_surat }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-sm text-slate-400">Tidak ada surat pada tab ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($surat->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $surat->links() }}</div>
        @endif
    </div>
@endsection
