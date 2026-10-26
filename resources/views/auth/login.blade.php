<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — SIDISURAT Universitas</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-900 text-slate-800 flex items-center justify-center p-4 sm:p-6">
@php
    $demoAccounts = [
        ['group' => 'Rektorat & Pimpinan', 'items' => [
            ['label' => 'Admin Surat Rektorat (Filter Utama)', 'email' => 'admin@univ.ac.id', 'badge' => 'Admin'],
            ['label' => 'Rektor (Pemberi Disposisi)', 'email' => 'rektor@univ.ac.id', 'badge' => 'Rektor'],
            ['label' => 'Wakil Rektor I (Akademik)', 'email' => 'wr1@univ.ac.id', 'badge' => 'WR I'],
            ['label' => 'Wakil Rektor II (Keuangan & Sarpras)', 'email' => 'wr2@univ.ac.id', 'badge' => 'WR II'],
            ['label' => 'Wakil Rektor III (Kemahasiswaan)', 'email' => 'wr3@univ.ac.id', 'badge' => 'WR III'],
        ]],
        ['group' => 'Unit Teknis Pelaksana', 'items' => [
            ['label' => 'BAA — Biro Administrasi Akademik', 'email' => 'baa@univ.ac.id', 'badge' => 'BAA'],
            ['label' => 'BAU — Biro Administrasi Umum & Keuangan', 'email' => 'bau@univ.ac.id', 'badge' => 'BAU'],
            ['label' => 'HMJ — Bagian Kemahasiswaan', 'email' => 'hmj@univ.ac.id', 'badge' => 'HMJ'],
        ]],
        ['group' => 'Pengirim Surat (Sumber A / B / C)', 'items' => [
            ['label' => 'Sekretariat Yayasan (Kode A)', 'email' => 'yayasan@univ.ac.id', 'badge' => 'Kode A'],
            ['label' => 'Dekanat Fakultas Teknik (Kode B)', 'email' => 'ft@univ.ac.id', 'badge' => 'Kode B'],
            ['label' => 'HMJ Teknik Informatika (Kode B)', 'email' => 'hmjti@univ.ac.id', 'badge' => 'Kode B'],
            ['label' => 'Ikatan Alumni (Kode B)', 'email' => 'alumni@univ.ac.id', 'badge' => 'Kode B'],
            ['label' => 'PT Mitra Teknologi Nusantara (Kode C)', 'email' => 'mitra@perusahaan.co.id', 'badge' => 'Kode C'],
        ]],
    ];
@endphp

<div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 rounded-2xl bg-white shadow-2xl overflow-hidden"
     x-data="{
        fillLogin(email) {
            this.$refs.email.value = email;
            this.$refs.password.value = 'password';
            this.$refs.form.submit();
        }
     }">

    <!-- Left: Login Form -->
    <div class="lg:col-span-5 p-5 sm:p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white font-bold shadow-md">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900">SIDISURAT</h1>
                    <p class="text-[11px] text-slate-500">Sistem Disposisi Surat Online UKWK</p>
                </div>
            </div>

            <div class="mt-4">
                <h2 class="text-lg font-bold text-slate-900">Masuk ke Akun Anda</h2>
                <p class="mt-0.5 text-xs text-slate-500">Kelola pengiriman, validasi, disposisi, dan pelacakan status surat secara terpadu.</p>
            </div>

            @if($errors->any())
                <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form x-ref="form" method="POST" action="{{ route('login') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="form-label" for="email">Alamat Email</label>
                    <input x-ref="email" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@univ.ac.id" class="form-input">
                </div>

                <div>
                    <label class="form-label" for="password">Password</label>
                    <input x-ref="password" id="password" type="password" name="password" required
                           placeholder="••••••••" class="form-input">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="inline-flex items-center gap-2 text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Ingat sesi saya
                    </label>
                    <span class="text-slate-400">Password demo: <code class="font-semibold text-slate-600">password</code></span>
                </div>

                <button type="submit" class="btn-primary w-full py-2">
                    Masuk ke Sistem
                </button>
            </form>
        </div>

        <div class="mt-4 border-t border-slate-100 pt-3 text-[11px] text-slate-400">
            Alur: <span class="font-medium text-slate-600">Terkirim → Divalidasi → Dibaca → Didisposisi → Diproses → Selesai</span>
        </div>
    </div>

    <!-- Right: Quick Demo Accounts -->
    <div class="lg:col-span-7 bg-slate-50 border-t lg:border-t-0 lg:border-l border-slate-200 p-5 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <span class="badge bg-brand-50 text-brand-700 ring-brand-600/20">Mode Demo Capstone</span>
                <h3 class="mt-0.5 text-sm font-bold text-slate-800">Login Cepat 1-Klik Sesuai Peran Aktor</h3>
            </div>
        </div>

        <div class="mt-3 space-y-2.5">
            @foreach($demoAccounts as $grp)
                <div>
                    <h4 class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $grp['group'] }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach($grp['items'] as $acc)
                            <button type="button" @click="fillLogin('{{ $acc['email'] }}')"
                                    class="group flex items-center justify-between rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-left transition hover:border-brand-400 hover:bg-brand-50/40">
                                <div class="min-w-0 pr-2">
                                    <p class="text-[11px] font-semibold text-slate-800 group-hover:text-brand-700 truncate">{{ $acc['label'] }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ $acc['email'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600 group-hover:bg-brand-600 group-hover:text-white">
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
