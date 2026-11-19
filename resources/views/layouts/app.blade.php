<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth overflow-x-hidden {{ ($currentTheme ?? 'light') === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Digi-Safe Purwakarta - Platform edukasi keamanan digital masyarakat Purwakarta.">
    <title>@yield('title', 'Digi-Safe Purwakarta - Platform Edukasi Keamanan Digital')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @view-transition {
            navigation: auto;
        }
        #pref-transition-veil {
            position: fixed;
            inset: 0;
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            will-change: clip-path, opacity;
        }
        html.pref-enter-active body > main {
            animation: prefMainGlideIn 320ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        @keyframes prefMainGlideIn {
            from { opacity: 0.35; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            html.pref-enter-active body > main {
                animation: none !important;
            }
        }
    </style>
    <script>
        (function () {
            try {
                var raw = sessionStorage.getItem('digisafe_pref_tx');
                if (!raw) return;
                var tx = JSON.parse(raw);
                if (!tx || (Date.now() - (tx.ts || 0)) > 4500) {
                    sessionStorage.removeItem('digisafe_pref_tx');
                    return;
                }
                document.documentElement.classList.add('pref-enter-active');
                window.__digisafeIncomingPrefTx = tx;
                sessionStorage.removeItem('digisafe_pref_tx');

                if (tx.kind === 'theme') {
                    var bg = tx.value === 'dark' ? '#0B1120' : '#F4F6FB';
                    var st = document.createElement('style');
                    st.id = 'pref-initial-veil-style';
                    st.textContent = '#pref-transition-veil{opacity:1;background:' + bg + ';transition:opacity 340ms cubic-bezier(0.22,1,0.36,1);}';
                    document.head.appendChild(st);
                } else if (tx.kind === 'locale') {
                    var isDarkNow = document.documentElement.classList.contains('dark');
                    var lbg = isDarkNow ? 'rgba(11,17,32,0.55)' : 'rgba(255,255,255,0.68)';
                    var lst = document.createElement('style');
                    lst.id = 'pref-initial-veil-style';
                    lst.textContent = '#pref-transition-veil{opacity:1;background:' + lbg + ';backdrop-filter:blur(6px);transition:opacity 300ms cubic-bezier(0.22,1,0.36,1);}';
                    document.head.appendChild(lst);
                }
            } catch (_) {}
        })();
    </script>
</head>

<body
    class="{{ ($currentTheme ?? 'light') === 'dark' ? 'bg-[#0B1120] text-[#F1F5F9]' : 'bg-white text-ink' }} antialiased selection:bg-lime selection:text-ink overflow-x-hidden transition-colors duration-300">

    <!-- overlay transisi halus untuk pergantian Tema (Light/Dark) & Bahasa (ID/EN) -->
    <div id="pref-transition-veil" aria-hidden="true"></div>

    <!-- container global notifikasi pop-up kanan atas (fixed, tidak pernah mendorong/mengubah tinggi halaman) -->
    <div
        id="digisafe-toast-stack"
        class="fixed top-20 right-4 sm:right-6 z-[95] flex flex-col items-end gap-2.5 pointer-events-none max-w-sm w-[calc(100vw-2rem)]"
    >
        @if (session('success'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4200)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-6 scale-95"
                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-6 scale-95"
                role="status"
                class="pointer-events-auto w-full rounded-2xl bg-ink text-white border border-lime/40 shadow-[0_14px_34px_rgba(15,23,42,0.22)] px-4 py-3 flex items-center justify-between gap-3 text-xs font-semibold"
            >
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-6 h-6 rounded-full bg-lime text-ink flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </span>
                    <span class="leading-snug">{{ session('success') }}</span>
                </div>
                <button
                    type="button"
                    @click="show = false"
                    aria-label="Tutup notifikasi"
                    class="w-6 h-6 rounded-full text-white/70 hover:text-white hover:bg-white/10 flex items-center justify-center shrink-0 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
                </button>
            </div>
        @endif

        @if (session('error') && ! request()->routeIs('login', 'register'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4800)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-6 scale-95"
                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-6 scale-95"
                role="alert"
                class="pointer-events-auto w-full rounded-2xl bg-red-600 text-white border border-red-400/40 shadow-[0_14px_34px_rgba(220,38,38,0.25)] px-4 py-3 flex items-center justify-between gap-3 text-xs font-semibold"
            >
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-6 h-6 rounded-full bg-white text-red-600 flex items-center justify-center shrink-0 font-extrabold">!</span>
                    <span class="leading-snug">{{ session('error') }}</span>
                </div>
                <button
                    type="button"
                    @click="show = false"
                    aria-label="Tutup notifikasi"
                    class="w-6 h-6 rounded-full text-white/80 hover:text-white hover:bg-white/10 flex items-center justify-center shrink-0 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
                </button>
            </div>
        @endif
    </div>

    @php
        $isBeranda = request()->routeIs('beranda');
        $isEdukasi = request()->routeIs('edukasi');
        $isMinigame = request()->routeIs('minigame.*');
        $isForum = request()->routeIs('forum.*');
        $isTestimoni = request()->routeIs('testimoni.*');
        $isTentang = request()->routeIs('tentang.*');
        $isAdminPage = request()->routeIs('admin.*');
        $isProfilePage = request()->routeIs('profile.*');
        $isUsersDirectory = request()->routeIs('users.directory');
        $isLeaderboard = request()->routeIs('leaderboard.*');
        $forceSolidNav = $isEdukasi || $isTentang || $isMinigame || $isForum || $isTestimoni || $isAdminPage || $isProfilePage || $isUsersDirectory || $isLeaderboard;
        $isDark = ($currentTheme ?? 'light') === 'dark';
    @endphp

    <!-- navbar sticky (Urutan seragam di seluruh panel: Beranda, Edukasi, Minigame, Forum, Testimoni, Tentang) -->
    <header x-data="{ scrolled: {{ $forceSolidNav ? 'true' : 'false' }}, mobileOpen: false, profileOpen: false }"
        @scroll.window.passive="scrolled = {{ $forceSolidNav ? 'true' : 'window.scrollY > 50' }}"
        @click.outside="mobileOpen = false"
        @keydown.escape.window="mobileOpen = false; profileOpen = false" :class="scrolled
            ? '{{ $isDark ? 'bg-[#0F172A]/95 border-slate-800 text-slate-100' : 'bg-white/95 border-gray-100 text-ink' }} backdrop-blur-md shadow-neutral py-3 border-b'
            : '{{ $isEdukasi ? 'bg-ink/20 backdrop-blur-xs py-4 text-white' : 'bg-transparent py-4 sm:py-5 text-white' }}'"
        class="fixed top-0 left-0 right-0 w-full max-w-[100vw] z-50 transition-all duration-300">
        <div :class="scrolled ? 'px-5 sm:px-6 lg:px-8' : '{{ $isBeranda ? 'px-6 sm:px-8 lg:px-10' : 'px-5 sm:px-6 lg:px-8' }}'"
            class="max-w-7xl mx-auto transition-all duration-300">
            <nav class="flex items-center justify-between gap-2" aria-label="Navigasi Utama">

                <!-- logo kiri -->
                <a href="{{ route('beranda') }}" class="flex items-center gap-2.5 sm:gap-3 group focus:outline-none min-w-0">
                    <span :class="scrolled
                            ? 'bg-white border border-gray-100 shadow-xs'
                            : 'bg-white/95 shadow-sm'"
                        class="w-11 h-10 rounded-xl flex items-center justify-center p-2.5 transition-all duration-300 group-hover:scale-105">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe Purwakarta"
                            class="w-full h-full object-contain select-none">
                    </span>
                    <div class="flex flex-col">
                        <span class="font-heading font-bold text-lg tracking-tight leading-none">
                            Digi-Safe
                        </span>
                        <span :class="scrolled ? 'text-muted' : 'text-white/80'"
                            class="text-[10px] font-medium tracking-[0.14em] uppercase mt-0.5">
                            Purwakarta
                        </span>
                    </div>
                </a>

                <!-- menu navigasi tengah: Beranda, Edukasi, Minigame, Forum, Testimoni, Tentang -->
                <div class="hidden lg:flex items-center gap-7 xl:gap-9">
                    <a href="{{ route('beranda') }}"
                        :class="scrolled
                            ? '{{ $isBeranda ? ($isDark ? 'text-white font-bold' : 'text-ink font-bold') : ($isDark ? 'text-slate-300 hover:text-sky font-medium' : 'text-ink/70 hover:text-sky font-medium') }}'
                            : '{{ $isBeranda ? 'text-white font-bold' : 'text-white/80 hover:text-white font-medium' }}'"
                        class="relative text-xs uppercase tracking-[0.18em] py-1 transition-colors flex items-center gap-1.5">
                        @if ($isBeranda)
                            <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                        @endif
                        <span>{{ __('ui.nav.beranda') }}</span>
                        @if ($isBeranda)
                            <span :class="scrolled ? 'bg-sky' : 'bg-lime'"
                                class="absolute -bottom-1 left-0 right-0 h-[2px] rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('edukasi') }}"
                        :class="scrolled
                            ? '{{ $isEdukasi ? ($isDark ? 'text-white font-bold' : 'text-ink font-bold') : ($isDark ? 'text-slate-300 hover:text-sky font-medium' : 'text-ink/70 hover:text-sky font-medium') }}'
                            : '{{ $isEdukasi ? 'text-white font-bold' : 'text-white/80 hover:text-white font-medium' }}'"
                        class="relative text-xs uppercase tracking-[0.18em] py-1 transition-colors flex items-center gap-1.5">
                        @if ($isEdukasi)
                            <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                        @endif
                        <span>{{ __('ui.nav.edukasi') }}</span>
                        @if ($isEdukasi)
                            <span :class="scrolled ? 'bg-sky' : 'bg-lime'"
                                class="absolute -bottom-1 left-0 right-0 h-[2px] rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('minigame.index') }}"
                        :class="scrolled
                            ? '{{ $isMinigame ? ($isDark ? 'text-white font-bold' : 'text-ink font-bold') : ($isDark ? 'text-slate-300 hover:text-sky font-medium' : 'text-ink/70 hover:text-sky font-medium') }}'
                            : '{{ $isMinigame ? 'text-white font-bold' : 'text-white/80 hover:text-white font-medium' }}'"
                        class="relative text-xs uppercase tracking-[0.18em] py-1 transition-colors flex items-center gap-1.5">
                        @if ($isMinigame)
                            <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                        @endif
                        <span>{{ __('ui.nav.minigame') }}</span>
                        @if ($isMinigame)
                            <span :class="scrolled ? 'bg-sky' : 'bg-lime'"
                                class="absolute -bottom-1 left-0 right-0 h-[2px] rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('forum.index') }}" :class="scrolled
                            ? '{{ $isForum ? ($isDark ? 'text-white font-bold' : 'text-ink font-bold') : ($isDark ? 'text-slate-300 hover:text-sky font-medium' : 'text-ink/70 hover:text-sky font-medium') }}'
                            : '{{ $isForum ? 'text-white font-bold' : 'text-white/80 hover:text-white font-medium' }}'"
                        class="relative text-xs uppercase tracking-[0.18em] py-1 transition-colors flex items-center gap-1.5">
                        @if ($isForum)
                            <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                        @endif
                        <span>{{ __('ui.nav.forum') }}</span>
                        @if ($isForum)
                            <span :class="scrolled ? 'bg-sky' : 'bg-lime'"
                                class="absolute -bottom-1 left-0 right-0 h-[2px] rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('testimoni.index') }}"
                        :class="scrolled
                            ? '{{ $isTestimoni ? ($isDark ? 'text-white font-bold' : 'text-ink font-bold') : ($isDark ? 'text-slate-300 hover:text-sky font-medium' : 'text-ink/70 hover:text-sky font-medium') }}'
                            : '{{ $isTestimoni ? 'text-white font-bold' : 'text-white/80 hover:text-white font-medium' }}'"
                        class="relative text-xs uppercase tracking-[0.18em] py-1 transition-colors flex items-center gap-1.5">
                        @if ($isTestimoni)
                            <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                        @endif
                        <span>{{ __('ui.nav.testimoni') }}</span>
                        @if ($isTestimoni)
                            <span :class="scrolled ? 'bg-sky' : 'bg-lime'"
                                class="absolute -bottom-1 left-0 right-0 h-[2px] rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('tentang.index') }}"
                        :class="scrolled
                            ? '{{ $isTentang ? ($isDark ? 'text-white font-bold' : 'text-ink font-bold') : ($isDark ? 'text-slate-300 hover:text-sky font-medium' : 'text-ink/70 hover:text-sky font-medium') }}'
                            : '{{ $isTentang ? 'text-white font-bold' : 'text-white/80 hover:text-white font-medium' }}'"
                        class="relative text-xs uppercase tracking-[0.18em] py-1 transition-colors flex items-center gap-1.5">
                        @if ($isTentang)
                            <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                        @endif
                        <span>{{ __('ui.nav.tentang') }}</span>
                        @if ($isTentang)
                            <span :class="scrolled ? 'bg-sky' : 'bg-lime'"
                                class="absolute -bottom-1 left-0 right-0 h-[2px] rounded-full"></span>
                        @endif
                    </a>
                </div>

                <!-- area kanan: tombol cepat bahasa/tema + profil/gabung -->
                <div class="hidden lg:flex items-center gap-2.5">
                    @php
                        $nextLocale = ($currentLocale ?? app()->getLocale()) === 'en' ? 'id' : 'en';
                        $nextTheme = $isDark ? 'light' : 'dark';
                    @endphp

                    <!-- Toggle Bahasa Cepat (ID / EN) -->
                    <form method="POST" action="{{ route('preferences.quick') }}" data-quick-pref-form data-pref-kind="locale" data-pref-value="{{ $nextLocale }}" class="inline-flex">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $nextLocale }}">
                        <button type="submit"
                            :class="scrolled
                                ? '{{ $isDark ? 'bg-slate-800 hover:bg-slate-700 text-slate-200 border-slate-700' : 'bg-gray-100 hover:bg-gray-200 text-ink border-gray-200' }}'
                                : 'bg-white/15 hover:bg-white/25 text-white border-white/30 backdrop-blur-xs'"
                            class="inline-flex items-center justify-center rounded-full border px-2.5 py-1.5 text-[11px] font-heading font-bold tracking-wider uppercase transition-all duration-200 active:scale-95 cursor-pointer"
                            title="{{ $nextLocale === 'en' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia' }}">
                            {{ strtoupper($currentLocale ?? app()->getLocale()) }}
                        </button>
                    </form>

                    <!-- Toggle Tema Cepat (Light / Dark) -->
                    <form method="POST" action="{{ route('preferences.quick') }}" data-quick-pref-form data-pref-kind="theme" data-pref-value="{{ $nextTheme }}" class="inline-flex">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $nextTheme }}">
                        <button type="submit"
                            :class="scrolled
                                ? '{{ $isDark ? 'bg-slate-800 hover:bg-slate-700 text-lime border-slate-700' : 'bg-gray-100 hover:bg-gray-200 text-ink border-gray-200' }}'
                                : 'bg-white/15 hover:bg-white/25 text-white border-white/30 backdrop-blur-xs'"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full border transition-all duration-200 active:scale-90 cursor-pointer"
                            aria-label="{{ $isDark ? 'Aktifkan Mode Terang' : 'Aktifkan Mode Gelap' }}"
                            title="{{ $isDark ? 'Mode Terang' : 'Mode Gelap' }}">
                            @if ($isDark)
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="4" />
                                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                                </svg>
                            @endif
                        </button>
                    </form>

                    @guest
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center rounded-full bg-lime text-ink font-bold text-xs tracking-wide px-6 py-2.5 shadow-sm hover:-translate-y-0.5 transition-transform duration-300">
                            {{ __('ui.nav.join') }}
                        </a>
                    @endguest

                    @auth
                        <div class="relative" @click.outside="profileOpen = false">
                            <button type="button" @click="profileOpen = !profileOpen"
                                :class="scrolled
                                    ? '{{ $isDark ? 'bg-slate-800 hover:bg-slate-700 text-slate-100 border-slate-700' : 'bg-gray-100/90 hover:bg-gray-200/70 text-ink border-gray-200' }}'
                                    : 'bg-white/20 hover:bg-white/30 text-white border-white/35 backdrop-blur-xs'"
                                class="inline-flex items-center gap-2.5 rounded-full border pl-1.5 pr-3.5 py-1.5 transition-all duration-200 cursor-pointer">
                                <x-user-avatar :user="Auth::user()" size="sm" class="ring-2 ring-lime" />

                                <span class="text-xs font-semibold tracking-tight max-w-[130px] truncate">
                                    {{ Auth::user()->nama_lengkap ?: Auth::user()->username }}
                                </span>

                                @if (Auth::user()->isOwner())
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-ink text-lime border border-lime/40 px-2 py-0.5 text-[10px] font-extrabold tracking-wide">
                                        <svg class="w-3 h-3 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                        </svg>
                                        <span>Owner</span>
                                    </span>
                                @elseif (Auth::user()->isAdmin())
                                    <span class="rounded-full bg-lime text-ink px-2 py-0.5 text-[10px] font-extrabold">
                                        Admin
                                    </span>
                                @endif

                                <svg class="w-3.5 h-3.5 opacity-75 transition-transform duration-200"
                                    :class="profileOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </button>

                            <!-- dropdown profil user (mendukung Light & Dark Mode) -->
                            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-2 scale-95" style="display: none;"
                                class="absolute right-0 top-full mt-2.5 w-64 rounded-[20px] {{ $isDark ? 'bg-[#0F172A] text-slate-100 border-slate-800' : 'bg-white text-ink border-gray-100' }} shadow-neutral border p-2 z-50">
                                <div class="px-3.5 py-2.5 border-b {{ $isDark ? 'border-slate-800' : 'border-gray-100' }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs font-bold {{ $isDark ? 'text-white' : 'text-ink' }} truncate">
                                            {{ Auth::user()->nama_lengkap ?: Auth::user()->username }}
                                        </p>
                                        @if (Auth::user()->isOwner())
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-ink text-lime border border-lime/30 px-2 py-0.5 text-[9px] font-extrabold shrink-0">
                                                <svg class="w-2.5 h-2.5 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                                </svg>
                                                <span>Owner</span>
                                            </span>
                                        @elseif (Auth::user()->isAdmin())
                                            <span
                                                class="rounded-full bg-sky/10 text-sky px-2 py-0.5 text-[9px] font-extrabold shrink-0">
                                                Admin
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ $isDark ? 'text-slate-400' : 'text-muted' }} truncate mt-0.5">
                                        {{ Auth::user()->email }}
                                    </p>
                                </div>

                                <div class="py-1">
                                    @if (Auth::user()->hasAdminAccess())
                                        <a href="{{ route('admin.dashboard') }}"
                                            class="flex items-center justify-between rounded-xl px-3.5 py-2 text-xs font-semibold {{ $isAdminPage ? 'bg-sky/15 text-sky' : ($isDark ? 'text-slate-200 hover:bg-slate-800' : 'text-ink hover:bg-gray-50') }} transition-colors">
                                            <div class="flex items-center gap-2.5">
                                                <svg class="w-4 h-4 text-sky" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                                </svg>
                                                <span>{{ __('ui.nav.admin_panel') }}</span>
                                            </div>
                                            <span
                                                class="rounded-md bg-lime px-1.5 py-0.5 text-[9px] font-extrabold text-ink">{{ __('ui.nav.manage') }}</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('profile.edit') }}"
                                        class="flex items-center gap-2.5 rounded-xl px-3.5 py-2 text-xs font-semibold {{ $isProfilePage ? 'bg-sky/15 text-sky' : ($isDark ? 'text-slate-200 hover:bg-slate-800' : 'text-ink hover:bg-gray-50') }} transition-colors">
                                        <svg class="w-4 h-4 {{ $isDark ? 'text-slate-400' : 'text-muted' }}" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                        <span>{{ __('ui.nav.my_profile') }}</span>
                                    </a>

                                    <a href="{{ route('leaderboard.index') }}"
                                        class="flex items-center gap-2.5 rounded-xl px-3.5 py-2 text-xs font-semibold {{ $isLeaderboard ? 'bg-sky/15 text-sky' : ($isDark ? 'text-slate-200 hover:bg-slate-800' : 'text-ink hover:bg-gray-50') }} transition-colors">
                                        <svg class="w-4 h-4 {{ $isDark ? 'text-slate-400' : 'text-muted' }}" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
                                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
                                            <path d="M4 22h16" />
                                            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
                                        </svg>
                                        <span>Leaderboard</span>
                                    </a>

                                    <a href="{{ route('users.directory') }}"
                                        class="flex items-center gap-2.5 rounded-xl px-3.5 py-2 text-xs font-semibold {{ $isUsersDirectory ? 'bg-sky/15 text-sky' : ($isDark ? 'text-slate-200 hover:bg-slate-800' : 'text-ink hover:bg-gray-50') }} transition-colors">
                                        <svg class="w-4 h-4 {{ $isDark ? 'text-slate-400' : 'text-muted' }}" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                        </svg>
                                        <span>{{ app()->getLocale() === 'en' ? 'Community' : 'Komunitas' }}</span>
                                    </a>
                                </div>

                                <div class="pt-1 border-t {{ $isDark ? 'border-slate-800' : 'border-gray-100' }}">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center gap-2.5 rounded-xl px-3.5 py-2 text-xs font-semibold text-red-500 {{ $isDark ? 'hover:bg-red-950/50' : 'hover:bg-red-50' }} transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                                <polyline points="16 17 21 12 16 7" />
                                                <line x1="21" y1="12" x2="9" y2="12" />
                                            </svg>
                                            <span>{{ __('ui.nav.logout') }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>

                <!-- kontrol cepat bahasa/tema + tombol hamburger mobile -->
                <div class="lg:hidden flex items-center gap-1.5 shrink-0">
                    <form method="POST" action="{{ route('preferences.quick') }}" data-quick-pref-form data-pref-kind="locale" data-pref-value="{{ $nextLocale }}" class="inline-flex">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $nextLocale }}">
                        <button type="submit"
                            :class="scrolled
                                ? '{{ $isDark ? 'bg-slate-800 text-slate-200 border-slate-700' : 'bg-gray-100 text-ink border-gray-200' }}'
                                : 'bg-white/15 text-white border-white/30'"
                            class="inline-flex items-center justify-center min-w-[38px] h-9 rounded-full border px-2.5 text-[11px] font-heading font-bold uppercase transition-transform active:scale-95 cursor-pointer"
                            aria-label="{{ $nextLocale === 'en' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia' }}">
                            {{ strtoupper($currentLocale ?? app()->getLocale()) }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('preferences.quick') }}" data-quick-pref-form data-pref-kind="theme" data-pref-value="{{ $nextTheme }}" class="inline-flex">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $nextTheme }}">
                        <button type="submit"
                            :class="scrolled
                                ? '{{ $isDark ? 'bg-slate-800 text-lime border-slate-700' : 'bg-gray-100 text-ink border-gray-200' }}'
                                : 'bg-white/15 text-white border-white/30'"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-full border transition-transform active:scale-90 cursor-pointer"
                            aria-label="{{ $isDark ? 'Aktifkan Mode Terang' : 'Aktifkan Mode Gelap' }}">
                            @if ($isDark)
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" /></svg>
                            @else
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" /></svg>
                            @endif
                        </button>
                    </form>

                    <button type="button" @click="mobileOpen = !mobileOpen"
                        :class="scrolled ? '{{ $isDark ? 'text-white hover:bg-slate-800' : 'text-ink hover:bg-gray-100' }}' : 'text-white hover:bg-white/10'"
                        class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl transition-colors focus:outline-none cursor-pointer"
                        aria-label="Buka Menu"
                        :aria-expanded="mobileOpen">
                        <svg x-show="!mobileOpen" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round">
                            <path d="M4 7h16M4 12h16M4 17h12" />
                        </svg>
                        <svg x-show="mobileOpen" style="display: none;" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </nav>

            <!-- menu mobile (mendukung Light & Dark Mode + scrollable di layar pendek) -->
            <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                style="display: none;"
                class="lg:hidden mt-3 max-h-[calc(100dvh-5.5rem)] overflow-y-auto overscroll-contain rounded-2xl {{ $isDark ? 'bg-[#0F172A] text-slate-100 border-slate-800' : 'bg-white text-ink border-gray-100' }} shadow-neutral border p-5 space-y-2.5">

                <!-- Quick Toggle Bahasa & Tema di Mobile -->
                <div class="flex items-center justify-between gap-2 pb-3 border-b {{ $isDark ? 'border-slate-800' : 'border-gray-100' }}">
                    <span class="text-[11px] font-semibold {{ $isDark ? 'text-slate-400' : 'text-muted' }}">
                        {{ app()->getLocale() === 'en' ? 'Display & Language' : 'Tampilan & Bahasa' }}
                    </span>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('preferences.quick') }}" data-quick-pref-form data-pref-kind="locale" data-pref-value="{{ $nextLocale }}">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $nextLocale }}">
                            <button type="submit"
                                class="rounded-full border px-3 py-1 text-[11px] font-heading font-bold uppercase transition-transform active:scale-95 cursor-pointer {{ $isDark ? 'bg-slate-800 border-slate-700 text-slate-200' : 'bg-gray-100 border-gray-200 text-ink' }}">
                                {{ strtoupper($currentLocale ?? app()->getLocale()) }} &rarr; {{ strtoupper($nextLocale) }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('preferences.quick') }}" data-quick-pref-form data-pref-kind="theme" data-pref-value="{{ $nextTheme }}">
                            @csrf
                            <input type="hidden" name="theme" value="{{ $nextTheme }}">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-heading font-bold transition-transform active:scale-95 cursor-pointer {{ $isDark ? 'bg-slate-800 border-slate-700 text-lime' : 'bg-gray-100 border-gray-200 text-ink' }}">
                                @if ($isDark)
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" /></svg>
                                    <span>Light</span>
                                @else
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" /></svg>
                                    <span>Dark</span>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>

                @php $mobileLinkIdle = $isDark ? 'font-semibold text-slate-300' : 'font-semibold text-ink/80'; @endphp
                <a href="{{ route('beranda') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isBeranda ? 'font-bold text-sky' : $mobileLinkIdle }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.beranda') }}</span>
                    @if ($isBeranda)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('edukasi') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isEdukasi ? 'font-bold text-sky' : $mobileLinkIdle }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.edukasi') }}</span>
                    @if ($isEdukasi)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('minigame.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isMinigame ? 'font-bold text-sky' : $mobileLinkIdle }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.minigame') }}</span>
                    @if ($isMinigame)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('forum.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isForum ? 'font-bold text-sky' : $mobileLinkIdle }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.forum') }}</span>
                    @if ($isForum)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('testimoni.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isTestimoni ? 'font-bold text-sky' : $mobileLinkIdle }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.testimoni') }}</span>
                    @if ($isTestimoni)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('leaderboard.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isLeaderboard ? 'font-bold text-sky' : $mobileLinkIdle }} uppercase tracking-[0.18em]">
                    <span>Leaderboard</span>
                    @if ($isLeaderboard)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('users.directory') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isUsersDirectory ? 'font-bold text-sky' : $mobileLinkIdle }} uppercase tracking-[0.18em]">
                    <span>{{ app()->getLocale() === 'en' ? 'Community' : 'Komunitas' }}</span>
                    @if ($isUsersDirectory)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('tentang.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isTentang ? 'font-bold text-sky' : $mobileLinkIdle }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.tentang') }}</span>
                    @if ($isTentang)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>

                <div class="pt-3 border-t {{ $isDark ? 'border-slate-800' : 'border-gray-100' }}">
                    @guest
                        <a href="{{ route('login') }}"
                            class="w-full inline-flex items-center justify-center rounded-full bg-lime text-ink font-bold text-xs px-5 py-3">
                            {{ __('ui.nav.join') }}
                        </a>
                    @endguest

                    @auth
                        <div class="flex items-center gap-3 pb-3">
                            <x-user-avatar :user="Auth::user()" size="md" class="ring-2 ring-lime" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p class="text-xs font-bold {{ $isDark ? 'text-white' : 'text-ink' }} truncate">
                                        {{ Auth::user()->nama_lengkap ?: Auth::user()->username }}
                                    </p>
                                    @if (Auth::user()->isOwner())
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-ink text-lime border border-lime/30 px-2 py-0.5 text-[9px] font-extrabold shrink-0">
                                            <svg class="w-2.5 h-2.5 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                            </svg>
                                            <span>Owner</span>
                                        </span>
                                    @elseif (Auth::user()->isAdmin())
                                        <span
                                            class="rounded-full bg-sky/10 text-sky px-2 py-0.5 text-[9px] font-extrabold shrink-0">Admin</span>
                                    @endif
                                </div>
                                <p class="text-[11px] {{ $isDark ? 'text-slate-400' : 'text-muted' }} truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            @if (Auth::user()->hasAdminAccess())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="block rounded-xl px-3 py-2 text-xs font-semibold text-sky {{ $isDark ? 'bg-sky/15 hover:bg-sky/20' : 'bg-sky/5 hover:bg-sky/10' }}">{{ __('ui.nav.admin_panel') }}</a>
                            @endif
                            <a href="{{ route('profile.edit') }}"
                                class="block rounded-xl px-3 py-2 text-xs font-semibold {{ $isDark ? 'text-slate-200 hover:bg-slate-800' : 'text-ink hover:bg-gray-50' }}">{{ __('ui.nav.my_profile') }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left rounded-xl px-3 py-2 text-xs font-semibold text-red-500 {{ $isDark ? 'hover:bg-red-950/50' : 'hover:bg-red-50' }}">
                                    {{ __('ui.nav.logout') }}
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <!-- footer (disembunyikan otomatis pada halaman yang mendeklarasikan hide_footer seperti Profile) -->
    @unless (View::hasSection('hide_footer'))
    <footer class="{{ $isDark ? 'bg-[#0F172A] border-slate-800 text-slate-200' : 'bg-white border-gray-100 text-ink' }} border-t py-12 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8 pb-8 border-b {{ $isDark ? 'border-slate-800' : 'border-gray-100' }}">
                <div class="flex items-center gap-3">
                    <span
                        class="w-11 h-10 rounded-xl bg-white border {{ $isDark ? 'border-slate-700' : 'border-gray-100' }} shadow-xs flex items-center justify-center p-2.5">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe Purwakarta"
                            class="w-full h-full object-contain">
                    </span>
                    <div>
                        <span class="font-heading font-bold text-base {{ $isDark ? 'text-white' : 'text-ink' }} block leading-none">Digi-Safe
                            Purwakarta</span>
                        <span class="text-xs {{ $isDark ? 'text-slate-400' : 'text-muted' }} mt-1 block">{{ __('ui.nav.footer_tagline') }}</span>
                    </div>
                </div>

                @php
                    $footerActive = $isDark ? 'text-white font-semibold' : 'text-ink font-semibold';
                    $footerIdle = $isDark ? 'text-slate-400 hover:text-white transition-colors' : 'text-muted hover:text-ink transition-colors';
                @endphp
                <div class="flex flex-wrap items-center gap-5 sm:gap-6 text-xs font-medium">
                    <a href="{{ route('beranda') }}" class="{{ $isBeranda ? $footerActive : $footerIdle }}">{{ ucfirst(strtolower(__('ui.nav.beranda'))) }}</a>
                    <a href="{{ route('edukasi') }}" class="{{ $isEdukasi ? $footerActive : $footerIdle }}">{{ ucfirst(strtolower(__('ui.nav.edukasi'))) }}</a>
                    <a href="{{ route('minigame.index') }}" class="{{ $isMinigame ? $footerActive : $footerIdle }}">{{ ucfirst(strtolower(__('ui.nav.minigame'))) }}</a>
                    <a href="{{ route('leaderboard.index') }}" class="{{ $isLeaderboard ? $footerActive : $footerIdle }}">Leaderboard</a>
                    <a href="{{ route('forum.index') }}" class="{{ $isForum ? $footerActive : $footerIdle }}">{{ ucfirst(strtolower(__('ui.nav.forum'))) }}</a>
                    <a href="{{ route('users.directory') }}" class="{{ $isUsersDirectory ? $footerActive : $footerIdle }}">{{ app()->getLocale() === 'en' ? 'Community' : 'Komunitas' }}</a>
                    <a href="{{ route('testimoni.index') }}" class="{{ $isTestimoni ? $footerActive : $footerIdle }}">{{ ucfirst(strtolower(__('ui.nav.testimoni'))) }}</a>
                    <a href="{{ route('tentang.index') }}" class="{{ $isTentang ? $footerActive : $footerIdle }}">{{ ucfirst(strtolower(__('ui.nav.tentang'))) }}</a>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs {{ $isDark ? 'text-slate-400' : 'text-muted' }}">
                <p>&copy; {{ date('Y') }} Digi-Safe Purwakarta &bull; Razief Afkar Rusli. {{ __('ui.nav.footer_rights') }}</p>

                <div class="flex items-center gap-2.5">
                    <a href="{{ route('apk.download') }}"
                        class="inline-flex items-center gap-2 rounded-full border {{ $isDark ? 'border-slate-700 bg-slate-800/80 text-slate-200 hover:border-lime hover:text-lime' : 'border-gray-200 bg-gray-50 text-ink hover:border-sky hover:text-sky' }} px-3.5 py-1.5 text-[11px] font-heading font-bold transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        <span>{{ app()->getLocale() === 'en' ? 'Download Android APK' : 'Unduh APK Android' }}</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    @endunless

    @unless (request()->routeIs('minigame.play'))
        <x-chat-widget />
    @endunless

    <script>
        (function () {
            // 0. Global Helper: Notifikasi Pop-up Kanan Atas (Tanpa Mendorong/Mengubah Tinggi Halaman)
            window.digisafeToast = function (message, type = 'success') {
                if (!message) return;
                const stack = document.getElementById('digisafe-toast-stack');
                if (!stack) return;

                const isErr = type === 'error';
                const card = document.createElement('div');
                card.setAttribute('role', isErr ? 'alert' : 'status');
                card.className = 'pointer-events-auto w-full rounded-2xl px-4 py-3 flex items-center justify-between gap-3 text-xs font-semibold transition-all duration-300 opacity-0 translate-x-6 scale-95 '
                    + (isErr
                        ? 'bg-red-600 text-white border border-red-400/40 shadow-[0_14px_34px_rgba(220,38,38,0.25)]'
                        : 'bg-ink text-white border border-lime/40 shadow-[0_14px_34px_rgba(15,23,42,0.22)]');

                const left = document.createElement('div');
                left.className = 'flex items-center gap-2.5 min-w-0';

                const iconWrap = document.createElement('span');
                iconWrap.className = 'w-6 h-6 rounded-full flex items-center justify-center shrink-0 '
                    + (isErr ? 'bg-white text-red-600 font-extrabold' : 'bg-lime text-ink');
                iconWrap.innerHTML = isErr
                    ? '!'
                    : '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>';

                const textSpan = document.createElement('span');
                textSpan.className = 'leading-snug';
                textSpan.textContent = message;

                left.appendChild(iconWrap);
                left.appendChild(textSpan);

                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.setAttribute('aria-label', 'Tutup notifikasi');
                closeBtn.className = 'w-6 h-6 rounded-full text-white/75 hover:text-white hover:bg-white/10 flex items-center justify-center shrink-0 cursor-pointer';
                closeBtn.innerHTML = '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>';

                const dismiss = () => {
                    card.classList.add('opacity-0', 'translate-x-6', 'scale-95');
                    setTimeout(() => card.remove(), 240);
                };
                closeBtn.addEventListener('click', dismiss);

                card.appendChild(left);
                card.appendChild(closeBtn);
                stack.appendChild(card);

                requestAnimationFrame(() => {
                    card.classList.remove('opacity-0', 'translate-x-6', 'scale-95');
                });

                setTimeout(dismiss, 4200);
            };

            window.addEventListener('digisafe-toast', (e) => {
                if (e.detail && e.detail.message) {
                    window.digisafeToast(e.detail.message, e.detail.type || 'success');
                }
            });

            const veil = document.getElementById('pref-transition-veil');
            const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const isEnLocale = @js(($currentLocale ?? app()->getLocale()) === 'en');

            // 1. Selesaikan animasi masuk halus jika baru berpindah tema/bahasa
            const incoming = window.__digisafeIncomingPrefTx;
            if (incoming) {
                if (typeof incoming.scrollY === 'number' && incoming.scrollY > 0) {
                    const prevScrollBehavior = document.documentElement.style.scrollBehavior;
                    document.documentElement.style.scrollBehavior = 'auto';
                    window.scrollTo(0, incoming.scrollY);
                    requestAnimationFrame(() => {
                        document.documentElement.style.scrollBehavior = prevScrollBehavior;
                    });
                }

                if (veil) {
                    if (incoming.kind === 'theme') {
                        const incomingToDark = incoming.value === 'dark';
                        const incomingLabel = incomingToDark
                            ? (isEnLocale ? 'Dark Mode' : 'Mode Gelap')
                            : (isEnLocale ? 'Light Mode' : 'Mode Terang');
                        const doneWidget = createIllustratedThemeSwitch(incomingToDark, incomingLabel, {
                            startCompleted: true,
                            isEn: isEnLocale
                        });
                        veil.replaceChildren(doneWidget.root);
                        requestAnimationFrame(() => {
                            doneWidget.root.style.transform = 'scale(1.03) translateY(-4px)';
                            doneWidget.root.style.opacity = '0';
                        });
                    }

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            veil.style.opacity = '0';
                            setTimeout(() => {
                                const initStyle = document.getElementById('pref-initial-veil-style');
                                if (initStyle) initStyle.remove();
                                veil.removeAttribute('style');
                                veil.replaceChildren();
                                document.documentElement.classList.remove('pref-enter-active');
                            }, 360);
                        });
                    });
                }
            }

            // Pembuat elemen visual Toggle Switch 3D:
            // Muncul sebagai bulatan kecil tepat dari posisi tombol navbar yang diklik -> terbang ke tengah sambil melebar (expand)
            // menjadi tombol switch 3D -> bergeser dengan animasi teks "Mode" mutlak dan kata "Dark/Light" ("Gelap/Terang") scroll ke bawah.
            function createIllustratedThemeSwitch(toDark, labelText, options = {}) {
                const startCompleted = Boolean(options.startCompleted);
                const isEn = Boolean(options.isEn);
                const originX = typeof options.originX === 'number' ? options.originX : (window.innerWidth - 60);
                const originY = typeof options.originY === 'number' ? options.originY : 28;

                // Kata yang berganti via scroll vertikal ke bawah vs kata "Mode" yang mutlak (diam)
                const darkWord = isEn ? 'Dark' : 'Gelap';
                const lightWord = isEn ? 'Light' : 'Terang';
                const fromWord = toDark ? lightWord : darkWord;
                const toWord = toDark ? darkWord : lightWord;

                // Hitung jarak dari tengah layar ke tombol toggle di navbar tempat user mengklik
                const dx = Math.round(originX - (window.innerWidth / 2));
                const dy = Math.round(originY - (window.innerHeight / 2) + 27);

                const wrap = document.createElement('div');
                wrap.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:18px;will-change:transform,opacity;'
                    + (startCompleted
                        ? 'transform:translate3d(0,0,0) scale(1);opacity:1;transition:transform 340ms cubic-bezier(0.22,1,0.36,1),opacity 300ms ease;'
                        : 'transform:translate3d(' + dx + 'px,' + dy + 'px,0) scale(0.34);opacity:0.95;transition:transform 400ms cubic-bezier(0.22,1,0.36,1),opacity 220ms ease;');

                const dayTrackBg = 'linear-gradient(135deg, #38BDF8 0%, #0EA5E9 52%, #0284C7 100%)';
                const nightTrackBg = 'linear-gradient(135deg, #1E1B4B 0%, #172554 50%, #1E3A8A 100%)';

                // State awal mengikuti tema sebelum diklik (misal Dark Mode dulu baru bergeser ke Light Mode)
                const initialIsDarkState = startCompleted ? toDark : !toDark;

                // Saat baru muncul dari tombol navbar: bentuk awalnya bulatan biasa (width 100px x height 100px),
                // lalu melebar (expand) menjadi kapsul switch 3D (width 228px x height 100px) saat tiba di tengah
                const initialTrackWidth = startCompleted ? '228px' : '100px';

                const track = document.createElement('div');
                track.style.cssText = 'position:relative;width:' + initialTrackWidth + ';height:100px;border-radius:999px;overflow:hidden;padding:10px;'
                    + 'box-shadow:0 24px 48px rgba(15,23,42,0.30),inset 0 4px 11px rgba(0,0,0,0.34),0 0 0 5px rgba(255,255,255,0.18);'
                    + 'background:' + (initialIsDarkState ? nightTrackBg : dayTrackBg) + ';'
                    + 'transition:width 340ms cubic-bezier(0.34,1.28,0.64,1),background 520ms cubic-bezier(0.22,1,0.36,1),box-shadow 520ms ease,transform 180ms ease;';

                // Lapisan bintang-bintang 4 sudut di sisi kanan untuk Mode Gelap
                const starsLayer = document.createElement('div');
                starsLayer.style.cssText = 'position:absolute;inset:0;width:228px;height:100px;pointer-events:none;transition:opacity 360ms ease,transform 500ms cubic-bezier(0.22,1,0.36,1);'
                    + ((startCompleted && toDark)
                        ? 'opacity:1;transform:translateY(0) scale(1);'
                        : 'opacity:0;transform:translateY(10px) scale(0.86);');
                starsLayer.innerHTML = ''
                    + '<svg viewBox="0 0 228 100" style="width:228px;height:100px;">'
                    + '  <path d="M166 18 L169.2 25.5 L176.5 28.5 L169.2 31.5 L166 39 L162.8 31.5 L155.5 28.5 L162.8 25.5 Z" fill="#FFFFFF" opacity="0.96"/>'
                    + '  <path d="M140 43 L142.2 47.5 L146.5 49.5 L142.2 51.5 L140 56 L137.8 51.5 L133.5 49.5 L137.8 47.5 Z" fill="#E0E7FF" opacity="0.92"/>'
                    + '  <path d="M184 58 L186.2 62.2 L190.5 64 L186.2 65.8 L184 70 L181.8 65.8 L177.5 64 L181.8 62.2 Z" fill="#FFFFFF" opacity="0.9"/>'
                    + '  <path d="M124 66 L125.8 69.8 L129.5 71.5 L125.8 73.2 L124 77 L122.2 73.2 L118.5 71.5 L122.2 69.8 Z" fill="#C7D2FE" opacity="0.86"/>'
                    + '  <circle cx="115" cy="26" r="2" fill="#E0E7FF" opacity="0.7"/>'
                    + '  <circle cx="198" cy="39" r="1.7" fill="#FFFFFF" opacity="0.65"/>'
                    + '  <circle cx="154" cy="76" r="1.5" fill="#C7D2FE" opacity="0.6"/>'
                    + '</svg>';

                // Lapisan awan lembut berlapis di sisi kiri/bawah untuk Mode Terang
                const cloudsLayer = document.createElement('div');
                cloudsLayer.style.cssText = 'position:absolute;inset:0;width:228px;height:100px;pointer-events:none;transition:opacity 360ms ease,transform 500ms cubic-bezier(0.22,1,0.36,1);'
                    + ((startCompleted && !toDark)
                        ? 'opacity:1;transform:translateY(0) scale(1);'
                        : 'opacity:0;transform:translateY(14px) scale(0.92);');
                cloudsLayer.innerHTML = ''
                    + '<svg viewBox="0 0 228 100" style="width:228px;height:100px;">'
                    + '  <g fill="#FFFFFF" opacity="0.32">'
                    + '    <circle cx="52" cy="78" r="25"/>'
                    + '    <circle cx="84" cy="82" r="21"/>'
                    + '    <circle cx="116" cy="80" r="23"/>'
                    + '  </g>'
                    + '  <g fill="#FFFFFF" opacity="0.88">'
                    + '    <circle cx="38" cy="86" r="19"/>'
                    + '    <circle cx="66" cy="84" r="21"/>'
                    + '    <circle cx="96" cy="88" r="18"/>'
                    + '    <circle cx="56" cy="28" r="5.5" opacity="0.58"/>'
                    + '    <circle cx="92" cy="22" r="3.8" opacity="0.48"/>'
                    + '  </g>'
                    + '</svg>';

                // Posisi Bola Geser (Thumb):
                // Saat masih berbentuk bulatan 100x100 dari navbar, thumb berada di 0px (tepat di tengah bulatan).
                // Saat bulatan expand menjadi kapsul 228x100, thumb menempati posisi mode awalnya (0px untuk Dark, 128px untuk Light),
                // lalu meluncur ke posisi mode tujuan (128px untuk Light, 0px untuk Dark).
                const darkThumbX = '0px';
                const lightThumbX = '128px';
                const expandedInitialThumbX = initialIsDarkState ? darkThumbX : lightThumbX;
                const targetThumbX = toDark ? darkThumbX : lightThumbX;
                const startThumbX = startCompleted ? targetThumbX : '0px';

                const darkThumbShadow = '0 0 0 10px rgba(167,139,250,0.24),0 0 0 18px rgba(167,139,250,0.10),0 12px 28px rgba(15,23,42,0.52)';
                const lightThumbShadow = '0 0 0 10px rgba(255,255,255,0.28),0 0 0 18px rgba(254,240,138,0.18),0 12px 26px rgba(2,132,199,0.38)';

                const thumb = document.createElement('div');
                thumb.style.cssText = 'position:relative;width:80px;height:80px;border-radius:999px;'
                    + 'transform:translate3d(' + startThumbX + ',0,0) scale(1) rotate(0deg);'
                    + 'transition:transform 340ms cubic-bezier(0.34, 1.28, 0.64, 1),box-shadow 500ms ease;'
                    + 'box-shadow:' + (initialIsDarkState ? darkThumbShadow : lightThumbShadow) + ';';

                // Wajah Matahari (Mode Terang)
                const sunFace = document.createElement('div');
                sunFace.style.cssText = 'position:absolute;inset:0;border-radius:999px;transition:opacity 380ms ease,transform 520ms cubic-bezier(0.34,1.36,0.64,1);'
                    + 'background:radial-gradient(circle at 35% 32%, #FEF9C3 0%, #FDE047 40%, #F59E0B 86%, #D97706 100%);'
                    + 'box-shadow:inset -5px -6px 12px rgba(180,83,9,0.36),inset 4px 4px 10px rgba(255,255,255,0.82);'
                    + (initialIsDarkState ? 'opacity:0;transform:scale(0.78) rotate(-25deg);' : 'opacity:1;transform:scale(1) rotate(0deg);');

                // Wajah Bulan Berkawah 3D (Mode Gelap)
                const moonFace = document.createElement('div');
                moonFace.style.cssText = 'position:absolute;inset:0;border-radius:999px;overflow:hidden;transition:opacity 380ms ease,transform 520ms cubic-bezier(0.34,1.36,0.64,1);'
                    + 'background:radial-gradient(circle at 36% 30%, #FFFFFF 0%, #F3E8FF 38%, #C4B5FD 74%, #7C3AED 100%);'
                    + 'box-shadow:inset -6px -7px 14px rgba(76,29,149,0.44),inset 4px 4px 10px rgba(255,255,255,0.92);'
                    + (initialIsDarkState ? 'opacity:1;transform:scale(1) rotate(0deg);' : 'opacity:0;transform:scale(0.78) rotate(25deg);');
                moonFace.innerHTML = ''
                    + '<svg viewBox="0 0 80 80" style="width:100%;height:100%;">'
                    + '  <circle cx="24.5" cy="27.5" r="9.6" fill="#C4B5FD" opacity="0.68"/>'
                    + '  <circle cx="25.7" cy="28.7" r="8.2" fill="#DDD6FE" opacity="0.86"/>'
                    + '  <circle cx="52" cy="24.5" r="5.6" fill="#A78BFA" opacity="0.58"/>'
                    + '  <circle cx="55" cy="45" r="8.6" fill="#A78BFA" opacity="0.62"/>'
                    + '  <circle cx="56" cy="46" r="7.2" fill="#C4B5FD" opacity="0.78"/>'
                    + '  <circle cx="28.5" cy="55" r="5.2" fill="#A78BFA" opacity="0.56"/>'
                    + '  <circle cx="39" cy="64" r="3.9" fill="#8B5CF6" opacity="0.52"/>'
                    + '</svg>';

                thumb.appendChild(sunFace);
                thumb.appendChild(moonFace);

                track.appendChild(starsLayer);
                track.appendChild(cloudsLayer);
                track.appendChild(thumb);

                // Kapsul Teks: Kata "Mode" mutlak (diam), sedangkan kata "Dark"/"Light" ("Gelap"/"Terang") berganti via animasi scroll ke bawah
                const statusBar = document.createElement('div');
                statusBar.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:7px 18px;border-radius:999px;'
                    + 'font-family:"Plus Jakarta Sans",sans-serif;font-weight:800;font-size:14px;letter-spacing:0.03em;'
                    + 'transition:background 480ms ease,color 480ms ease,border-color 480ms ease,box-shadow 480ms ease,opacity 240ms ease,transform 260ms cubic-bezier(0.22,1,0.36,1);'
                    + (startCompleted ? 'opacity:1;transform:translateY(0);' : 'opacity:0;transform:translateY(8px);')
                    + (initialIsDarkState
                        ? 'background:rgba(15,23,42,0.90);color:#F8FAFC;border:1px solid rgba(148,163,184,0.28);box-shadow:0 10px 26px rgba(2,6,23,0.45);'
                        : 'background:rgba(255,255,255,0.94);color:#0F172A;border:1px solid rgba(15,23,42,0.10);box-shadow:0 10px 26px rgba(15,23,42,0.10);');

                const staticModeSpan = document.createElement('span');
                staticModeSpan.textContent = 'Mode';
                staticModeSpan.style.cssText = 'line-height:24px;display:inline-block;';

                // Jendela clip vertikal (tinggi 24px) tempat kata lama scroll turun keluar & kata baru scroll turun masuk dari atas
                const wordWindow = document.createElement('span');
                wordWindow.style.cssText = 'display:inline-block;height:24px;overflow:hidden;position:relative;vertical-align:middle;';

                const wordReel = document.createElement('span');
                // Baris 0 (atas) = kata tujuan (toWord), Baris 1 (bawah) = kata awal (fromWord).
                // Saat bergeser dari translateY(-24px) -> translateY(0px), seluruh reel bergerak TURUN (scroll ke bawah)!
                wordReel.style.cssText = 'display:flex;flex-direction:column;will-change:transform;'
                    + 'transform:translate3d(0,' + (startCompleted ? '0px' : '-24px') + ',0);'
                    + 'transition:transform 500ms cubic-bezier(0.34, 1.36, 0.64, 1);';

                const topWordItem = document.createElement('span');
                topWordItem.textContent = toWord;
                topWordItem.style.cssText = 'height:24px;line-height:24px;display:flex;align-items:center;'
                    + (isEn ? 'justify-content:flex-end;' : 'justify-content:flex-start;')
                    + 'color:' + (toDark ? '#C4B5FD' : '#0284C7') + ';';

                const bottomWordItem = document.createElement('span');
                bottomWordItem.textContent = fromWord;
                bottomWordItem.style.cssText = 'height:24px;line-height:24px;display:flex;align-items:center;'
                    + (isEn ? 'justify-content:flex-end;' : 'justify-content:flex-start;')
                    + 'color:' + (initialIsDarkState ? '#C4B5FD' : '#0284C7') + ';';

                wordReel.appendChild(topWordItem);
                wordReel.appendChild(bottomWordItem);
                wordWindow.appendChild(wordReel);

                if (isEn) {
                    // Bahasa Inggris: "[Dark ⇓ Light] Mode"
                    statusBar.appendChild(wordWindow);
                    statusBar.appendChild(staticModeSpan);
                } else {
                    // Bahasa Indonesia: "Mode [Gelap ⇓ Terang]"
                    statusBar.appendChild(staticModeSpan);
                    statusBar.appendChild(wordWindow);
                }

                wrap.appendChild(track);
                wrap.appendChild(statusBar);

                return {
                    root: wrap,
                    play(onSlideStart) {
                        // Tahap 1 (0ms -> 380ms): Bulatan terbang dari posisi tombol navbar yang diklik ke tengah layar
                        wrap.style.transform = 'translate3d(0,0,0) scale(1)';
                        wrap.style.opacity = '1';

                        // Tahap 2 (110ms -> 430ms): Bulatan melebar (expand) menjadi tombol switch 3D utuh pada mode awal
                        setTimeout(() => {
                            track.style.width = '228px';
                            thumb.style.transform = 'translate3d(' + expandedInitialThumbX + ',0,0) scale(1) rotate(0deg)';

                            if (initialIsDarkState) {
                                starsLayer.style.opacity = '1';
                                starsLayer.style.transform = 'translateY(0) scale(1)';
                            } else {
                                cloudsLayer.style.opacity = '1';
                                cloudsLayer.style.transform = 'translateY(0) scale(1)';
                            }

                            statusBar.style.opacity = '1';
                            statusBar.style.transform = 'translateY(0)';
                        }, 110);

                        // Tahap 3 (520ms -> 1060ms): Tombol 3D bergeser ke mode tujuan & kata Dark/Light (Gelap/Terang) scroll ke bawah
                        setTimeout(() => {
                            if (typeof onSlideStart === 'function') {
                                onSlideStart();
                            }

                            track.style.background = toDark ? nightTrackBg : dayTrackBg;

                            thumb.style.transition = 'transform 540ms cubic-bezier(0.34, 1.36, 0.64, 1),box-shadow 500ms ease';
                            thumb.style.transform = 'translate3d(' + targetThumbX + ',0,0) scale(1) rotate(0deg)';
                            thumb.style.boxShadow = toDark ? darkThumbShadow : lightThumbShadow;

                            sunFace.style.opacity = toDark ? '0' : '1';
                            sunFace.style.transform = toDark ? 'scale(0.78) rotate(-25deg)' : 'scale(1) rotate(0deg)';

                            moonFace.style.opacity = toDark ? '1' : '0';
                            moonFace.style.transform = toDark ? 'scale(1) rotate(0deg)' : 'scale(0.78) rotate(25deg)';

                            starsLayer.style.opacity = toDark ? '1' : '0';
                            starsLayer.style.transform = toDark ? 'translateY(0) scale(1)' : 'translateY(-14px) scale(0.84)';

                            cloudsLayer.style.opacity = toDark ? '0' : '1';
                            cloudsLayer.style.transform = toDark ? 'translateY(16px) scale(0.92)' : 'translateY(0) scale(1)';

                            statusBar.style.background = toDark ? 'rgba(15,23,42,0.90)' : 'rgba(255,255,255,0.94)';
                            statusBar.style.color = toDark ? '#F8FAFC' : '#0F172A';
                            statusBar.style.borderColor = toDark ? 'rgba(148,163,184,0.28)' : 'rgba(15,23,42,0.10)';
                            statusBar.style.boxShadow = toDark ? '0 10px 26px rgba(2,6,23,0.45)' : '0 10px 26px rgba(15,23,42,0.10)';

                            // Animasi scroll ke bawah: kata lama turun keluar, kata baru turun masuk di sebelah teks "Mode" yang mutlak
                            wordReel.style.transform = 'translate3d(0,0px,0)';
                        }, 520);
                    }
                };
            }

            // 2. Intersep klik tombol cepat Tema (Light <-> Dark) & Bahasa (ID <-> EN)
            let isSwitchingPref = false;
            document.querySelectorAll('form[data-quick-pref-form]').forEach((form) => {
                form.addEventListener('submit', async (e) => {
                    if (reducedMotion || !veil) return;
                    e.preventDefault();
                    if (isSwitchingPref) return;
                    isSwitchingPref = true;

                    const kind = form.getAttribute('data-pref-kind') || 'theme';
                    const value = form.getAttribute('data-pref-value') || '';
                    const btn = form.querySelector('button[type="submit"]');
                    const rect = btn ? btn.getBoundingClientRect() : { left: window.innerWidth - 60, top: 28, width: 32, height: 32 };
                    const cx = Math.round(rect.left + rect.width / 2);
                    const cy = Math.round(rect.top + rect.height / 2);
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const payload = kind === 'theme' ? { theme: value } : { locale: value };

                    try {
                        sessionStorage.setItem('digisafe_pref_tx', JSON.stringify({
                            kind,
                            value,
                            scrollY: window.scrollY || 0,
                            ts: Date.now()
                        }));
                    } catch (_) {}

                    const saveReq = fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(payload)
                    }).catch(() => null);

                    const mainEl = document.querySelector('main');

                    if (kind === 'theme') {
                        const toDark = value === 'dark';
                        const maxR = Math.ceil(Math.hypot(
                            Math.max(cx, window.innerWidth - cx),
                            Math.max(cy, window.innerHeight - cy)
                        ));

                        const labelText = toDark
                            ? (isEnLocale ? 'Dark Mode' : 'Mode Gelap')
                            : (isEnLocale ? 'Light Mode' : 'Mode Terang');

                        // Mulai dari warna latar mode SAAT INI agar tombol mode awal terlihat jelas lebih dulu,
                        // lalu bertransisi halus ke warna latar mode TUJUAN bersamaan dengan gesernya tombol 3D
                        const startCanvasBg = toDark ? '#F4F6FB' : '#0B1120';
                        const endCanvasBg = toDark ? '#0B1120' : '#F4F6FB';

                        veil.style.transition = 'none';
                        veil.style.opacity = '1';
                        veil.style.backdropFilter = 'none';
                        veil.style.background = startCanvasBg;
                        veil.style.clipPath = `circle(0px at ${cx}px ${cy}px)`;

                        const widget = createIllustratedThemeSwitch(toDark, labelText, {
                            startCompleted: false,
                            isEn: isEnLocale,
                            originX: cx,
                            originY: cy
                        });
                        veil.replaceChildren(widget.root);

                        void veil.offsetWidth;
                        veil.style.transition = 'clip-path 360ms cubic-bezier(0.22, 1, 0.36, 1), background-color 540ms cubic-bezier(0.22, 1, 0.36, 1)';
                        veil.style.clipPath = `circle(${maxR}px at ${cx}px ${cy}px)`;

                        requestAnimationFrame(() => {
                            widget.play(() => {
                                veil.style.background = endCanvasBg;
                            });
                        });

                        await Promise.all([
                            saveReq,
                            new Promise((resolve) => setTimeout(resolve, 1180))
                        ]);
                    } else {
                        const isDarkNow = document.documentElement.classList.contains('dark');
                        if (mainEl) {
                            mainEl.style.transition = 'opacity 220ms ease, transform 240ms cubic-bezier(0.22,1,0.36,1)';
                            mainEl.style.opacity = '0.45';
                            mainEl.style.transform = 'translateY(-4px)';
                        }

                        veil.style.transition = 'opacity 240ms cubic-bezier(0.22, 1, 0.36, 1)';
                        veil.style.clipPath = 'none';
                        veil.style.background = isDarkNow ? 'rgba(11, 17, 32, 0.55)' : 'rgba(255, 255, 255, 0.68)';
                        veil.style.backdropFilter = 'blur(6px)';

                        const pill = document.createElement('div');
                        pill.style.cssText = 'display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:999px;font-weight:800;font-size:12px;letter-spacing:0.06em;transform:translateY(6px) scale(0.94);opacity:0;transition:transform 240ms cubic-bezier(0.22,1,0.36,1),opacity 200ms ease;'
                            + (isDarkNow
                                ? 'background:#0F172A;color:#F8FAFC;border:1px solid #334155;'
                                : 'background:#0F172A;color:#C8F34A;box-shadow:0 10px 28px rgba(15,23,42,0.14);');
                        pill.textContent = value === 'en' ? 'ID  →  EN' : 'EN  →  ID';
                        veil.replaceChildren(pill);

                        void veil.offsetWidth;
                        veil.style.opacity = '1';
                        requestAnimationFrame(() => {
                            pill.style.transform = 'translateY(0) scale(1)';
                            pill.style.opacity = '1';
                        });

                        await Promise.all([
                            saveReq,
                            new Promise((resolve) => setTimeout(resolve, 260))
                        ]);
                    }

                    window.location.replace(window.location.href);
                });
            });
        })();
    </script>

</body>

</html>