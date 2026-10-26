@extends('layouts.app', [
    'title' => 'Verifikasi & Klasifikasi Surat (Rektorat)',
    'subtitle' => 'Filter utama: validasi kelengkapan surat, klasifikasi kode sumber (A/B/C) & bidang, serta saran tujuan disposisi',
])

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex rounded-xl bg-white p-1 shadow-xs ring-1 ring-slate-200 text-xs font-semibold">
            <a href="{{ route('verifikasi.index', ['tab' => 'antrian']) }}"
               class="rounded-lg px-3.5 py-2 {{ $tab === 'antrian' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Antrian Validasi ({{ $jumlah['antrian'] }})
            </a>
            <a href="{{ route('verifikasi.index', ['tab' => 'revisi']) }}"
               class="rounded-lg px-3.5 py-2 {{ $tab === 'revisi' ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Perlu Revisi ({{ $jumlah['revisi'] }})
            </a>
            <a href="{{ route('verifikasi.index', ['tab' => 'tervalidasi']) }}"
               class="rounded-lg px-3.5 py-2 {{ $tab === 'tervalidasi' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Telah Divalidasi
            </a>
        </div>

        <form method="GET" action="{{ route('verifikasi.index') }}" class="flex items-center gap-2">
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
                    <th>Nomor & Tanggal</th>
                    <th>Perihal</th>
                    <th>Pengirim & Sumber</th>
                    <th>Lampiran</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($surat as $s)
                    <tr>
                        <td>
                            <p class="font-mono text-xs font-semibold text-slate-800">{{ $s->nomor_surat }}</p>
                            <p class="text-xs text-slate-400">{{ $s->tanggal_surat?->format('d/m/Y') }}</p>
                        </td>
                        <td class="whitespace-normal max-w-md">
                            <div class="flex items-center gap-2">
                                <x-sifat-badge :sifat="$s->sifat_surat" />
                                <a href="{{ route('surat.show', $s) }}" class="font-semibold text-slate-800 hover:text-brand-600">{{ $s->perihal }}</a>
                            </div>
                        </td>
                        <td class="text-xs">
                            <p class="font-medium text-slate-800">{{ $s->pengirim?->nama }}</p>
                            <p class="text-slate-400">{{ $s->pengirim?->unit?->nama_unit }} (Saran Kode {{ $s->pengirim?->unit?->kode_sumber_default ?? 'B' }})</p>
                        </td>
                        <td class="text-xs text-slate-600">{{ $s->lampiran->count() }} berkas</td>
                        <td><x-status-badge :status="$s->status_terkini" /></td>
                        <td class="text-right space-x-1.5">
                            <a href="{{ route('surat.show', $s) }}" class="{{ $tab === 'antrian' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                                {{ $tab === 'antrian' ? 'Periksa & Klasifikasi' : 'Detail' }}
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
                        <td colspan="6" class="py-10 text-center text-sm text-slate-400">Tidak ada surat pada tab ini.</td>
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
