@php
    /** @var \App\Models\Pengguna $user */
    $user = auth()->user();
    $defaultSumber = old('kode_sumber', $surat->kode_sumber ?? $surat->pengirim?->unit?->kode_sumber_default ?? 'B');
    $bidangWrMap = $bidang->pluck('id_unit_wr', 'kode_bidang');
@endphp

@extends('layouts.app', [
    'title' => 'Detail & Disposisi Surat',
    'subtitle' => $surat->nomor_surat . ' — ' . $surat->perihal,
])

@section('content')
    <div class="mb-6">
        <x-progress-tracker :surat="$surat" />
    </div>

    <!-- Banner Catatan Revisi (jika status Perlu Revisi) -->
    @if($surat->status_terkini === \App\Models\Surat::PERLU_REVISI)
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900">
            <div>
                <p class="font-bold">Surat Dikembalikan untuk Revisi oleh Admin Rektorat</p>
                <p class="mt-0.5 text-xs">{{ $surat->catatan_admin }}</p>
            </div>
            @if($user->id_pengguna === $surat->id_pengirim)
                <a href="{{ route('surat.edit', $surat) }}" class="btn-danger btn-sm">Revisi & Kirim Ulang Surat</a>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Left 7 cols: Informasi Surat, Isi, Lampiran, Pohon Disposisi -->
        <div class="lg:col-span-7 space-y-6">
            <div class="card">
                <div class="card-header">
                    <div>
                        <span class="font-mono text-xs text-slate-500">{{ $surat->nomor_surat }}</span>
                        <h2 class="text-base font-bold text-slate-900">{{ $surat->perihal }}</h2>
                    </div>
                    <div class="no-print flex flex-wrap items-center gap-2">
                        @if($user->isAdmin() || ($user->id_pengguna === $surat->id_pengirim && in_array($surat->status_terkini, [\App\Models\Surat::TERKIRIM, \App\Models\Surat::PERLU_REVISI], true)))
                            <a href="{{ route('surat.edit', $surat) }}" class="btn-secondary btn-sm">Edit Surat</a>
                            <form method="POST" action="{{ route('surat.destroy', $surat) }}" onsubmit="return confirm('Hapus surat ini beserta seluruh riwayat dan lampirannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">Hapus</button>
                            </form>
                        @endif
                        <button type="button" onclick="window.print()" class="btn-secondary btn-sm">
                            Cetak Lembar Disposisi
                        </button>
                    </div>
                </div>

                <div class="card-body space-y-5">
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-xs">
                        <div class="rounded-lg bg-slate-50 p-3">
                            <dt class="text-slate-400">Pengirim Surat</dt>
                            <dd class="mt-0.5 font-semibold text-slate-800">{{ $surat->pengirim?->nama }}</dd>
                            <dd class="text-slate-500">{{ $surat->pengirim?->unit?->nama_unit ?? '-' }}</dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <dt class="text-slate-400">Tanggal Surat / Diterima Rektorat</dt>
                            <dd class="mt-0.5 font-semibold text-slate-800">{{ $surat->tanggal_surat?->translatedFormat('d F Y') }}</dd>
                            <dd class="text-slate-500">
                                Diterima: {{ $surat->tanggal_diterima ? $surat->tanggal_diterima->format('d/m/Y H:i') : 'Menunggu validasi' }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <dt class="text-slate-400">Klasifikasi Kode Sumber</dt>
                            <dd class="mt-0.5 font-semibold text-slate-800">
                                @if($surat->kode_sumber)
                                    Kode {{ $surat->kode_sumber }} — {{ $surat->kategoriSumber?->nama_kategori }}
                                @else
                                    <span class="text-slate-400">Belum diklasifikasi</span>
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3">
                            <dt class="text-slate-400">Klasifikasi Kode Bidang</dt>
                            <dd class="mt-0.5 font-semibold text-slate-800">
                                @if($surat->kode_bidang)
                                    Kode {{ $surat->kode_bidang }} — {{ $surat->bidang?->nama_bidang }}
                                @else
                                    <span class="text-slate-400">Belum diklasifikasi</span>
                                @endif
                            </dd>
                            @if($surat->unitSaran)
                                <dd class="text-brand-600 font-medium">Saran Tujuan: {{ $surat->unitSaran->nama_unit }}</dd>
                            @endif
                        </div>
                    </dl>

                    @if($surat->catatan_admin && $surat->status_terkini !== \App\Models\Surat::PERLU_REVISI)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
                            <span class="font-bold">Catatan Admin Rektorat:</span> {{ $surat->catatan_admin }}
                        </div>
                    @endif

                    <div>
                        <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Isi / Ringkasan Surat</h3>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 font-mono text-xs whitespace-pre-wrap leading-relaxed text-slate-800">{{ $surat->isi_surat ?: 'Tidak ada isi teks surat (lihat berkas lampiran).' }}</div>
                    </div>

                    <!-- Lampiran Pengirim -->
                    <div>
                        <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Berkas Lampiran Surat ({{ $surat->lampiran->count() }})</h3>
                        @if($surat->lampiran->isNotEmpty())
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($surat->lampiran as $lamp)
                                    <a href="{{ route('lampiran.unduh', $lamp) }}"
                                       class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs hover:border-brand-400 hover:bg-brand-50/40">
                                        <span class="font-semibold text-slate-700 truncate pr-2">{{ $lamp->nama_file }}</span>
                                        <span class="shrink-0 text-[11px] font-medium text-brand-600">{{ $lamp->ukuran_label }} ↓</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400">Tidak ada berkas lampiran yang diunggah.</p>
                        @endif
                    </div>

                    <!-- Bukti Penyelesaian Unit Teknis -->
                    @if($surat->bukti->isNotEmpty())
                        <div>
                            <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-emerald-700">Bukti Penyelesaian Tindak Lanjut ({{ $surat->bukti->count() }})</h3>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($surat->bukti as $bkt)
                                    <a href="{{ route('lampiran.unduh', $bkt) }}"
                                       class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50/60 px-3 py-2 text-xs hover:border-emerald-400">
                                        <div class="min-w-0 pr-2">
                                            <p class="font-semibold text-emerald-900 truncate">{{ $bkt->nama_file }}</p>
                                            <p class="text-[11px] text-emerald-700">{{ $bkt->disposisi?->unitTujuan?->nama_unit }}</p>
                                        </div>
                                        <span class="shrink-0 text-[11px] font-semibold text-emerald-700">{{ $bkt->ukuran_label }} ↓</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pohon Alur Disposisi (Rektor -> Wakil Rektor -> Unit Teknis) -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rantai & Instruksi Disposisi (Rektor → Wakil Rektor → Unit Teknis)</h3>
                </div>
                <div class="card-body space-y-4">
                    @forelse($pohonDisposisi as $disp)
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600">Disposisi Pimpinan</span>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ $disp->pemberi?->nama }} → {{ $disp->unitTujuan?->nama_unit }}
                                    </p>
                                </div>
                                <x-status-badge :status="$disp->status" />
                            </div>
                            <p class="mt-2 rounded-lg bg-slate-50 p-2.5 text-xs text-slate-700">
                                <span class="font-semibold">Instruksi:</span> {{ $disp->instruksi }}
                                @if($disp->catatan)
                                    <br><span class="text-slate-500">Catatan: {{ $disp->catatan }}</span>
                                @endif
                            </p>
                            <div class="mt-2 flex flex-wrap gap-4 text-[11px] text-slate-400">
                                <span>Tanggal: {{ $disp->tanggal_disposisi?->format('d/m/Y H:i') }}</span>
                                @if($disp->batas_waktu)
                                    <span class="{{ $disp->terlambat ? 'font-bold text-rose-600' : '' }}">
                                        Batas Waktu: {{ $disp->batas_waktu->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Turunan ke Unit Teknis -->
                            @if($disp->turunan->isNotEmpty())
                                <div class="mt-3 space-y-2 border-l-2 border-brand-200 pl-4">
                                    @foreach($disp->turunan as $sub)
                                        <div class="rounded-lg bg-slate-50 p-3">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <p class="text-xs font-bold text-slate-800">
                                                    ↳ Diteruskan ke {{ $sub->unitTujuan?->nama_unit }}
                                                </p>
                                                <x-status-badge :status="$sub->status" />
                                            </div>
                                            <p class="mt-1 text-xs text-slate-700">
                                                <span class="font-semibold">Instruksi WR:</span> {{ $sub->instruksi }}
                                                @if($sub->catatan)
                                                    <span class="text-slate-500"> ({{ $sub->catatan }})</span>
                                                @endif
                                            </p>
                                            <div class="mt-1 flex flex-wrap gap-4 text-[11px] text-slate-400">
                                                <span>Tanggal: {{ $sub->tanggal_disposisi?->format('d/m/Y H:i') }}</span>
                                                @if($sub->batas_waktu)
                                                    <span class="{{ $sub->terlambat ? 'font-bold text-rose-600' : '' }}">
                                                        Batas Waktu: {{ $sub->batas_waktu->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada disposisi untuk surat ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right 5 cols: Panel Tindakan Sesuai Peran + Riwayat Status -->
        <div class="lg:col-span-5 space-y-6">

            {{-- ============================================================ 1. PANEL ADMIN REKTORAT --}}
            @if($user->isAdmin() && $surat->status_terkini === \App\Models\Surat::TERKIRIM)
                <div class="card border-2 border-brand-500/30"
                     x-data="{
                        mode: 'validasi',
                        bidangWr: @js($bidangWrMap),
                        saranWr: '{{ old('id_unit_saran', $surat->id_unit_saran) }}',
                        pilihBidang(e) {
                            const kode = e.target.value;
                            if (this.bidangWr[kode]) {
                                this.saranWr = String(this.bidangWr[kode]);
                            }
                        }
                     }">
                    <div class="card-header bg-brand-50/60">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand-700">Filter Utama Rektorat</span>
                            <h3 class="card-title">Validasi & Klasifikasi Surat</h3>
                        </div>
                        <div class="flex rounded-lg bg-white p-0.5 ring-1 ring-slate-200 text-xs">
                            <button type="button" @click="mode = 'validasi'"
                                    :class="mode === 'validasi' ? 'bg-brand-600 text-white' : 'text-slate-600'"
                                    class="rounded-md px-2.5 py-1 font-semibold">Validasi</button>
                            <button type="button" @click="mode = 'tolak'"
                                    :class="mode === 'tolak' ? 'bg-rose-600 text-white' : 'text-slate-600'"
                                    class="rounded-md px-2.5 py-1 font-semibold">Minta Revisi</button>
                        </div>
                    </div>

                    <!-- Form Validasi & Klasifikasi -->
                    <form x-show="mode === 'validasi'" method="POST" action="{{ route('verifikasi.validasi', $surat) }}" class="card-body space-y-4">
                        @csrf
                        <div>
                            <label class="form-label">Kode Sumber Surat (A / B / C) <span class="text-rose-600">*</span></label>
                            <select name="kode_sumber" required class="form-input">
                                @foreach($kategori as $kat)
                                    <option value="{{ $kat->kode_sumber }}" @selected($defaultSumber === $kat->kode_sumber)>
                                        Kode {{ $kat->kode_sumber }} — {{ $kat->nama_kategori }} ({{ $kat->keterangan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Kode Bidang / Departemen <span class="text-rose-600">*</span></label>
                            <select name="kode_bidang" @change="pilihBidang($event)" required class="form-input">
                                <option value="">— Pilih Kode Bidang —</option>
                                @foreach($bidang as $b)
                                    <option value="{{ $b->kode_bidang }}" @selected(old('kode_bidang') === $b->kode_bidang)>
                                        Kode {{ $b->kode_bidang }} — {{ $b->nama_bidang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Saran Tujuan Disposisi (Wakil Rektor)</label>
                            <select name="id_unit_saran" x-model="saranWr" class="form-input">
                                <option value="">— Pilih Saran Wakil Rektor —</option>
                                @foreach($unitWr as $uwr)
                                    <option value="{{ $uwr->id_unit }}">{{ $uwr->nama_unit }}</option>
                                @endforeach
                            </select>
                            <p class="form-hint">Otomatis disarankan sesuai kode bidang untuk membantu Rektor.</p>
                        </div>

                        <div>
                            <label class="form-label">Catatan Pengantar untuk Rektor (Opsional)</label>
                            <textarea name="catatan_admin" rows="2" placeholder="Catatan kelengkapan berkas atau urgensi..." class="form-input">{{ old('catatan_admin') }}</textarea>
                        </div>

                        <button type="submit" class="btn-primary w-full">
                            Validasi & Teruskan ke Rektor
                        </button>
                    </form>

                    <!-- Form Kembalikan untuk Revisi -->
                    <form x-show="mode === 'tolak'" x-cloak method="POST" action="{{ route('verifikasi.tolak', $surat) }}" class="card-body space-y-4">
                        @csrf
                        <div>
                            <label class="form-label">Catatan Revisi untuk Pengirim <span class="text-rose-600">*</span></label>
                            <textarea name="catatan_revisi" rows="3" required
                                      placeholder="Jelaskan kekurangan surat atau lampiran yang perlu dilengkapi..."
                                      class="form-input">{{ old('catatan_revisi') }}</textarea>
                        </div>
                        <button type="submit" class="btn-danger w-full">
                            Kembalikan Surat (Perlu Revisi)
                        </button>
                    </form>
                </div>
            @endif

            {{-- ============================================================ 2. PANEL REKTOR --}}
            @if($user->isRektor() && in_array($surat->status_terkini, [\App\Models\Surat::DIVALIDASI, \App\Models\Surat::DIBACA], true))
                <div class="card border-2 border-brand-500/30" x-data="{ mode: 'disposisi', instruksi: '{{ old('instruksi') }}' }">
                    <div class="card-header bg-brand-50/60">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand-700">Keputusan Pimpinan</span>
                            <h3 class="card-title">Beri Disposisi Rektor</h3>
                        </div>
                        <div class="flex rounded-lg bg-white p-0.5 ring-1 ring-slate-200 text-xs">
                            <button type="button" @click="mode = 'disposisi'"
                                    :class="mode === 'disposisi' ? 'bg-brand-600 text-white' : 'text-slate-600'"
                                    class="rounded-md px-2.5 py-1 font-semibold">Disposisi</button>
                            <button type="button" @click="mode = 'arsip'"
                                    :class="mode === 'arsip' ? 'bg-slate-700 text-white' : 'text-slate-600'"
                                    class="rounded-md px-2.5 py-1 font-semibold">Arsipkan</button>
                        </div>
                    </div>

                    <form x-show="mode === 'disposisi'" method="POST" action="{{ route('rektor.disposisi', $surat) }}" class="card-body space-y-4">
                        @csrf
                        <div>
                            <label class="form-label">Pilih Wakil Rektor Tujuan <span class="text-rose-600">*</span></label>
                            <div class="space-y-2">
                                @foreach($unitWr as $uwr)
                                    @php $checked = ($surat->id_unit_saran === $uwr->id_unit) || ($surat->bidang?->id_unit_wr === $uwr->id_unit); @endphp
                                    <label class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-xs hover:bg-slate-50 cursor-pointer">
                                        <span class="flex items-center gap-2 font-medium text-slate-800">
                                            <input type="checkbox" name="unit_tujuan[]" value="{{ $uwr->id_unit }}"
                                                   @checked($checked)
                                                   class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            {{ $uwr->nama_unit }}
                                        </span>
                                        @if($checked)
                                            <span class="badge bg-brand-50 text-brand-700 ring-brand-500/20">Disarankan</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Instruksi Disposisi <span class="text-rose-600">*</span></label>
                            <div class="mb-2 flex flex-wrap gap-1.5">
                                @foreach(['Mohon ditindaklanjuti sesuai ketentuan.', 'Koordinasikan dengan Unit Teknis terkait.', 'Pelajari dan laporkan hasilnya.', 'Jadwalkan dan fasilitasi kegiatan.'] as $cepat)
                                    <button type="button" @click="instruksi = '{{ $cepat }}'"
                                            class="rounded-md bg-slate-100 px-2 py-1 text-[11px] text-slate-600 hover:bg-brand-50 hover:text-brand-700">
                                        + {{ $cepat }}
                                    </button>
                                @endforeach
                            </div>
                            <textarea name="instruksi" x-model="instruksi" rows="3" required
                                      placeholder="Tulis instruksi disposisi untuk Wakil Rektor..." class="form-input"></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="form-label">Batas Waktu Tindak Lanjut</label>
                                <input type="date" name="batas_waktu" value="{{ old('batas_waktu', now()->addDays(5)->toDateString()) }}" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Catatan Tambahan</label>
                                <input type="text" name="catatan" value="{{ old('catatan') }}" placeholder="Opsional" class="form-input">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary w-full">
                            Kirim Disposisi ke Wakil Rektor
                        </button>
                    </form>

                    <form x-show="mode === 'arsip'" x-cloak method="POST" action="{{ route('rektor.arsip', $surat) }}" class="card-body space-y-4">
                        @csrf
                        <p class="text-xs text-slate-600">Pilih opsi ini apabila surat hanya bersifat pemberitahuan/informasi dan tidak memerlukan tindak lanjut disposisi.</p>
                        <div>
                            <label class="form-label">Catatan Arsip (Opsional)</label>
                            <textarea name="catatan" rows="2" placeholder="Contoh: Sebagai informasi pimpinan." class="form-input"></textarea>
                        </div>
                        <button type="submit" class="btn-secondary w-full">
                            Arsipkan Surat Sebagai Informasi
                        </button>
                    </form>
                </div>
            @endif

            {{-- ============================================================ 3. PANEL WAKIL REKTOR & UNIT TEKNIS --}}
            @foreach($disposisiSaya as $dispItem)
                @if(! in_array($dispItem->status, [\App\Models\Surat::SELESAI, \App\Models\Surat::DIDISPOSISI], true))
                    <div class="card border-2 border-brand-500/30" x-data="{ tab: '{{ $user->isWakilRektor() ? 'teruskan' : 'progres' }}' }">
                        <div class="card-header bg-brand-50/60">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-700">Disposisi untuk {{ $dispItem->unitTujuan?->nama_unit }}</span>
                                <h3 class="card-title">Tindak Lanjut Disposisi</h3>
                            </div>
                        </div>

                        <div class="card-body space-y-4">
                            <div class="rounded-lg bg-slate-50 p-3 text-xs">
                                <p class="font-semibold text-slate-800">Instruksi dari {{ $dispItem->pemberi?->nama }}:</p>
                                <p class="mt-0.5 text-slate-700">{{ $dispItem->instruksi }}</p>
                            </div>

                            <div class="flex rounded-lg bg-slate-100 p-1 text-xs font-semibold">
                                @if($user->isWakilRektor())
                                    <button type="button" @click="tab = 'teruskan'"
                                            :class="tab === 'teruskan' ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600'"
                                            class="flex-1 rounded-md py-1.5">Teruskan ke Unit Teknis</button>
                                @endif
                                <button type="button" @click="tab = 'progres'"
                                        :class="tab === 'progres' ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600'"
                                        class="flex-1 rounded-md py-1.5">Update Status</button>
                                <button type="button" @click="tab = 'selesai'"
                                        :class="tab === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600'"
                                        class="flex-1 rounded-md py-1.5">Selesai & Bukti</button>
                            </div>

                            @if($user->isWakilRektor())
                                <form x-show="tab === 'teruskan'" method="POST" action="{{ route('disposisi.teruskan', $dispItem) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="form-label">Unit Teknis Tujuan (BAA / BAU / HMJ) <span class="text-rose-600">*</span></label>
                                        <div class="space-y-1.5">
                                            @foreach($unitTeknis as $ut)
                                                @php $defaultUt = $ut->id_induk === $user->id_unit; @endphp
                                                <label class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-xs hover:bg-slate-50 cursor-pointer">
                                                    <span class="flex items-center gap-2 font-medium text-slate-800">
                                                        <input type="checkbox" name="unit_tujuan[]" value="{{ $ut->id_unit }}"
                                                               @checked($defaultUt)
                                                               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                        {{ $ut->nama_unit }}
                                                    </span>
                                                    @if($defaultUt)
                                                        <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-500/20">Unit Bidang Anda</span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">Instruksi untuk Unit Teknis <span class="text-rose-600">*</span></label>
                                        <textarea name="instruksi" rows="2" required placeholder="Instruksi pelaksanaan teknis..." class="form-input"></textarea>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="form-label">Batas Waktu</label>
                                            <input type="date" name="batas_waktu" value="{{ $dispItem->batas_waktu?->format('Y-m-d') ?? now()->addDays(3)->toDateString() }}" class="form-input">
                                        </div>
                                        <div>
                                            <label class="form-label">Catatan</label>
                                            <input type="text" name="catatan" class="form-input" placeholder="Opsional">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn-primary w-full">Teruskan Disposisi ke Unit Teknis</button>
                                </form>
                            @endif

                            <!-- Form Update Status Diproses / Pending -->
                            <form x-show="tab === 'progres'" x-cloak method="POST" action="{{ route('disposisi.progres', $dispItem) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="form-label">Status Penanganan <span class="text-rose-600">*</span></label>
                                    <select name="status" class="form-input">
                                        <option value="Diproses" @selected($dispItem->status === 'Diproses')>Diproses (Sedang Dikerjakan)</option>
                                        <option value="Pending" @selected($dispItem->status === 'Pending')>Pending (Menunggu Kelengkapan / Konfirmasi)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Keterangan Progres</label>
                                    <textarea name="keterangan" rows="2" placeholder="Contoh: Sedang menyiapkan surat balasan / menunggu konfirmasi jadwal..." class="form-input"></textarea>
                                </div>
                                <button type="submit" class="btn-warning w-full">Simpan Pembaruan Status</button>
                            </form>

                            <!-- Form Selesaikan + Unggah Bukti -->
                            <form x-show="tab === 'selesai'" x-cloak method="POST" action="{{ route('disposisi.selesai', $dispItem) }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="form-label">Laporan Penyelesaian Tindak Lanjut <span class="text-rose-600">*</span></label>
                                    <textarea name="keterangan" rows="3" required placeholder="Ringkasan hasil tindak lanjut yang telah diselesaikan..." class="form-input"></textarea>
                                </div>
                                <div>
                                    <label class="form-label">Unggah Bukti Penyelesaian (SK / Surat Balasan / Dokumentasi)</label>
                                    <input type="file" name="bukti[]" multiple
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
                                           class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2 text-xs text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                                </div>
                                <button type="submit" class="btn-success w-full">Tandai Selesai & Kirim Notifikasi</button>
                            </form>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Timeline Riwayat Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Pelacakan Status (Audit Trail)</h3>
                </div>
                <div class="card-body">
                    <ol class="relative border-l-2 border-slate-200 ml-2 space-y-4">
                        @foreach($surat->riwayat as $rw)
                            <li class="ml-4">
                                <span class="absolute -left-2 mt-1 h-3.5 w-3.5 rounded-full border-2 border-white bg-brand-600"></span>
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <x-status-badge :status="$rw->status" />
                                    <span class="text-[11px] font-mono text-slate-400">{{ $rw->waktu?->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-700">{{ $rw->keterangan }}</p>
                                @if($rw->pengguna)
                                    <p class="mt-0.5 text-[11px] text-slate-400">
                                        Oleh: <span class="font-medium text-slate-600">{{ $rw->pengguna->nama }}</span>
                                        ({{ $rw->pengguna->role?->nama_role }})
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
