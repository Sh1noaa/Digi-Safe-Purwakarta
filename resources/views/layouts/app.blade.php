<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth overflow-x-hidden {{ ($currentTheme ?? 'light') === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Digi-Safe Purwakarta — Platform edukasi keamanan digital masyarakat Purwakarta.">
    <title>@yield('title', 'Digi-Safe Purwakarta — Platform Edukasi Keamanan Digital')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="{{ ($currentTheme ?? 'light') === 'dark' ? 'bg-[#0B1120] text-[#F1F5F9]' : 'bg-white text-ink' }} antialiased selection:bg-lime selection:text-ink overflow-x-hidden transition-colors duration-300">

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
        @scroll.window="scrolled = {{ $forceSolidNav ? 'true' : 'window.scrollY > 50' }}" :class="scrolled
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

                <!-- area kanan profil dan tombol gabung -->
                <div class="hidden lg:flex items-center">
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

                            <!-- dropdown profil user -->
                            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-2 scale-95" style="display: none;"
                                class="absolute right-0 top-full mt-2.5 w-64 rounded-[20px] bg-white text-ink shadow-neutral border border-gray-100 p-2 z-50">
                                <div class="px-3.5 py-2.5 border-b border-gray-100">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs font-bold text-ink truncate">
                                            {{ Auth::user()->nama_lengkap ?: Auth::user()->username }}
                                        </p>
                                        @if (Auth::user()->isOwner())
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-2 py-0.5 text-[9px] font-extrabold shrink-0">
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
                                    <p class="text-[11px] text-muted truncate mt-0.5">
                                        {{ Auth::user()->email }}
                                    </p>
                                </div>

                                <div class="py-1">
                                    @if (Auth::user()->hasAdminAccess())
                                        <a href="{{ route('admin.dashboard') }}"
                                            class="flex items-center justify-between rounded-xl px-3.5 py-2 text-xs font-semibold {{ $isAdminPage ? 'bg-sky/10 text-sky' : 'text-ink hover:bg-gray-50' }} transition-colors">
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
                                        class="flex items-center gap-2.5 rounded-xl px-3.5 py-2 text-xs font-semibold {{ $isProfilePage ? 'bg-sky/10 text-sky' : 'text-ink hover:bg-gray-50' }} transition-colors">
                                        <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                        <span>{{ __('ui.nav.my_profile') }}</span>
                                    </a>

                                    <a href="{{ route('minigame.index') }}"
                                        class="flex items-center gap-2.5 rounded-xl px-3.5 py-2 text-xs font-semibold text-ink hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 20V10" />
                                            <path d="M18 20V4" />
                                            <path d="M6 20v-4" />
                                        </svg>
                                        <span>{{ __('ui.nav.my_stats') }}</span>
                                    </a>
                                </div>

                                <div class="pt-1 border-t border-gray-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center gap-2.5 rounded-xl px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors cursor-pointer">
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

                <!-- tombol hamburger mobile -->
                <button type="button" @click="mobileOpen = !mobileOpen"
                    :class="scrolled ? '{{ $isDark ? 'text-white hover:bg-slate-800' : 'text-ink hover:bg-gray-100' }}' : 'text-white hover:bg-white/10'"
                    class="lg:hidden shrink-0 inline-flex items-center justify-center p-2 mr-0.5 sm:mr-0 rounded-lg transition-colors focus:outline-none"
                    aria-label="Buka Menu">
                    <svg x-show="!mobileOpen" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round">
                        <path d="M4 7h16M4 12h16M4 17h12" />
                    </svg>
                    <svg x-show="mobileOpen" style="display: none;" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>

            <!-- menu mobile (Urutan sama: Beranda, Edukasi, Minigame, Forum, Testimoni, Tentang) -->
            <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                style="display: none;"
                class="lg:hidden mt-3 rounded-2xl bg-white text-ink shadow-neutral border border-gray-100 p-5 space-y-2.5">
                <a href="{{ route('beranda') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isBeranda ? 'font-bold text-sky' : 'font-semibold text-ink/80' }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.beranda') }}</span>
                    @if ($isBeranda)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('edukasi') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isEdukasi ? 'font-bold text-sky' : 'font-semibold text-ink/80' }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.edukasi') }}</span>
                    @if ($isEdukasi)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('minigame.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isMinigame ? 'font-bold text-sky' : 'font-semibold text-ink/80' }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.minigame') }}</span>
                    @if ($isMinigame)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('forum.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isForum ? 'font-bold text-sky' : 'font-semibold text-ink/80' }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.forum') }}</span>
                    @if ($isForum)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('testimoni.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isTestimoni ? 'font-bold text-sky' : 'font-semibold text-ink/80' }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.testimoni') }}</span>
                    @if ($isTestimoni)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>
                <a href="{{ route('tentang.index') }}"
                    class="flex items-center justify-between py-2 text-xs {{ $isTentang ? 'font-bold text-sky' : 'font-semibold text-ink/80' }} uppercase tracking-[0.18em]">
                    <span>{{ __('ui.nav.tentang') }}</span>
                    @if ($isTentang)
                        <span class="px-2 py-0.5 rounded-full bg-lime text-ink text-[10px]">{{ __('ui.nav.active') }}</span>
                    @endif
                </a>

                <div class="pt-3 border-t border-gray-100">
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
                                    <p class="text-xs font-bold text-ink truncate">
                                        {{ Auth::user()->nama_lengkap ?: Auth::user()->username }}
                                    </p>
                                    @if (Auth::user()->isOwner())
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-2 py-0.5 text-[9px] font-extrabold shrink-0">
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
                                <p class="text-[11px] text-muted truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            @if (Auth::user()->hasAdminAccess())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="block rounded-xl px-3 py-2 text-xs font-semibold text-sky bg-sky/5 hover:bg-sky/10">{{ __('ui.nav.admin_panel') }}</a>
                            @endif
                            <a href="{{ route('profile.edit') }}"
                                class="block rounded-xl px-3 py-2 text-xs font-semibold text-ink hover:bg-gray-50">{{ __('ui.nav.my_profile') }}</a>
                            <a href="{{ route('minigame.index') }}"
                                class="block rounded-xl px-3 py-2 text-xs font-semibold text-ink hover:bg-gray-50">{{ __('ui.nav.my_stats') }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left rounded-xl px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">
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

    <!-- footer (Urutan seragam: Beranda, Edukasi, Minigame, Forum, Testimoni, Tentang) -->
    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8 pb-8 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <span
                        class="w-11 h-10 rounded-xl bg-white border border-gray-100 shadow-xs flex items-center justify-center p-2.5">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe Purwakarta"
                            class="w-full h-full object-contain">
                    </span>
                    <div>
                        <span class="font-heading font-bold text-base text-ink block leading-none">Digi-Safe
                            Purwakarta</span>
                        <span class="text-xs text-muted mt-1 block">{{ __('ui.nav.footer_tagline') }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6 text-xs font-medium text-muted">
                    <a href="{{ route('beranda') }}"
                        class="{{ $isBeranda ? 'text-ink font-semibold' : 'hover:text-ink transition-colors' }}">{{ ucfirst(strtolower(__('ui.nav.beranda'))) }}</a>
                    <a href="{{ route('edukasi') }}"
                        class="{{ $isEdukasi ? 'text-ink font-semibold' : 'hover:text-ink transition-colors' }}">{{ ucfirst(strtolower(__('ui.nav.edukasi'))) }}</a>
                    <a href="{{ route('minigame.index') }}"
                        class="{{ $isMinigame ? 'text-ink font-semibold' : 'hover:text-ink transition-colors' }}">{{ ucfirst(strtolower(__('ui.nav.minigame'))) }}</a>
                    <a href="{{ route('forum.index') }}"
                        class="{{ $isForum ? 'text-ink font-semibold' : 'hover:text-ink transition-colors' }}">{{ ucfirst(strtolower(__('ui.nav.forum'))) }}</a>
                    <a href="{{ route('testimoni.index') }}"
                        class="{{ $isTestimoni ? 'text-ink font-semibold' : 'hover:text-ink transition-colors' }}">{{ ucfirst(strtolower(__('ui.nav.testimoni'))) }}</a>
                    <a href="{{ route('tentang.index') }}"
                        class="{{ $isTentang ? 'text-ink font-semibold' : 'hover:text-ink transition-colors' }}">{{ ucfirst(strtolower(__('ui.nav.tentang'))) }}</a>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted">
                <p>&copy; {{ date('Y') }} Digi-Safe Purwakarta &bull; Razief Afkar Rusli. {{ __('ui.nav.footer_rights') }}</p>

                <div class="flex items-center gap-3">
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer"
                        aria-label="Instagram Digi-Safe"
                        class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-muted hover:text-ink hover:border-ink transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                        </svg>
                    </a>
                    <a href="https://x.com" target="_blank" rel="noopener noreferrer" aria-label="X Digi-Safe"
                        class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-muted hover:text-ink hover:border-ink transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                            <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
                        </svg>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer"
                        aria-label="YouTube Digi-Safe"
                        class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-muted hover:text-ink hover:border-ink transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z" />
                            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <x-chat-widget />

</body>

</html>