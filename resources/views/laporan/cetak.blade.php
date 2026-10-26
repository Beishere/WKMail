<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Rekapitulasi Disposisi Surat</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white p-8 text-slate-900">
    <div class="no-print mb-6 flex justify-end gap-2">
        <button onclick="window.print()" class="btn-primary btn-sm">Cetak Sekarang</button>
        <button onclick="window.close()" class="btn-secondary btn-sm">Tutup</button>
    </div>

    <div class="border-b-2 border-slate-900 pb-4 text-center">
        <img src="{{ asset('kop-ukwk.jpeg') }}" alt="Kop Surat UKWK" class="mx-auto mb-3 max-h-36 w-full object-contain">
        <h1 class="text-base font-bold uppercase tracking-wide">Rekapitulasi & Laporan Disposisi Surat</h1>
        <p class="text-xs text-slate-600">Periode: {{ $dari->translatedFormat('d F Y') }} s/d {{ $sampai->translatedFormat('d F Y') }}</p>
    </div>

    <div class="my-4 flex flex-wrap gap-6 text-xs">
        <div><strong>Total Surat:</strong> {{ $total }}</div>
        <div><strong>Urgent:</strong> {{ $urgent }}</div>
        <div><strong>Kode A (Yayasan):</strong> {{ $perSumber['A'] ?? 0 }}</div>
        <div><strong>Kode B (Internal):</strong> {{ $perSumber['B'] ?? 0 }}</div>
        <div><strong>Kode C (Eksternal):</strong> {{ $perSumber['C'] ?? 0 }}</div>
    </div>

    <table class="w-full border-collapse border border-slate-300 text-xs">
        <thead>
        <tr class="bg-slate-100">
            <th class="border border-slate-300 px-2 py-1.5 text-left">No</th>
            <th class="border border-slate-300 px-2 py-1.5 text-left">Nomor Surat</th>
            <th class="border border-slate-300 px-2 py-1.5 text-left">Tanggal</th>
            <th class="border border-slate-300 px-2 py-1.5 text-left">Perihal</th>
            <th class="border border-slate-300 px-2 py-1.5 text-left">Pengirim / Unit</th>
            <th class="border border-slate-300 px-2 py-1.5 text-center">Sifat</th>
            <th class="border border-slate-300 px-2 py-1.5 text-center">Kode</th>
            <th class="border border-slate-300 px-2 py-1.5 text-left">Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse($surat as $i => $s)
            <tr>
                <td class="border border-slate-300 px-2 py-1.5">{{ $i + 1 }}</td>
                <td class="border border-slate-300 px-2 py-1.5 font-mono">{{ $s->nomor_surat }}</td>
                <td class="border border-slate-300 px-2 py-1.5">{{ $s->tanggal_surat?->format('d/m/Y') }}</td>
                <td class="border border-slate-300 px-2 py-1.5">{{ $s->perihal }}</td>
                <td class="border border-slate-300 px-2 py-1.5">{{ $s->pengirim?->nama }} ({{ $s->pengirim?->unit?->nama_unit }})</td>
                <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $s->sifat_surat }}</td>
                <td class="border border-slate-300 px-2 py-1.5 text-center font-mono">{{ $s->kode_klasifikasi ?? '-' }}</td>
                <td class="border border-slate-300 px-2 py-1.5">{{ $s->status_terkini }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="border border-slate-300 py-6 text-center text-slate-500">Tidak ada data surat.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-8 flex justify-end text-xs">
        <div class="text-center">
            <p>Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}</p>
            <p class="mt-1">Admin Surat Rektorat</p>
            <div class="h-14"></div>
            <p class="font-bold underline">{{ auth()->user()->nama }}</p>
        </div>
    </div>
</body>
</html>
