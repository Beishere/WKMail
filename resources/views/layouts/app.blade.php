<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}WKMail • Universitas Katolik Widya Karya</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-ukwk.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen lg:h-screen lg:overflow-hidden" x-data="{ sidebarOpen: false }">
@php
    /** @var \App\Models\Pengguna $authUser */
    $authUser = auth()->user();
    $theme = $authUser->theme();
@endphp

<div class="flex min-h-screen lg:h-screen lg:overflow-hidden">
    <!-- Mobile backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-xs lg:hidden"></div>

    <!-- Sidebar (fixed on mobile/tablet drawer, static on lg+ desktop) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="no-print fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col {{ $theme['sidebar_bg'] }} text-slate-100 transition-transform duration-200 lg:static lg:w-60 lg:translate-x-0 shadow-xl">
        <!-- Brand WKMail -->
        <div class="flex h-14 shrink-0 items-center justify-between gap-2.5 border-b border-white/15 px-4">
            <div class="flex items-center gap-2.5 min-w-0">
                <img src="{{ asset('logo-ukwk.png') }}" alt="Logo UKWK" class="h-8 w-8 shrink-0 rounded-lg bg-white p-0.5 object-contain shadow-sm">
                <div class="leading-tight min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-extrabold tracking-tight text-white">WK<span class="text-[#ff854d]">Mail</span></span>
                        <span class="rounded bg-[#ed6623]/30 px-1.5 py-0.5 text-[10px] font-bold text-[#ffb370] ring-1 ring-[#ed6623]/50">UKWK</span>
                    </div>
                    <p class="text-[11px] text-white/75 truncate">Scientia ad Laborem</p>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="rounded-lg p-1 text-white/70 hover:bg-white/10 hover:text-white lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- User Role & Administrative Level Card -->
        <div class="mx-3 mt-2.5 rounded-xl bg-black/25 p-2.5 ring-1 ring-white/15">
            <span class="inline-block rounded-md px-2 py-0.5 text-[10px] font-bold leading-snug ring-1 {{ $theme['level_badge'] }}">
                {{ $theme['level'] }}
            </span>
            <p class="mt-1.5 text-xs font-bold text-white leading-snug break-words">{{ $authUser->nama }}</p>
            <p class="mt-0.5 text-[11px] text-white/80 font-medium leading-snug break-words">
                {{ $authUser->role?->nama_role }}{{ $authUser->unit ? ' • ' . $authUser->unit->nama_unit : '' }}
            </p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-2.5 py-2">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? $theme['nav_active'] : '' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                Dashboard
            </a>

            <a href="{{ route('surat.create') }}" class="nav-link {{ request()->routeIs('surat.create') ? $theme['nav_active'] : '' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Buat Surat Baru
            </a>

            @if($authUser->isAdmin())
                <div class="nav-section">Rektorat</div>
                <a href="{{ route('verifikasi.index') }}" class="nav-link {{ request()->routeIs('verifikasi.*') ? $theme['nav_active'] : '' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="flex-1">Verifikasi Surat</span>
                    @php $jmlVerif = \App\Models\Surat::where('status_terkini', \App\Models\Surat::TERKIRIM)->count(); @endphp
                    @if($jmlVerif > 0)
                        <span class="rounded-full bg-rose-500 px-1.5 py-0.2 text-[10px] font-bold text-white">{{ $jmlVerif }}</span>
                    @endif
                </a>
            @endif

            @if($authUser->isRektor())
                <div class="nav-section">Pimpinan</div>
                <a href="{{ route('rektor.index') }}" class="nav-link {{ request()->routeIs('rektor.*') ? $theme['nav_active'] : '' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />
                    </svg>
                    <span class="flex-1">Disposisi Rektor</span>
                    @php $jmlRektor = \App\Models\Surat::whereIn('status_terkini', [\App\Models\Surat::DIVALIDASI, \App\Models\Surat::DIBACA])->count(); @endphp
                    @if($jmlRektor > 0)
                        <span class="rounded-full bg-rose-500 px-1.5 py-0.2 text-[10px] font-bold text-white">{{ $jmlRektor }}</span>
                    @endif
                </a>
            @endif

            @if($authUser->isWakilRektor() || $authUser->isUnitTeknis())
                <div class="nav-section">Tindak Lanjut</div>
                <a href="{{ route('disposisi.index') }}" class="nav-link {{ request()->routeIs('disposisi.*') ? $theme['nav_active'] : '' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                    <span class="flex-1">Disposisi Masuk</span>
                    @php $jmlDisp = \App\Models\Disposisi::untuk($authUser)->whereNotIn('status', [\App\Models\Surat::SELESAI, \App\Models\Surat::DIDISPOSISI])->count(); @endphp
                    @if($jmlDisp > 0)
                        <span class="rounded-full bg-amber-500 px-1.5 py-0.2 text-[10px] font-bold text-white">{{ $jmlDisp }}</span>
                    @endif
                </a>
            @endif

            <div class="nav-section">Surat & Pelacakan</div>
            <a href="{{ route('surat.index') }}" class="nav-link {{ request()->routeIs('surat.index', 'surat.show', 'surat.edit') ? $theme['nav_active'] : '' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                {{ $authUser->isPengirim() ? 'Surat Saya' : 'Arsip & Daftar Surat' }}
            </a>

            <a href="{{ route('lacak') }}" class="nav-link {{ request()->routeIs('lacak') ? $theme['nav_active'] : '' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Lacak Status Surat
            </a>

            <a href="{{ route('notifikasi.index') }}" class="nav-link {{ request()->routeIs('notifikasi.*') ? $theme['nav_active'] : '' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span class="flex-1">Notifikasi</span>
                @if(($notifBelumDibaca ?? 0) > 0)
                    <span class="rounded-full bg-[#ed6623] px-1.5 py-0.2 text-[10px] font-bold text-white">{{ $notifBelumDibaca }}</span>
                @endif
            </a>

            @if($authUser->isAdmin() || $authUser->isRektor())
                <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? $theme['nav_active'] : '' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    Rekap & Laporan
                </a>
            @endif

            @if($authUser->isAdmin())
                <div class="nav-section">Master Data</div>
                <a href="{{ route('master.template.index') }}" class="nav-link {{ request()->routeIs('master.template.*') ? $theme['nav_active'] : '' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Template & Kop Surat
                </a>
                <a href="{{ route('master.kategori-sumber.index') }}" class="nav-link {{ request()->routeIs('master.kategori-sumber.*', 'master.kode-bidang.*') ? $theme['nav_active'] : '' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    Kode Sumber & Bidang
                </a>
                <a href="{{ route('master.unit.index') }}" class="nav-link {{ request()->routeIs('master.unit.*') ? $theme['nav_active'] : '' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    Unit & Organisasi
                </a>
                <a href="{{ route('master.pengguna.index') }}" class="nav-link {{ request()->routeIs('master.pengguna.*') ? $theme['nav_active'] : '' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Pengguna & Akses
                </a>
            @endif
        </nav>

        <!-- Footer Sidebar -->
        <div class="border-t border-white/15 p-2 flex items-center gap-1">
            <a href="{{ route('profil.edit') }}" class="nav-link flex-1 {{ request()->routeIs('profil.*') ? $theme['nav_active'] : '' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Profil
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit" class="nav-link w-full text-rose-200 hover:bg-rose-500/20 hover:text-white">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content (scrollable internally on desktop, natural scroll on mobile) -->
    <div class="flex flex-1 flex-col min-w-0 lg:h-screen lg:overflow-hidden">
        <!-- Responsive Topbar with Role Border Accent -->
        <header class="no-print flex min-h-14 shrink-0 items-center justify-between gap-2 sm:gap-3 {{ $theme['topbar_border'] }} bg-white px-3 py-2 sm:px-5">
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <button @click="sidebarOpen = true" class="rounded-lg p-1.5 text-slate-600 hover:bg-slate-100 lg:hidden shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 truncate">{{ $title ?? 'Dashboard' }}</h1>
                        <span class="hidden md:inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold ring-1 ring-inset {{ $theme['role_badge'] }}">
                            {{ $theme['jabatan'] }}
                        </span>
                    </div>
                    @isset($subtitle)
                        <p class="hidden xl:block text-[11px] text-slate-500 truncate">{{ $subtitle }}</p>
                    @endisset
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                <!-- Quick Search Tracking -->
                <form action="{{ route('lacak') }}" method="GET" class="hidden md:block">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Lacak nomor / perihal..."
                               class="w-44 lg:w-56 rounded-lg border-slate-300 py-1.5 pl-8 pr-2.5 text-xs focus:border-brand-500 focus:ring-brand-500">
                        <svg class="absolute left-2.5 top-2 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                </form>

                <!-- Notification Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative rounded-lg p-1.5 text-slate-600 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if(($notifBelumDibaca ?? 0) > 0)
                            <span class="absolute right-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white">
                                {{ $notifBelumDibaca > 9 ? '9+' : $notifBelumDibaca }}
                            </span>
                        @endif
                    </button>

                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="fixed inset-x-3 top-16 sm:Categories sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-1.5 sm:w-80 rounded-xl bg-white shadow-xl ring-1 ring-slate-200 z-50">
                        <div class="flex items-center justify-between border-b border-slate-100 px-3.5 py-2.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Notifikasi</span>
                            @if(($notifBelumDibaca ?? 0) > 0)
                                <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-brand-600 hover:underline">Tandai dibaca</button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-64 divide-y divide-slate-100 overflow-y-auto">
                            @forelse($notifTerbaru ?? [] as $n)
                                <a href="{{ route('notifikasi.buka', $n) }}"
                                   class="block px-3.5 py-2.5 text-xs transition hover:bg-slate-50 {{ ! $n->sudah_dibaca ? 'bg-brand-50/50' : '' }}">
                                    <p class="{{ ! $n->sudah_dibaca ? 'font-semibold text-slate-800' : 'text-slate-600' }} leading-snug">{{ $n->pesan }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">{{ $n->waktu?->diffForHumans() }}</p>
                                </a>
                            @empty
                                <p class="px-3.5 py-5 text-center text-xs text-slate-400">Belum ada notifikasi.</p>
                            @endforelse
                        </div>
                        <div class="border-t border-slate-100 px-3.5 py-2 text-center">
                            <a href="{{ route('notifikasi.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Lihat semua</a>
                        </div>
                    </div>
                </div>

                <!-- User Pill -->
                <a href="{{ route('profil.edit') }}" class="flex items-center gap-2 rounded-lg p-1 hover:bg-slate-100">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $theme['avatar_bg'] }} text-xs font-bold">
                        {{ $authUser->inisial }}
                    </span>
                    <span class="hidden text-xs font-semibold text-slate-700 sm:inline max-w-[150px] xl:max-w-[220px] truncate">{{ $authUser->nama }}</span>
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 lg:overflow-y-auto p-3 sm:p-4 lg:p-5">
            @if(session('success'))
                <div class="no-print mb-3 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                    <svg class="h-4 w-4 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="no-print mb-3 flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800">
                    <svg class="h-4 w-4 text-rose-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="no-print mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800">
                    <p class="font-semibold">Mohon periksa kembali isian berikut:</p>
                    <ul class="mt-0.5 list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
