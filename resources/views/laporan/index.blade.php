@extends('layouts.app', [
    'title' => 'Rekap & Laporan Surat',
    'subtitle' => 'Rekapitulasi surat berdasarkan periode, status, kode sumber (A/B/C), dan kode bidang',
])

@section('content')
    <div class="card card-body mb-6">
        <form method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div>
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari->toDateString() }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai->toDateString() }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Surat::STATUSES as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Kode Sumber</label>
                <select name="kode_sumber" class="form-input">
                    <option value="">Semua Sumber</option>
                    @foreach($kategori as $k)
                        <option value="{{ $k->kode_sumber }}" @selected(request('kode_sumber') === $k->kode_sumber)>
                            Kode {{ $k->kode_sumber }} — {{ $k->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Kode Bidang</label>
                <select name="kode_bidang" class="form-input">
                    <option value="">Semua Bidang</option>
                    @foreach($bidang as $b)
                        <option value="{{ $b->kode_bidang }}" @selected(request('kode_bidang') === $b->kode_bidang)>
                            {{ $b->kode_bidang }} — {{ $b->nama_bidang }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Tampilkan</button>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
            <p class="text-xs text-slate-500">
                Periode: <span class="font-semibold text-slate-800">{{ $dari->translatedFormat('d M Y') }} – {{ $sampai->translatedFormat('d M Y') }}</span>
            </p>
            <div class="flex items-center gap-2">
                <a href="{{ route('laporan.cetak', request()->query()) }}" target="_blank" class="btn-secondary btn-sm">
                    Cetak Laporan / PDF
                </a>
                <a href="{{ route('laporan.export', request()->query()) }}" class="btn-success btn-sm">
                    Unduh CSV (Excel)
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="card card-body">
            <p class="text-xs text-slate-500">Total Surat pada Periode</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $total }}</p>
        </div>
        <div class="card card-body">
            <p class="text-xs text-slate-500">Surat Bersifat Urgent</p>
            <p class="mt-1 text-2xl font-bold text-rose-600">{{ $urgent }}</p>
        </div>
        <div class="card card-body">
            <p class="text-xs text-slate-500">Per Sumber (A / B / C)</p>
            <p class="mt-1 text-sm font-bold text-slate-800">
                A: {{ $perSumber['A'] ?? 0 }} • B: {{ $perSumber['B'] ?? 0 }} • C: {{ $perSumber['C'] ?? 0 }}
            </p>
        </div>
        <div class="card card-body">
            <p class="text-xs text-slate-500">Selesai / Diarsipkan</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">
                {{ ($perStatus[\App\Models\Surat::SELESAI] ?? 0) + ($perStatus[\App\Models\Surat::DIARSIPKAN] ?? 0) }}
            </p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor & Tanggal</th>
                    <th>Perihal</th>
                    <th>Pengirim</th>
                    <th>Kode</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($surat as $idx => $s)
                    <tr>
                        <td class="text-xs text-slate-400">{{ $surat->firstItem() + $idx }}</td>
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
                            <p class="text-slate-400">{{ $s->pengirim?->unit?->nama_unit }}</p>
                        </td>
                        <td class="font-mono text-xs">{{ $s->kode_klasifikasi ?? '—' }}</td>
                        <td><x-status-badge :status="$s->status_terkini" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-sm text-slate-400">Tidak ada surat pada periode ini.</td>
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
