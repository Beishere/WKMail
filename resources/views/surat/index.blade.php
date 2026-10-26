@php
    /** @var \App\Models\Pengguna $authUser */
    $authUser = auth()->user();
    $allIds = $surat->pluck('id_surat')->values();
@endphp

@extends('layouts.app', [
    'title' => $authUser->isPengirim() ? 'Surat Saya' : 'Daftar & Arsip Surat',
    'subtitle' => 'Pencarian, filter klasifikasi, dan pengelolaan surat',
])

@section('content')
    <!-- Compact 1-Line Filter Toolbar -->
    <div class="card px-3.5 py-2.5 mb-3" x-data="{ showDate: {{ request()->filled('dari') || request()->filled('sampai') ? 'true' : 'false' }} }">
        <form method="GET" action="{{ route('surat.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor / perihal / pengirim..."
                   class="form-input w-48 sm:w-56 flex-1 min-w-[160px]">

            <select name="status" class="form-input w-auto">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Surat::STATUSES as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                @endforeach
            </select>

            <select name="sifat" class="form-input w-auto">
                <option value="">Semua Sifat</option>
                <option value="Biasa" @selected(request('sifat') === 'Biasa')>Biasa</option>
                <option value="Urgent" @selected(request('sifat') === 'Urgent')>Urgent</option>
            </select>

            <select name="kode_sumber" class="form-input w-auto">
                <option value="">Sumber (A/B/C)</option>
                @foreach($kategori as $k)
                    <option value="{{ $k->kode_sumber }}" @selected(request('kode_sumber') === $k->kode_sumber)>
                        Kode {{ $k->kode_sumber }} — {{ $k->nama_kategori }}
                    </option>
                @endforeach
            </select>

            <select name="kode_bidang" class="form-input w-auto">
                <option value="">Semua Bidang</option>
                @foreach($bidang as $b)
                    <option value="{{ $b->kode_bidang }}" @selected(request('kode_bidang') === $b->kode_bidang)>
                        {{ $b->kode_bidang }} — {{ $b->nama_bidang }}
                    </option>
                @endforeach
            </select>

            <button type="button" @click="showDate = !showDate" class="btn-secondary btn-sm">Tanggal</button>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            @if(request()->anyFilled(['q', 'status', 'sifat', 'kode_sumber', 'kode_bidang', 'dari', 'sampai']))
                <a href="{{ route('surat.index') }}" class="btn-secondary btn-sm">Reset</a>
            @endif
            <a href="{{ route('surat.create') }}" class="btn-success btn-sm">+ Buat Surat</a>

            <div x-show="showDate" x-cloak class="w-full flex items-center gap-2 pt-1.5 border-t border-slate-100 text-xs text-slate-600">
                <span>Periode:</span>
                <input type="date" name="dari" value="{{ request('dari') }}" class="form-input py-1 w-auto">
                <span>s/d</span>
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-input py-1 w-auto">
            </div>
        </form>
    </div>

    <!-- Compact Table with Single & Bulk Delete -->
    <div class="card"
         x-data="{
            selected: [],
            allIds: @js($allIds),
            toggleAll(e) {
                this.selected = e.target.checked ? [...this.allIds] : [];
            }
         }">

        <div x-show="selected.length > 0" x-cloak
             class="flex items-center justify-between border-b border-rose-200 bg-rose-50 px-4 py-2 text-xs">
            <span class="font-semibold text-rose-900">
                <span x-text="selected.length"></span> surat dipilih
            </span>
            <form method="POST" action="{{ route('surat.destroy-bulk') }}"
                  onsubmit="return confirm('Hapus seluruh surat yang dipilih beserta lampiran dan riwayatnya?')">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="btn-danger btn-sm">
                    Hapus Terpilih (<span x-text="selected.length"></span>)
                </button>
            </form>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th class="w-7">
                        <input type="checkbox"
                               @change="toggleAll($event)"
                               :checked="allIds.length > 0 && selected.length === allIds.length"
                               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    </th>
                    <th>Nomor & Tanggal</th>
                    <th>Perihal</th>
                    <th>Pengirim</th>
                    <th>Kode</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($surat as $s)
                    @php
                        $bisaKelola = $authUser->isAdmin() || $authUser->isRektor() || $s->id_pengirim === $authUser->id_pengguna;
                    @endphp
                    <tr>
                        <td>
                            @if($bisaKelola)
                                <input type="checkbox" :value="{{ $s->id_surat }}" x-model="selected"
                                       class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            @endif
                        </td>
                        <td>
                            <p class="font-mono text-[11px] font-semibold text-slate-800 leading-tight">{{ $s->nomor_surat }}</p>
                            <p class="text-[10px] text-slate-400">{{ $s->tanggal_surat?->format('d M Y') }}</p>
                        </td>
                        <td class="whitespace-normal max-w-sm">
                            <div class="flex items-center gap-1.5">
                                <x-sifat-badge :sifat="$s->sifat_surat" />
                                <a href="{{ route('surat.show', $s) }}" class="font-semibold text-slate-800 hover:text-brand-600 truncate">
                                    {{ $s->perihal }}
                                </a>
                            </div>
                        </td>
                        <td>
                            <p class="font-medium text-slate-800 leading-tight">{{ $s->pengirim?->nama }}</p>
                            <p class="text-[10px] text-slate-400">{{ $s->pengirim?->unit?->nama_unit }}</p>
                        </td>
                        <td>
                            @if($s->kode_klasifikasi)
                                <span class="badge bg-slate-100 text-slate-700 ring-slate-400/20 font-mono">
                                    {{ $s->kode_klasifikasi }}
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td><x-status-badge :status="$s->status_terkini" /></td>
                        <td class="text-right space-x-1">
                            <a href="{{ route('surat.show', $s) }}" class="btn-secondary btn-sm">Detail</a>
                            @if($bisaKelola)
                                <a href="{{ route('surat.edit', $s) }}" class="btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('surat.destroy', $s) }}" class="inline"
                                      onsubmit="return confirm('Hapus surat {{ $s->nomor_surat }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-xs text-slate-400">Tidak ditemukan data surat yang sesuai kriteria.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($surat->hasPages())
            <div class="border-t border-slate-100 px-4 py-2">
                {{ $surat->links() }}
            </div>
        @endif
    </div>
@endsection
