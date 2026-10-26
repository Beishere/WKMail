<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — WKMail • Universitas Katolik Widya Karya Malang</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-ukwk.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gradient-to-br from-[#171a4a] via-[#2d338c] to-[#3e2214] text-slate-800 flex items-center justify-center p-3 sm:p-5">
@php
    $demoAccounts = [
        ['group' => 'Tingkat I–III • Rektorat & Pimpinan UKWK (Periode 2024–2028)', 'items' => [
            ['label' => 'Prof. Dr. Ir. Kukuk Yudiono, M.S.', 'sub' => 'Rektor UKWK (Pemberi Disposisi)', 'email' => 'rektor@widyakarya.ac.id', 'badge' => 'Rektor • Tkt I', 'color' => 'bg-[#2d338c] text-white'],
            ['label' => 'Sekretariat Rektorat UKWK', 'sub' => 'Admin WKMail (Filter Utama)', 'email' => 'admin@widyakarya.ac.id', 'badge' => 'Admin • Tkt II', 'color' => 'bg-[#ed6623] text-white'],
            ['label' => 'Harsa Dhani, S.T., M.T., Ph.D.', 'sub' => 'Wakil Rektor I (Akademik)', 'email' => 'wr1ukwk@widyakarya.ac.id', 'badge' => 'WR I • Tkt III', 'color' => 'bg-violet-600 text-white'],
            ['label' => 'Galuh Budi Astuti, S.E., M.M.', 'sub' => 'Wakil Rektor II (Adm. Umum & Keuangan)', 'email' => 'wr2ukwk@widyakarya.ac.id', 'badge' => 'WR II • Tkt III', 'color' => 'bg-violet-600 text-white'],
            ['label' => 'Dr. Celina Tri Siwi, S.H., M.Hum.', 'sub' => 'Wakil Rektor III (Kemahasiswaan & Alumni)', 'email' => 'wr3ukwk@widyakarya.ac.id', 'badge' => 'WR III • Tkt III', 'color' => 'bg-violet-600 text-white'],
        ]],
        ['group' => 'Tingkat IV • Biro & Unit Pendukung UKWK', 'items' => [
            ['label' => 'BAA — Biro Administrasi Akademik', 'sub' => 'baa@widyakarya.ac.id', 'email' => 'baa@widyakarya.ac.id', 'badge' => 'BAA • Tkt IV', 'color' => 'bg-emerald-600 text-white'],
            ['label' => 'BAU — Biro Administrasi dan Umum', 'sub' => 'bau@widyakarya.ac.id', 'email' => 'bau@widyakarya.ac.id', 'badge' => 'BAU • Tkt IV', 'color' => 'bg-emerald-600 text-white'],
            ['label' => 'BKA — Biro Kemahasiswaan & Alumni', 'sub' => 'bka@widyakarya.ac.id', 'email' => 'bka@widyakarya.ac.id', 'badge' => 'BKA • Tkt IV', 'color' => 'bg-emerald-600 text-white'],
            ['label' => 'LPPM / LPM / PUSTIK UKWK', 'sub' => 'lppm@widyakarya.ac.id', 'email' => 'lppm@widyakarya.ac.id', 'badge' => 'LPPM • Tkt IV', 'color' => 'bg-emerald-600 text-white'],
        ]],
        ['group' => 'Tingkat V • Pengirim Surat (Yayasan A / Fakultas & Internal B / Mitra C)', 'items' => [
            ['label' => 'Budi Santoso Djadja, S.H., M.M.', 'sub' => 'Yayasan PTK Adisucipto Malang', 'email' => 'yayasan@widyakarya.ac.id', 'badge' => 'Kode A • Yayasan', 'color' => 'bg-amber-500 text-slate-950'],
            ['label' => 'Dr. Diah Imaningrum Susanti, S.H., M.Hum.', 'sub' => 'Dekan Fakultas Hukum (FH)', 'email' => 'fh@widyakarya.ac.id', 'badge' => 'Kode B • FH', 'color' => 'bg-sky-600 text-white'],
            ['label' => 'Dekan Fakultas Teknik (FT)', 'sub' => 'Mesin, Sipil, SI eCommerce', 'email' => 'teknik@widyakarya.ac.id', 'badge' => 'Kode B • FT', 'color' => 'bg-sky-600 text-white'],
            ['label' => 'Dekan Fakultas Ekonomi & Bisnis (FEB)', 'sub' => 'Manajemen & Akuntansi', 'email' => 'feb@widyakarya.ac.id', 'badge' => 'Kode B • FEB', 'color' => 'bg-sky-600 text-white'],
            ['label' => 'Dekan Fakultas Pertanian (FP)', 'sub' => 'Agribisnis & Teknologi Pangan', 'email' => 'pertanian@widyakarya.ac.id', 'badge' => 'Kode B • FP', 'color' => 'bg-sky-600 text-white'],
            ['label' => 'HMJ Sistem Informasi eCommerce', 'sub' => 'hmjsi@widyakarya.ac.id', 'email' => 'hmjsi@widyakarya.ac.id', 'badge' => 'Kode B • HMJ', 'color' => 'bg-sky-600 text-white'],
            ['label' => 'Mitra Kerjasama Nasional & Internasional', 'sub' => 'mitra@institusi.co.id', 'email' => 'mitra@institusi.co.id', 'badge' => 'Kode C • Mitra', 'color' => 'bg-slate-700 text-white'],
        ]],
    ];
@endphp

<div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 rounded-2xl bg-white shadow-2xl overflow-hidden border-t-4 border-[#ed6623]"
     x-data="{
        fillLogin(email) {
            this.$refs.email.value = email;
            this.$refs.password.value = 'password';
            this.$refs.form.submit();
        }
     }">

    <!-- Left: Login Form -->
    <div class="lg:col-span-4 p-5 flex flex-col justify-between bg-gradient-to-b from-white to-slate-50">
        <div>
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo-ukwk.png') }}" alt="Logo UKWK" class="h-11 w-11 rounded-xl bg-white p-1 shadow-md ring-1 ring-slate-200 object-contain">
                <div>
                    <div class="flex items-center gap-1.5">
                        <h1 class="text-lg font-extrabold tracking-tight text-[#2d338c]">WK<span class="text-[#ed6623]">Mail</span></h1>
                        <span class="rounded bg-[#ed6623]/15 px-1.5 py-0.5 text-[10px] font-bold text-[#ed6623]">UKWK</span>
                    </div>
                    <p class="text-[11px] font-medium text-slate-500">Universitas Katolik Widya Karya Malang</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-[#2d338c]/5 border border-[#2d338c]/15 px-3 py-2">
                <p class="text-[11px] font-bold text-[#2d338c]">Scientia ad Laborem</p>
                <p class="text-[10px] text-slate-600">Sistem Disposisi & Pelacakan Surat Resmi Kampus Jl. Bondowoso No. 2 Malang</p>
            </div>

            @if($errors->any())
                <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form x-ref="form" method="POST" action="{{ route('login') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="form-label" for="email">Email Resmi UKWK</label>
                    <input x-ref="email" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@widyakarya.ac.id" class="form-input">
                </div>

                <div>
                    <label class="form-label" for="password">Password</label>
                    <input x-ref="password" id="password" type="password" name="password" required
                           placeholder="••••••••" class="form-input">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="inline-flex items-center gap-2 text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#2d338c] focus:ring-[#2d338c]">
                        Ingat sesi
                    </label>
                    <span class="text-slate-400">Password: <code class="font-semibold text-slate-700">password</code></span>
                </div>

                <button type="submit" class="w-full rounded-lg bg-[#ed6623] hover:bg-[#d95316] text-white font-bold py-2 text-xs shadow-sm transition">
                    Masuk ke WKMail
                </button>
            </form>
        </div>

        <div class="mt-4 border-t border-slate-200 pt-2.5 text-[10px] text-slate-500">
            Alur: <span class="font-semibold text-[#2d338c]">Terkirim → Divalidasi → Dibaca → Didisposisi → Diproses → Selesai</span>
        </div>
    </div>

    <!-- Right: Quick Demo Accounts -->
    <div class="lg:col-span-8 bg-slate-50 border-t lg:border-t-0 lg:border-l border-slate-200 p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
            <div>
                <span class="badge bg-[#ed6623]/15 text-[#b43e12] ring-[#ed6623]/30">Akses Cepat Pejabat & Unit UKWK</span>
                <h3 class="mt-0.5 text-xs font-bold text-slate-800">Klik Jabatan untuk Masuk & Lihat Tema Warna Dashboard Sesuai Tingkat</h3>
            </div>
        </div>

        <div class="space-y-2.5">
            @foreach($demoAccounts as $grp)
                <div>
                    <h4 class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $grp['group'] }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach($grp['items'] as $acc)
                            <button type="button" @click="fillLogin('{{ $acc['email'] }}')"
                                    class="group flex items-center justify-between rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-left transition hover:border-[#ed6623] hover:bg-orange-50/30 shadow-sm">
                                <div class="min-w-0 pr-2">
                                    <p class="text-[11px] font-bold text-slate-800 group-hover:text-[#2d338c] truncate">{{ $acc['label'] }}</p>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $acc['sub'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-md px-1.5 py-0.5 text-[9px] font-bold {{ $acc['color'] }}">
                                    {{ $acc['badge'] }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
</body>
</html>
