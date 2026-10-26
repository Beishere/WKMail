@extends('layouts.app', [
    'title' => 'Disposisi Masuk — ' . (auth()->user()->unit?->nama_unit ?? ''),
    'subtitle' => auth()->user()->isWakilRektor()
        ? 'Tinjau disposisi Rektor dan teruskan ke Unit Teknis (BAA / BAU / HMJ)'
        : 'Tindak lanjuti disposisi, perbarui status progres, dan unggah bukti penyelesaian',
])

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex rounded-xl bg-white p-1 shadow-xs ring-1 ring-slate-200 text-xs font-semibold">
            <a href="{{ route('disposisi.index', ['tab' => 'aktif']) }}"
               class="rounded-lg px-3.5 py-2 {{ $tab === 'aktif' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Perlu Tindak Lanjut
                @if($jumlahBaru > 0)
                    <span class="ml-1 rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] text-white">{{ $jumlahBaru }} baru</span>
                @endif
            </a>
            @if(auth()->user()->isWakilRektor())
                <a href="{{ route('disposisi.index', ['tab' => 'diteruskan']) }}"
                   class="rounded-lg px-3.5 py-2 {{ $tab === 'diteruskan' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    Telah Diteruskan ke Unit Teknis
                </a>
            @endif
            <a href="{{ route('disposisi.index', ['tab' => 'selesai']) }}"
               class="rounded-lg px-3.5 py-2 {{ $tab === 'selesai' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Selesai
            </a>
        </div>

        <form method="GET" action="{{ route('disposisi.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari surat..." class="form-input py-1.5 text-xs">
            <button type="submit" class="btn-secondary btn-sm">Cari</button>
        </form>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Surat</th>
                    <th>Pemberi Disposisi</th>
                    <th>Instruksi</th>
                    <th>Batas Waktu</th>
                    <th>Status Disposisi</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($disposisi as $d)
                    <tr>
                        <td class="whitespace-normal max-w-xs">
                            <div class="flex items-center gap-1.5">
                                <x-sifat-badge :sifat="$d->surat->sifat_surat" />
                                <span class="font-mono text-xs text-slate-500">{{ $d->surat->nomor_surat }}</span>
                            </div>
                            <a href="{{ route('surat.show', $d->id_surat) }}" class="mt-0.5 block font-semibold text-slate-800 hover:text-brand-600">
                                {{ $d->surat->perihal }}
                            </a>
                            <p class="text-[11px] text-slate-400">Pengirim: {{ $d->surat->pengirim?->nama }}</p>
                        </td>
                        <td class="text-xs">
                            <p class="font-medium text-slate-800">{{ $d->pemberi?->nama }}</p>
                            <p class="text-slate-400">{{ $d->tanggal_disposisi?->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="whitespace-normal max-w-xs text-xs text-slate-700">
                            {{ $d->instruksi }}
                            @if($d->catatan)
                                <p class="mt-0.5 text-slate-400">Catatan: {{ $d->catatan }}</p>
                            @endif
                        </td>
                        <td class="text-xs">
                            @if($d->batas_waktu)
                                <span class="{{ $d->terlambat ? 'font-bold text-rose-600' : 'text-slate-600' }}">
                                    {{ $d->batas_waktu->format('d/m/Y') }}
                                    @if($d->terlambat) <br><span class="text-[10px] uppercase">Terlambat</span> @endif
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td><x-status-badge :status="$d->status" /></td>
                        <td class="text-right">
                            <a href="{{ route('surat.show', $d->id_surat) }}" class="{{ $tab === 'aktif' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                                {{ $tab === 'aktif' ? 'Proses' : 'Detail' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-sm text-slate-400">Tidak ada disposisi pada tab ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($disposisi->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $disposisi->links() }}</div>
        @endif
    </div>
@endsection
