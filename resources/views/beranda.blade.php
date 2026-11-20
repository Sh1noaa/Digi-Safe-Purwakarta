@extends('layouts.app')

@section('title', 'Digi-Safe Purwakarta - Membangun Masa Depan Digital yang Aman dan Sadar')

@section('content')

    @php
        $isDarkHome = ($currentTheme ?? 'light') === 'dark';
    @endphp

    <!-- hero background langit (siang: awan cerah, malam: langit berbintang) -->
    <section id="beranda" class="relative pt-3 sm:pt-4 px-2 sm:px-4">
        <div
            class="hero-sky-frame relative rounded-[20px] sm:rounded-[28px] overflow-hidden pt-28 sm:pt-32 pb-14 sm:pb-16 px-4 sm:px-8 {{ $isDarkHome ? 'bg-[#040B22] border border-sky/25 shadow-[0_24px_60px_rgba(2,6,23,0.65)]' : 'bg-[#105EB2]' }}"
        >
            <img
                src="{{ $isDarkHome ? asset('images/nightsky.jpg') : asset('images/bluesky.jpeg') }}"
                alt=""
                aria-hidden="true"
                width="1600"
                height="900"
                fetchpriority="high"
                decoding="async"
                class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none select-none"
            >
            <div
                class="hero-sky-scrim absolute inset-0 pointer-events-none"
            ></div>

            <!-- animasi kawanan burung berterbangan di langit beranda -->
            <div class="bird-flock" aria-hidden="true">
                <svg width="170" height="60" viewBox="0 0 170 60" fill="none">
                    <g class="bird-wing-1">
                        <path d="M98 18 C104 10, 112 10, 117 17 C122 10, 130 10, 136 18" stroke="rgba(255,255,255,0.85)" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <g class="bird-wing-2">
                        <path d="M52 10 C56 4, 62 4, 66 9 C70 4, 76 4, 80 10" stroke="rgba(255,255,255,0.72)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <g class="bird-wing-3">
                        <path d="M14 28 C18 23, 23 23, 26 27 C29 23, 34 23, 38 28" stroke="rgba(255,255,255,0.62)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
            </div>

            <div class="bird-flock-2" aria-hidden="true">
                <svg width="135" height="48" viewBox="0 0 135 48" fill="none">
                    <g class="bird-wing-2">
                        <path d="M84 18 C89 12, 95 12, 99 17 C103 12, 109 12, 114 18" stroke="rgba(255,255,255,0.75)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <g class="bird-wing-1">
                        <path d="M40 28 C44 23, 49 23, 52 27 C55 23, 60 23, 64 28" stroke="rgba(255,255,255,0.6)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <g class="bird-wing-3">
                        <path d="M8 14 C11 10, 15 10, 18 13 C21 10, 25 10, 28 14" stroke="rgba(255,255,255,0.55)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
            </div>

            <div class="bird-flock-3 hidden sm:block" aria-hidden="true">
                <svg width="120" height="44" viewBox="0 0 120 44" fill="none">
                    <g class="bird-wing-1">
                        <path d="M74 16 C78 11, 83 11, 86 15 C89 11, 94 11, 98 16" stroke="rgba(255,255,255,0.7)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <g class="bird-wing-3">
                        <path d="M22 24 C25 20, 29 20, 32 23 C35 20, 39 20, 42 24" stroke="rgba(255,255,255,0.58)" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
            </div>

            <div class="bird-flock-4 hidden md:block" aria-hidden="true">
                <svg width="140" height="50" viewBox="0 0 140 50" fill="none">
                    <g class="bird-wing-3">
                        <path d="M90 16 C94 11, 100 11, 103 15 C106 11, 112 11, 116 16" stroke="rgba(255,255,255,0.65)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <g class="bird-wing-2">
                        <path d="M36 28 C40 24, 44 24, 47 27 C50 24, 54 24, 58 28" stroke="rgba(255,255,255,0.55)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
            </div>

            <div class="bird-flock-5 hidden md:block" aria-hidden="true">
                <svg width="110" height="40" viewBox="0 0 110 40" fill="none">
                    <g class="bird-wing-2">
                        <path d="M60 18 C64 13, 69 13, 72 17 C75 13, 80 13, 84 18" stroke="rgba(255,255,255,0.62)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <g class="bird-wing-1">
                        <path d="M16 12 C19 8, 23 8, 26 11 C29 8, 33 8, 36 12" stroke="rgba(255,255,255,0.5)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
            </div>

            <div class="relative z-10 max-w-6xl mx-auto text-center">
                @auth
                    <div class="hero-auth-badge reveal mb-5 inline-flex items-center gap-2.5 rounded-full bg-white/95 border border-white/25 px-4 py-1.5 shadow-sm text-xs text-ink">
                        <span class="font-medium text-muted">{{ __('ui.nav.logged_in_as') }}</span>
                        <span class="font-heading font-bold text-ink">{{ Auth::user()->nama_lengkap ?: Auth::user()->username }}</span>
                        @if (Auth::user()->isOwner())
                            <span class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-2.5 py-0.5 text-[10px] font-extrabold">
                                <svg class="w-3 h-3 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                </svg>
                                <span>Owner</span>
                            </span>
                        @elseif (Auth::user()->isAdmin())
                            <span class="rounded-full bg-lime text-ink px-2.5 py-0.5 text-[10px] font-extrabold">
                                Admin
                            </span>
                        @endif
                    </div>
                @endauth

                <h1 class="reveal font-heading font-extrabold text-white text-3xl sm:text-5xl lg:text-[56px] leading-[1.1] tracking-[-0.02em] max-w-3xl mx-auto">
                    {!! str_replace('Digital', '<span class="inline-block bg-lime text-ink px-2.5 sm:px-3.5 py-0.5 leading-tight rounded-md mx-0.5">Digital</span>', e(__('ui.home.hero_title'))) !!}
                </h1>

                <p class="reveal reveal-delay-1 mt-5 text-white/95 text-sm sm:text-base max-w-xl mx-auto leading-relaxed font-normal">
                    {{ __('ui.home.hero_subtitle') }}
                </p>

                <div class="reveal reveal-delay-2 mt-8 flex flex-col items-center gap-3">
                    <div class="flex flex-wrap items-center justify-center gap-3.5">
                        <a
                            href="https://www.mediafire.com/file/267zvidpwo13p4n/digi-safe-purwakarta.apk/file"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="hero-secondary-cta inline-flex items-center justify-center gap-2 rounded-full border border-white/35 bg-ink/25 backdrop-blur-xs text-white text-xs font-semibold uppercase tracking-[0.14em] px-6 py-3.5 hover:bg-ink/40 hover:-translate-y-0.5 transition-all duration-300"
                        >
                            <svg class="w-4 h-4 text-lime" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <span>{{ __('ui.home.download_apk') }}</span>
                        </a>

                        @if (Auth::check() && Auth::user()->hasAdminAccess())
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="inline-flex items-center gap-3 rounded-full bg-lime text-ink text-xs font-bold uppercase tracking-[0.14em] pl-6 pr-2 py-2 shadow-neutral hover:-translate-y-0.5 transition-all duration-300"
                            >
                                <span>{{ __('ui.home.open_admin') }}</span>
                                <span class="w-8 h-8 rounded-full bg-ink text-lime flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="7" y1="17" x2="17" y2="7" />
                                        <polyline points="7 7 17 7 17 17" />
                                    </svg>
                                </span>
                            </a>
                        @else
                            <a
                                href="{{ url('/register') }}"
                                class="inline-flex items-center gap-3 rounded-full bg-lime text-ink text-xs font-bold uppercase tracking-[0.14em] pl-6 pr-2 py-2 shadow-neutral hover:-translate-y-0.5 transition-all duration-300"
                            >
                                <span>{{ __('ui.home.start_now') }}</span>
                                <span class="w-8 h-8 rounded-full bg-ink text-white flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="7" y1="17" x2="17" y2="7" />
                                        <polyline points="7 7 17 7 17 17" />
                                    </svg>
                                </span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 9 kartu 3d melingkar (diam saat tidak di-drag, berputar melingkar 360° saat di-drag kanan/kiri, khusus desktop) -->
                <div
                    x-data="heroRingCarousel()"
                    @pointerdown="handlePointerDown($event)"
                    @dragstart.prevent
                    :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
                    style="touch-action: pan-y;"
                    class="hidden lg:block reveal reveal-delay-3 mt-10 sm:mt-12 hero-carousel-stage select-none"
                >
                    <div x-ref="stage" class="relative w-full max-w-6xl mx-auto h-[290px] overflow-visible">

                        @for ($heroLoop = 0; $heroLoop < 1; $heroLoop++)
                        <!-- Kartu 1: Privasi & Sandi -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-glass-edge w-[158px] h-[214px] rounded-[16px] p-3.5 flex flex-col justify-center gap-6 shadow-lg">
                                <div class="inline-flex items-center justify-between rounded-full bg-white px-2.5 py-1.5 shadow-xs">
                                    <span class="w-3.5 h-3.5 rounded-full bg-sky flex items-center justify-center text-white">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                    </span>
                                    <span class="text-[9.5px] font-bold text-ink">{{ __('ui.home.card_privacy') }}</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                                </div>

                                <div class="inline-flex items-center justify-between rounded-full bg-white px-2.5 py-1.5 shadow-xs">
                                    <span class="w-3.5 h-3.5 rounded-full bg-sky flex items-center justify-center text-white">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                    </span>
                                    <span class="text-[9.5px] font-bold text-ink">{{ __('ui.home.card_password') }}</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Kartu 2: Skor Kuis 89% +12% -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-surface-card bg-white w-[178px] h-[220px] rounded-[18px] p-3 text-left text-ink shadow-neutral border border-white flex flex-col justify-between">
                                <div class="rounded-[11px] bg-ink text-white px-3 py-2.5 flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] font-semibold block leading-none">{{ __('ui.home.card_quiz_score') }}</span>
                                        <span class="text-[8px] text-white/75 block mt-1">{{ __('ui.home.card_citizen_avg') }}</span>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-white/85" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                        <polyline points="17 6 23 6 23 12" />
                                    </svg>
                                </div>

                                <div class="px-1 my-1">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="font-heading font-bold text-[26px] text-ink leading-none tracking-tight">89%</span>
                                        <span class="text-[9px] font-bold text-emerald-500">+12%</span>
                                    </div>
                                    <span class="text-[8px] text-muted block mt-1">{{ __('ui.home.card_cyber_understanding') }}</span>
                                </div>

                                <div class="space-y-1.5 overflow-hidden">
                                    <div class="flex items-center gap-1 -mx-1">
                                        <span class="hero-tag-chip text-[8px] font-medium text-muted border border-gray-200 rounded-full px-2 py-0.5 whitespace-nowrap">{{ __('ui.home.card_tag_alert') }}</span>
                                        <span class="hero-tag-chip text-[8px] font-semibold text-ink border border-gray-200 rounded-full px-2 py-0.5 whitespace-nowrap">{{ __('ui.home.card_tag_strategy') }}</span>
                                        <span class="hero-tag-chip text-[8px] font-semibold text-ink border border-gray-200 rounded-full px-2 py-0.5 whitespace-nowrap">{{ __('ui.home.card_tag_focus') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="hero-tag-chip text-[8px] font-semibold text-ink border border-gray-200 rounded-full px-2 py-0.5 whitespace-nowrap">{{ __('ui.home.card_tag_privacy') }}</span>
                                        <span class="hero-tag-chip text-[8px] font-semibold text-ink border border-gray-200 rounded-full px-2 py-0.5 whitespace-nowrap">{{ __('ui.home.card_tag_antihoax') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kartu 3: Modul Phishing 520k+ -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-surface-soft bg-[#F3F4F6] w-[184px] h-[222px] rounded-[18px] p-4 text-left text-ink shadow-neutral border border-white flex flex-col justify-between overflow-hidden">
                                <div class="space-y-2 -mx-3 pt-1">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="hero-soft-pill text-[10px] font-semibold text-ink bg-white rounded-full px-3 py-1 shadow-2xs whitespace-nowrap">{{ __('ui.home.card_tag_edu') }}</span>
                                        <span class="hero-soft-pill text-[10px] font-semibold text-ink bg-white rounded-full px-3 py-1 shadow-2xs whitespace-nowrap">{{ __('ui.home.card_tag_strategic') }}</span>
                                        <span class="hero-soft-pill text-[10px] font-semibold text-muted bg-white rounded-full px-3 py-1 shadow-2xs whitespace-nowrap">{{ __('ui.home.card_tag_cyber') }}</span>
                                    </div>
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="hero-soft-pill text-[10px] font-semibold text-ink bg-white rounded-full px-3 py-1 shadow-2xs whitespace-nowrap">{{ __('ui.home.card_tag_aware') }}</span>
                                        <span class="hero-soft-pill text-[10px] font-semibold text-ink bg-white rounded-full px-3 py-1 shadow-2xs whitespace-nowrap">{{ __('ui.home.card_tag_safe_factor') }}</span>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <span class="text-[11px] font-medium text-ink/75 block">{{ __('ui.home.card_phishing_module') }}</span>
                                    <span class="font-heading font-extrabold text-[34px] text-ink leading-none tracking-tight mt-1.5 block">
                                        520k+
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kartu 4 (posisi tengah awal): Data Latihan -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-glass-blue w-[186px] h-[224px] rounded-[18px] p-5 text-center text-white flex flex-col items-center justify-center">
                                <div class="hero-glass-plus w-9 h-9 rounded-full bg-white text-sky flex items-center justify-center shadow-sm mb-3">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                </div>
                                <h3 class="font-heading font-bold text-[19px] text-white tracking-tight leading-tight">
                                    {{ __('ui.home.card_training_data') }}
                                </h3>
                                <p class="text-[10px] text-white/90 mt-1 font-medium">
                                    {{ __('ui.home.card_real_sim') }}
                                </p>
                            </div>
                        </div>

                        <!-- Kartu 5: Slogan Literasi -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-slogan-card bg-[#0A0A0A] text-white w-[180px] h-[220px] rounded-[18px] p-4 text-left shadow-neutral border-[1.5px] border-white/85 flex items-center">
                                <p class="font-heading font-semibold text-[15px] leading-[1.35] tracking-tight">
                                    <span class="text-white">{{ __('ui.home.card_slogan_1') }}</span>
                                    <span class="inline-flex items-center justify-center align-middle w-4 h-4 rounded-full bg-lime text-ink mx-0.5">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="4" />
                                        </svg>
                                    </span>
                                    <span class="text-slate-300">{{ __('ui.home.card_slogan_2') }}</span>
                                    <span class="text-white">{{ __('ui.home.card_slogan_3') }}</span>
                                    <span class="text-slate-300">{{ __('ui.home.card_slogan_4') }}</span>
                                    <span class="text-white">{{ __('ui.home.card_slogan_5') }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Kartu 6: Indeks Literasi Siber -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-surface-card bg-white w-[170px] h-[216px] rounded-[18px] p-3.5 text-left text-ink shadow-neutral border border-white flex flex-col justify-between overflow-hidden">
                                <div>
                                    <span class="font-heading font-bold text-[11px] text-ink block leading-tight">
                                        {!! nl2br(e(__('ui.home.card_index_title'))) !!}
                                    </span>
                                </div>

                                <div class="mt-auto">
                                    <svg class="w-full h-24" viewBox="0 0 120 80" fill="none">
                                        <defs>
                                            <linearGradient id="skyChartGrad-{{ $heroLoop }}" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.55" />
                                                <stop offset="100%" stop-color="#38BDF8" stop-opacity="0.08" />
                                            </linearGradient>
                                        </defs>
                                        <line class="hero-chart-grid" x1="20" y1="10" x2="20" y2="68" stroke="#E5E7EB" stroke-width="1" stroke-dasharray="2 2" />
                                        <line class="hero-chart-grid" x1="45" y1="10" x2="45" y2="68" stroke="#E5E7EB" stroke-width="1" stroke-dasharray="2 2" />
                                        <line class="hero-chart-grid" x1="70" y1="10" x2="70" y2="68" stroke="#E5E7EB" stroke-width="1" stroke-dasharray="2 2" />
                                        <line class="hero-chart-grid" x1="95" y1="10" x2="95" y2="68" stroke="#E5E7EB" stroke-width="1" stroke-dasharray="2 2" />

                                        <path d="M4 60 L24 44 L44 50 L68 30 L90 36 L116 18 L116 68 L4 68 Z" fill="url(#skyChartGrad-{{ $heroLoop }})" />
                                        <path class="hero-chart-line" d="M4 60 L24 44 L44 50 L68 30 L90 36 L116 18" stroke="#1E88E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <div class="flex items-center justify-between text-[7px] text-gray-400 font-semibold pt-1 border-t border-gray-100">
                                        @foreach (__('ui.home.months') as $monthLabel)
                                            <span>{{ $monthLabel }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kartu 7 (Variasi Baru): Proteksi Akun & Checklist Harian -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-surface-card bg-white w-[180px] h-[220px] rounded-[18px] p-3.5 text-left text-ink shadow-neutral border border-white flex flex-col justify-between">
                                <div class="rounded-[11px] bg-sky text-white px-3 py-2 flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] font-heading font-bold block leading-none">{{ __('ui.home.card_shield_title') }}</span>
                                        <span class="text-[8px] text-white/85 block mt-1">{{ __('ui.home.card_shield_sub') }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-lime shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        <polyline points="9 12 11 14 15 10" />
                                    </svg>
                                </div>

                                <div class="space-y-1.5 my-1">
                                    <div class="flex items-center gap-2 text-[9.5px] font-semibold text-ink">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12" /></svg>
                                        </span>
                                        <span class="truncate">{{ __('ui.home.card_shield_item_1') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[9.5px] font-semibold text-ink">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12" /></svg>
                                        </span>
                                        <span class="truncate">{{ __('ui.home.card_shield_item_2') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[9.5px] font-semibold text-ink">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12" /></svg>
                                        </span>
                                        <span class="truncate">{{ __('ui.home.card_shield_item_3') }}</span>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-[9px] font-bold text-ink">
                                    <span>{{ __('ui.home.card_shield_status') }}</span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Kartu 8 (Variasi Baru): Arena Minigame Neon Lime -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-lime-card bg-lime text-ink w-[182px] h-[222px] rounded-[18px] p-4 text-left shadow-neutral border border-white/80 flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-heading font-extrabold uppercase tracking-wider text-ink/80">{{ __('ui.home.card_arena_badge') }}</span>
                                    <span class="w-7 h-7 rounded-lg bg-ink text-lime flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="5 3 19 12 5 21 5 3" />
                                        </svg>
                                    </span>
                                </div>

                                <div>
                                    <span class="font-heading font-extrabold text-[30px] text-ink leading-none tracking-tight block">
                                        {{ __('ui.home.card_arena_title') }}
                                    </span>
                                    <span class="text-[10px] font-bold text-ink/75 block mt-1">
                                        {{ __('ui.home.card_arena_sub') }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-1">
                                    <span class="rounded-full bg-ink text-white text-[8.5px] font-bold px-2.5 py-0.5">Phishing</span>
                                    <span class="rounded-full bg-ink text-white text-[8.5px] font-bold px-2.5 py-0.5">Defender</span>
                                    <span class="rounded-full bg-white text-ink text-[8.5px] font-bold px-2.5 py-0.5">Link Trap</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kartu 9 (Variasi Baru): Forum Warga Siber -->
                        <div data-hero-ring-card class="hero-ring-card" @if ($heroLoop === 1) aria-hidden="true" @endif>
                            <div class="hero-surface-soft bg-[#F3F4F6] w-[182px] h-[220px] rounded-[18px] p-4 text-left text-ink shadow-neutral border border-white flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center -space-x-1.5">
                                        <span class="w-6 h-6 rounded-full bg-sky text-white text-[9px] font-extrabold flex items-center justify-center ring-2 ring-white">R</span>
                                        <span class="w-6 h-6 rounded-full bg-ink text-lime text-[9px] font-extrabold flex items-center justify-center ring-2 ring-white">A</span>
                                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[9px] font-extrabold flex items-center justify-center ring-2 ring-white">S</span>
                                    </div>
                                    <span class="hero-soft-pill text-[9px] font-bold bg-white text-ink rounded-full px-2.5 py-0.5 shadow-2xs">
                                        {{ __('ui.home.card_forum_title') }}
                                    </span>
                                </div>

                                <p class="font-heading font-bold text-[12px] text-ink leading-snug">
                                    {{ __('ui.home.card_forum_quote') }}
                                </p>

                                <div class="flex items-center justify-between pt-2 border-t border-gray-200/80">
                                    <span class="rounded-full bg-sky/15 text-sky text-[8.5px] font-extrabold px-2 py-0.5">
                                        {{ __('ui.home.card_forum_helpful') }}
                                    </span>
                                    <span class="text-[10px] font-heading font-extrabold text-emerald-600">+48</span>
                                </div>
                            </div>
                        </div>
                        @endfor

                    </div>
                </div>

                <!-- rating pengguna -->
                <div class="reveal mt-8 flex flex-col items-center justify-center gap-1.5">
                    <p class="text-xs font-medium text-white/95 tracking-wide">
                        {{ __('ui.home.trusted_users') }}
                    </p>
                    <div class="flex items-center gap-1" aria-label="Rating 5 dari 5 bintang">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" />
                            </svg>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- tentang kami -->
    <section id="tentang-kami" class="py-20 sm:py-28 bg-white overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="reveal text-center max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                    <span class="text-sky">&bull;</span> {{ __('ui.home.about_badge') }}
                </span>

                <h2 class="mt-4 font-heading font-bold text-2xl sm:text-4xl lg:text-[42px] leading-[1.2] tracking-[-0.02em] text-ink">
                    {{ __('ui.home.about_title_1') }}
                    <span class="inline-flex items-center justify-center align-middle w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-sky text-white mx-1 shadow-xs">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            <circle cx="12" cy="11" r="2.5" />
                        </svg>
                    </span>
                    {{ __('ui.home.about_title_2') }}
                    <span class="text-muted font-bold">
                        &amp;
                        <span class="inline-flex items-center justify-center align-middle w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-lime text-ink mx-1 shadow-xs">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18h6" />
                                <path d="M10 22h4" />
                                <path d="M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z" />
                            </svg>
                        </span>
                        {{ __('ui.home.about_title_3') }}
                    </span>
                </h2>
            </div>

            <!-- kartu scanner penghapus kebiasaan buruk digital (recreated KPI UI) -->
            @php
                $isEnHome = app()->getLocale() === 'en';
                $habitRows = $isEnHome ? [
                    ['bad' => 'Click Phishing Links', 'good' => 'Verify Official Domains', 'offset' => 'sm:pl-4'],
                    ['bad' => 'Reuse Weak Passwords', 'good' => 'Enable 2FA Protection', 'offset' => 'sm:pr-4'],
                    ['bad' => 'Share Secret OTP Codes', 'good' => 'Guard Your OTP Privacy', 'offset' => 'sm:pl-6'],
                    ['bad' => 'Install Unknown APKs', 'good' => 'Audit App Permissions', 'offset' => ''],
                ] : [
                    ['bad' => 'Klik Link Phishing', 'good' => 'Verifikasi Domain Resmi', 'offset' => 'sm:pl-4'],
                    ['bad' => 'Sandi Lemah & Sama', 'good' => 'Aktifkan Autentikasi 2FA', 'offset' => 'sm:pr-4'],
                    ['bad' => 'Bagikan Kode OTP', 'good' => 'Jaga Kerahasiaan OTP', 'offset' => 'sm:pl-6'],
                    ['bad' => 'Unduh APK Asing', 'good' => 'Cek Izin Aplikasi & APK', 'offset' => ''],
                ];
            @endphp

            <div class="reveal reveal-delay-1 mt-10 max-w-4xl mx-auto">
                <div class="relative px-0 sm:px-6 pt-12 sm:pt-14 pb-5 sm:pb-6 overflow-visible">

                    <!-- garis scanner biru vertikal di tengah -->
                    <div class="pointer-events-none absolute inset-y-2 left-1/2 -translate-x-1/2 flex flex-col items-center z-20">
                        <div class="inline-flex items-center gap-1.5 sm:gap-2.5 rounded-full bg-gradient-to-r from-[#1D4ED8] to-[#0EA5E9] text-white text-[11px] sm:text-sm font-heading font-bold px-3 sm:px-4 py-1.5 sm:py-2 shadow-md ring-1 ring-white/20 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-lime animate-spin" style="animation-duration: 7s;" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                                <path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                            <span>{{ $isEnHome ? 'Cyber Hygiene 88%' : 'Kebiasaan Aman 88%' }}</span>
                        </div>
                        <div class="w-[2px] flex-1 scanner-beam-glow"></div>
                    </div>

                    <!-- cahaya partikel di sepanjang garis scanner -->
                    <div class="pointer-events-none absolute inset-y-12 left-1/2 -translate-x-1/2 w-20 sm:w-28 bg-gradient-to-r from-[#38BDF8]/18 via-[#2563EB]/12 to-transparent blur-md z-10"></div>
                    <div class="pointer-events-none absolute inset-y-16 left-1/2 -translate-x-1/2 w-12 sm:w-14 z-20">
                        <span class="scanner-sparkle absolute top-[14%] left-2 w-1.5 h-1.5 rounded-full bg-sky/70"></span>
                        <span class="scanner-sparkle absolute top-[38%] right-2 w-1.5 h-1.5 rounded-full bg-lime" style="animation-delay: 0.7s;"></span>
                        <span class="scanner-sparkle absolute top-[62%] left-3 w-1 h-1 rounded-full bg-[#38BDF8]" style="animation-delay: 1.3s;"></span>
                        <span class="scanner-sparkle absolute top-[82%] right-3 w-1.5 h-1.5 rounded-full bg-sky/60" style="animation-delay: 1.9s;"></span>
                    </div>

                    <!-- deretan kebiasaan buruk terhapus -> kebiasaan aman terverifikasi -->
                    <div class="relative z-10 mt-4 sm:mt-5 space-y-2.5 sm:space-y-4">
                        @foreach ($habitRows as $row)
                            <div class="grid grid-cols-2 items-center gap-3 sm:gap-7 {{ $row['offset'] }}">
                                <!-- sisi kiri: kebiasaan buruk sedang dihapus/terurai -->
                                <div class="flex justify-end pr-2 sm:pr-4 min-w-0">
                                    <span class="habit-dissolve-left habit-bad-pill inline-flex items-center gap-1 sm:gap-1.5 rounded-2xl sm:rounded-full bg-gray-100 border border-gray-200/60 px-2.5 sm:px-4 py-1.5 sm:py-2 text-[10.5px] sm:text-sm font-medium text-muted line-through decoration-rose-400/70 select-none text-right leading-tight max-w-full">
                                        {{ $row['bad'] }}
                                    </span>
                                </div>

                                <!-- sisi kanan: kebiasaan aman terverifikasi -->
                                <div class="flex justify-start pl-2 sm:pl-4 min-w-0">
                                    <span class="habit-good-pill inline-flex items-center gap-1.5 sm:gap-2.5 rounded-2xl sm:rounded-full bg-white border border-gray-100 px-2.5 sm:px-5 py-1.5 sm:py-2 text-[10.5px] sm:text-sm font-semibold text-ink shadow-xs leading-tight max-w-full min-w-0">
                                        <span>{{ $row['good'] }}</span>
                                        <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                            <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="relative z-10 mt-7 text-center text-xs sm:text-sm font-medium text-muted">
                        {{ $isEnHome ? 'Filtering unsafe digital habits into verified cyber awareness' : 'Menyaring kebiasaan siber berisiko menjadi perlindungan digital aktif' }}
                    </p>
                </div>
            </div>

            <!-- grid tentang kami -->
            <div class="mt-14 grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">

                <div class="reveal lg:col-span-5 relative rounded-[20px] bg-sky p-6 overflow-hidden flex flex-col justify-between min-h-[340px] shadow-neutral hover:-translate-y-1 transition-transform duration-300">
                    <svg class="absolute inset-0 w-full h-full object-cover pointer-events-none opacity-35" viewBox="0 0 400 340" fill="none">
                        <circle cx="290" cy="110" r="95" stroke="#FFFFFF" stroke-width="1.5" stroke-dasharray="6 6" />
                        <circle cx="290" cy="110" r="60" fill="rgba(255,255,255,0.12)" />
                        <path d="M265 110 L282 127 L318 91" stroke="#C6F432" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M-20 240 Q90 180 210 230 T420 200" stroke="rgba(255,255,255,0.3)" stroke-width="2" fill="none" />
                    </svg>

                    <div class="relative z-10 flex items-center justify-between">
                        <span class="font-heading font-extrabold text-white text-lg tracking-wider">
                            DIGI&bull;SAFE
                        </span>
                        <span class="w-9 h-9 rounded-xl bg-white text-ink flex items-center justify-center shadow-xs">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10" />
                                <line x1="12" y1="20" x2="12" y2="4" />
                                <line x1="6" y1="20" x2="6" y2="14" />
                            </svg>
                        </span>
                    </div>

                    <div class="relative z-10 mt-20 rounded-[16px] bg-white p-5 shadow-xs">
                        <span class="font-heading font-extrabold text-3xl sm:text-4xl text-ink block tracking-tight">
                            {{ $aboutStats['mitra_count'] }}
                        </span>
                        <p class="text-xs text-muted mt-1.5 leading-relaxed">
                            {{ $aboutStats['mitra_desc'] }}
                        </p>
                    </div>
                </div>

                <div class="reveal reveal-delay-1 lg:col-span-4 rounded-[20px] bg-[#F6F7F9] border border-gray-100 p-7 flex flex-col justify-between min-h-[340px] hover:-translate-y-1 transition-transform duration-300">
                    <div>
                        <span class="text-xs font-medium text-muted block">
                            {{ $aboutStats['komitmen_label'] }}
                        </span>
                        <span class="font-heading font-extrabold text-4xl sm:text-[44px] text-ink tracking-tight mt-2 block">
                            {{ $aboutStats['komitmen_persen'] }}
                        </span>
                    </div>

                    <div class="mt-8">
                        <div class="flex items-center justify-between gap-2 mb-4">
                            <div class="flex items-center -space-x-2">
                                @forelse (($realReviews ?? collect())->take(3) as $revAvatar)
                                    @if (! $revAvatar->is_anonymous && $revAvatar->avatar_tampil)
                                        <img src="{{ $revAvatar->avatar_tampil }}" alt="{{ $revAvatar->nama_tampil }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-white shadow-xs">
                                    @else
                                        <div class="w-9 h-9 rounded-full {{ $revAvatar->is_anonymous ? 'bg-gray-200 text-muted' : ($revAvatar->user?->pastel_class ?: 'bg-sky text-white') }} ring-2 ring-white flex items-center justify-center text-xs font-heading font-extrabold">
                                            {{ $revAvatar->inisial_tampil }}
                                        </div>
                                    @endif
                                @empty
                                    <div class="w-9 h-9 rounded-full bg-sky text-white ring-2 ring-white flex items-center justify-center">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <circle cx="12" cy="8" r="4" />
                                            <path d="M5 20c1.5-4 12.5-4 14 0" />
                                        </svg>
                                    </div>
                                @endforelse
                                <a href="{{ route('testimoni.index') }}" class="w-9 h-9 rounded-full bg-white text-ink ring-2 ring-gray-100 hover:bg-lime transition-colors flex items-center justify-center text-[10px] font-bold">
                                    {{ ($totalReviewsCount ?? 0) > 0 ? $totalReviewsCount . '+' : '0' }}
                                </a>
                            </div>

                            @if (! empty($featuredReview))
                                <div class="flex items-center gap-0.5" aria-label="Rating {{ $featuredReview->rating ?: 5 }} dari 5">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <svg class="w-3.5 h-3.5 {{ $s <= ($featuredReview->rating ?: 5) ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200' }}" viewBox="0 0 24 24">
                                            <path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" />
                                        </svg>
                                    @endfor
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-ink/80 leading-relaxed font-medium line-clamp-3">
                            {{ $aboutStats['komitmen_quote'] }}
                        </p>

                        @if (! empty($featuredReview))
                            <div class="mt-3 flex items-center justify-between gap-2 text-[11px]">
                                <span class="font-heading font-bold text-ink truncate">- {{ $featuredReview->nama_tampil }}</span>
                                <a href="{{ route('testimoni.index') }}" class="font-semibold text-sky hover:underline shrink-0">
                                    {{ app()->getLocale() === 'en' ? 'All reviews' : 'Lihat ulasan' }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="reveal reveal-delay-2 lg:col-span-3 flex flex-col gap-5">
                    <div class="rounded-[20px] bg-lime p-6 flex-1 flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300">
                        <div>
                            <span class="text-xs font-medium text-ink/75 block">
                                {{ $aboutStats['modul_label'] }}
                            </span>
                            <span class="font-heading font-extrabold text-3xl sm:text-4xl text-ink tracking-tight mt-1.5 block">
                                {{ $aboutStats['modul_interaksi'] }}
                            </span>
                        </div>

                        <p class="text-xs text-ink/85 font-medium leading-relaxed mt-6">
                            {{ $aboutStats['modul_desc'] }}
                        </p>
                    </div>

                    <div class="rounded-[18px] bg-ink text-white px-6 py-5 flex items-center justify-between hover:-translate-y-1 transition-transform duration-300">
                        <span class="text-xs font-medium text-white/75">
                            {{ $aboutStats['kecamatan_label'] }}
                        </span>
                        <span class="font-heading font-extrabold text-2xl sm:text-3xl text-white tracking-tight">
                            {{ $aboutStats['kecamatan_count'] }}
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- layanan kami -->
    <section class="py-16 sm:py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="reveal text-center max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                    <span class="text-sky">&bull;</span> {{ __('ui.home.services_badge') }}
                </span>

                <h2 class="mt-4 font-heading font-bold text-2xl sm:text-4xl lg:text-[40px] leading-[1.18] tracking-[-0.02em] text-ink">
                    {{ __('ui.home.services_title') }}
                </h2>

                <p class="mt-4 text-xs sm:text-sm text-muted max-w-md mx-auto leading-relaxed">
                    {{ __('ui.home.services_desc') }}
                </p>

                <div class="mt-7">
                    <a
                        href="{{ route('edukasi') }}"
                        class="inline-flex items-center gap-3 rounded-full bg-ink text-white text-xs font-semibold uppercase tracking-[0.14em] pl-6 pr-2 py-2 shadow-neutral hover:-translate-y-0.5 transition-transform duration-300"
                    >
                        <span>{{ __('ui.home.start_learning') }}</span>
                        <span class="w-8 h-8 rounded-full bg-lime text-ink flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="7" y1="17" x2="17" y2="7" />
                                <polyline points="7 7 17 7 17 17" />
                            </svg>
                        </span>
                    </a>
                </div>
            </div>

            <!-- grid 3 kartu layanan bergaya folder tab Digi-Safe -->
            <div class="reveal reveal-delay-1 mt-14 grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">

                <!-- Kartu 1: Edukasi -->
                <a href="{{ route('edukasi') }}" class="lg:col-span-3 rounded-[24px] p-[3px] service-folder-frame border shadow-neutral hover:-translate-y-1.5 transition-all duration-300 group flex flex-col overflow-hidden">
                    <div class="relative rounded-[20px] service-folder-fill overflow-hidden flex-1 flex flex-col justify-between min-h-[296px]">
                        <!-- header grid halus di balik bahu folder kanan -->
                        <div class="service-white-grid service-folder-grid relative h-24 px-5 pt-4 flex justify-end items-start">
                            <span class="service-folder-grid-label text-[11px] font-heading font-bold text-ink text-right leading-tight">
                                Digi-Safe<br><span class="text-sky text-[10px]">{{ __('ui.home.folder_1_sub') }}</span>
                            </span>
                        </div>

                        <!-- bentuk tab folder naik di sisi kiri atas -->
                        <div class="relative -mt-12 flex-1 flex flex-col justify-between">
                            <div class="flex items-end">
                                <div class="service-folder-fill rounded-tl-[16px] pl-5 pt-4 pr-3 pb-1 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-lime text-ink flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                        </svg>
                                    </span>
                                </div>
                                <svg class="w-7 h-7 service-folder-notch -ml-px shrink-0" viewBox="0 0 28 28" fill="currentColor" aria-hidden="true">
                                    <path d="M0 0 C10 0, 14 28, 28 28 L0 28 Z" />
                                </svg>
                                <div class="flex-1 h-7 service-folder-fill"></div>
                            </div>

                            <div class="service-folder-fill px-5 pt-2 pb-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-heading font-bold text-lg service-folder-title group-hover:text-lime transition-colors">
                                        {{ $services[0]['title'] }}
                                    </h3>
                                    <p class="mt-2 text-xs service-folder-desc leading-relaxed">
                                        {{ $services[0]['description'] }}
                                    </p>
                                </div>

                                <div class="mt-6 pt-4 border-t service-folder-footer flex items-end justify-between">
                                    <div class="leading-none">
                                        <span class="font-heading font-extrabold text-2xl service-folder-num">01</span>
                                        <span class="text-[11px] font-medium service-folder-unit ml-1">{{ __('ui.home.folder_1_unit') }}</span>
                                    </div>
                                    <span class="text-xs font-semibold service-folder-link group-hover:text-lime transition-colors inline-flex items-center gap-1">
                                        <span>{{ __('ui.home.folder_1_cta') }}</span>
                                        <span>&rarr;</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Kartu 2: Minigame -->
                <a href="{{ route('minigame.index') }}" class="lg:col-span-3 rounded-[24px] p-[3px] service-folder-frame border shadow-neutral hover:-translate-y-1.5 transition-all duration-300 group flex flex-col overflow-hidden">
                    <div class="relative rounded-[20px] service-folder-fill overflow-hidden flex-1 flex flex-col justify-between min-h-[296px]">
                        <div class="service-white-grid service-folder-grid relative h-24 px-5 pt-4 flex justify-end items-start">
                            <span class="service-folder-grid-label text-[11px] font-heading font-bold text-ink text-right leading-tight">
                                Digi-Safe<br><span class="text-sky text-[10px]">{{ __('ui.home.folder_2_sub') }}</span>
                            </span>
                        </div>

                        <div class="relative -mt-12 flex-1 flex flex-col justify-between">
                            <div class="flex items-end">
                                <div class="service-folder-fill rounded-tl-[16px] pl-5 pt-4 pr-3 pb-1 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-sky text-white flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9" />
                                            <polyline points="12 7 12 12 15 15" />
                                        </svg>
                                    </span>
                                </div>
                                <svg class="w-7 h-7 service-folder-notch -ml-px shrink-0" viewBox="0 0 28 28" fill="currentColor" aria-hidden="true">
                                    <path d="M0 0 C10 0, 14 28, 28 28 L0 28 Z" />
                                </svg>
                                <div class="flex-1 h-7 service-folder-fill"></div>
                            </div>

                            <div class="service-folder-fill px-5 pt-2 pb-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-heading font-bold text-lg service-folder-title group-hover:text-lime transition-colors">
                                        {{ $services[1]['title'] }}
                                    </h3>
                                    <p class="mt-2 text-xs service-folder-desc leading-relaxed">
                                        {{ $services[1]['description'] }}
                                    </p>
                                </div>

                                <div class="mt-6 pt-4 border-t service-folder-footer flex items-end justify-between">
                                    <div class="leading-none">
                                        <span class="font-heading font-extrabold text-2xl service-folder-num">02</span>
                                        <span class="text-[11px] font-medium service-folder-unit ml-1">{{ __('ui.home.folder_2_unit') }}</span>
                                    </div>
                                    <span class="text-xs font-semibold service-folder-link group-hover:text-lime transition-colors inline-flex items-center gap-1">
                                        <span>{{ __('ui.home.folder_2_cta') }}</span>
                                        <span>&rarr;</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Kartu 3: Forum Komunitas -->
                <a href="{{ route('forum.index') }}" class="lg:col-span-6 rounded-[24px] p-[3px] service-folder-frame border shadow-neutral hover:-translate-y-1.5 transition-all duration-300 group flex flex-col overflow-hidden">
                    <div class="relative rounded-[20px] service-folder-fill overflow-hidden flex-1 flex flex-col justify-between min-h-[296px]">
                        <div class="service-white-grid service-folder-grid relative h-24 px-6 pt-4 flex justify-end items-start">
                            <span class="service-folder-grid-label text-[11px] font-heading font-bold text-ink text-right leading-tight">
                                Digi-Safe Hub<br><span class="text-sky text-[10px]">{{ __('ui.home.folder_3_sub') }}</span>
                            </span>
                        </div>

                        <div class="relative -mt-12 flex-1 flex flex-col justify-between">
                            <div class="flex items-end">
                                <div class="service-folder-fill rounded-tl-[16px] pl-6 pt-4 pr-3 pb-1 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-lime text-ink flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                        </svg>
                                    </span>
                                </div>
                                <svg class="w-8 h-7 service-folder-notch -ml-px shrink-0" viewBox="0 0 32 28" fill="currentColor" aria-hidden="true">
                                    <path d="M0 0 C12 0, 16 28, 32 28 L0 28 Z" />
                                </svg>
                                <div class="flex-1 h-7 service-folder-fill"></div>
                            </div>

                            <div class="service-folder-fill px-6 pt-2 pb-6 flex-1 grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                                <div class="md:col-span-6 flex flex-col justify-between h-full">
                                    <div>
                                        <h3 class="font-heading font-bold text-lg service-folder-title group-hover:text-lime transition-colors">
                                            {{ $services[2]['title'] }}
                                        </h3>
                                        <p class="mt-2 text-xs service-folder-desc leading-relaxed">
                                            {{ $services[2]['description'] }}
                                        </p>
                                    </div>

                                    <div class="mt-6 pt-4 border-t service-folder-footer flex items-end justify-between">
                                        <div class="leading-none">
                                            <span class="font-heading font-extrabold text-2xl service-folder-num">03</span>
                                            <span class="text-[11px] font-medium service-folder-unit ml-1">{{ __('ui.home.folder_3_unit') }}</span>
                                        </div>
                                        <span class="text-xs font-semibold service-folder-link group-hover:text-lime transition-colors inline-flex items-center gap-1">
                                            <span>{{ __('ui.home.folder_3_cta') }}</span>
                                            <span>&rarr;</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="md:col-span-6 rounded-[14px] service-folder-subcard border p-3.5 flex flex-col justify-between relative overflow-hidden">
                                    <svg class="w-full h-36" viewBox="0 0 240 165" fill="none">
                                        <circle cx="190" cy="34" r="38" fill="rgba(30, 136, 229, 0.22)" />
                                        <circle cx="48" cy="130" r="44" fill="rgba(198, 244, 50, 0.18)" />

                                        <rect x="16" y="18" width="146" height="54" rx="10" fill="#1E293B" stroke="rgba(255,255,255,0.16)" stroke-width="1.2" />
                                        <circle cx="38" cy="45" r="11" fill="#1E88E5" />
                                        <path d="M34 45 L37 48 L43 42" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <rect x="58" y="34" width="68" height="6" rx="3" fill="#FFFFFF" />
                                        <rect x="58" y="46" width="88" height="5" rx="2.5" fill="#94A3B8" />

                                        <rect x="64" y="86" width="158" height="62" rx="10" fill="#0F172A" stroke="rgba(198,244,50,0.35)" stroke-width="1.2" />
                                        <circle cx="88" cy="117" r="12" fill="#C6F432" />
                                        <path d="M88 110 V120 M88 124 H88.01" stroke="#0A0A0A" stroke-width="2" stroke-linecap="round" />
                                        <rect x="108" y="104" width="84" height="6" rx="3" fill="#FFFFFF" />
                                        <rect x="108" y="117" width="66" height="5" rx="2.5" fill="#94A3B8" />
                                        <rect x="108" y="129" width="44" height="5" rx="2.5" fill="#C6F432" />
                                    </svg>

                                    <div class="flex items-center justify-between text-[11px] font-semibold service-folder-subcard-title pt-1.5">
                                        <span>{{ __('ui.home.community_active') }}</span>
                                        <span class="inline-flex items-center gap-1 text-emerald-500">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            {{ __('ui.home.verified') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </section>

    <!-- direktori animasi kanan ke kiri seluruh user terdaftar di digi-safe -->
    <section id="komunitas-user" class="py-18 sm:py-24 bg-[#F6F8FB] border-t border-gray-100 overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                        <span class="text-sky">&bull;</span> {{ __('ui.home.users_badge') }}
                    </span>
                    <h2 class="mt-3 font-heading font-extrabold text-2xl sm:text-4xl text-ink leading-tight tracking-tight">
                        {{ __('ui.home.users_title_1') }} <span class="text-sky">Digi-Safe Purwakarta</span>
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm text-muted max-w-xl leading-relaxed">
                        {{ __('ui.home.users_desc', ['count' => $registeredUsers->count()]) }}
                    </p>
                </div>

                <a
                    href="{{ route('users.directory') }}"
                    class="inline-flex items-center gap-3 rounded-full bg-ink hover:bg-sky text-white text-xs font-heading font-bold uppercase tracking-[0.12em] pl-6 pr-2 py-2 shadow-neutral hover:-translate-y-0.5 transition-all shrink-0 self-start md:self-auto"
                >
                    <span>{{ __('ui.home.view_all_users', ['count' => $registeredUsers->count()]) }}</span>
                    <span class="w-8 h-8 rounded-full bg-lime text-ink flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7" />
                            <polyline points="7 7 17 7 17 17" />
                        </svg>
                    </span>
                </a>
            </div>
        </div>

        <!-- Track Marquee Bergerak Nonstop + Dapat Di-drag Kanan/Kiri & Tetap Bisa Diklik -->
        <div
            x-data="memberMarquee()"
            @pointerdown="handlePointerDown($event)"
            @click.capture="handleClickCapture($event)"
            @mouseenter="isHovering = true"
            @mouseleave="isHovering = false"
            @dragstart.prevent
            :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
            style="touch-action: pan-y;"
            class="relative mt-12 overflow-hidden select-none"
        >
            <!-- Fade gradasi kiri & kanan agar transisi masuk/keluar tampak halus -->
            <div class="marquee-fade-left pointer-events-none absolute inset-y-0 left-0 w-12 sm:w-28 z-20"></div>
            <div class="marquee-fade-right pointer-events-none absolute inset-y-0 right-0 w-12 sm:w-28 z-20"></div>

            <div x-ref="track" class="user-marquee-track gap-6 px-3 py-4">
                <!-- Loop 1: seluruh user terdaftar -->
                @foreach ($registeredUsers as $regUser)
                    <div class="w-[290px] sm:w-[310px] shrink-0">
                        <x-user-profile-card :user="$regUser" :followingIds="$followingIds ?? []" :linkToDirectory="true" />
                    </div>
                @endforeach

                <!-- Loop 2 (duplikasi seamless untuk animasi infinite kanan ke kiri) -->
                @foreach ($registeredUsers as $regUserDup)
                    <div class="w-[290px] sm:w-[310px] shrink-0" aria-hidden="true">
                        <x-user-profile-card :user="$regUserDup" :followingIds="$followingIds ?? []" :linkToDirectory="true" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- bagian Q & A interaktif di bagian paling bawah beranda -->
    @php
        $isEnFaq = app()->getLocale() === 'en';
        $faqGroups = $isEnFaq ? [
            'pelajar' => [
                [
                    'q' => 'What is Digi-Safe Purwakarta and how does it help students learn?',
                    'a' => 'Digi-Safe Purwakarta is an interactive cybersecurity literacy platform built to help students and young citizens spot online scams, phishing links, fake giveaways, and malware safely before encountering real threats.',
                ],
                [
                    'q' => 'Are the learning modules and minigames free to access?',
                    'a' => 'Yes! All reading modules in the Education Center and community forums are completely free. Creating a free account unlocks all tiered minigame levels, score tracking, achievement badges, and Purwakarta Leaderboard rankings.',
                ],
                [
                    'q' => 'How is Digi-Safe different from general articles on the internet?',
                    'a' => 'Instead of passive theory, Digi-Safe trains your reflexes using real Indonesian & Purwakarta case simulations (such as fake social aid domains, WhatsApp APK traps, and deceptive banking alerts) with instant feedback for every answer.',
                ],
                [
                    'q' => 'Can I switch the minigame challenges and website to English or Indonesian?',
                    'a' => 'Absolutely! Open your Profile Settings anytime to toggle seamlessly between Bahasa Indonesia and English, as well as Light or Dark mode across the entire site including every quiz level.',
                ],
                [
                    'q' => 'How do I earn Achievement Badges and climb the Leaderboard?',
                    'a' => 'Finish any minigame level with high accuracy and fast response time. Completing levels flawlessly or scoring 60+ unlocks special badges on your profile card and increases your ranking.',
                ],
            ],
            'orangtua' => [
                [
                    'q' => 'How can Digi-Safe help parents protect children online?',
                    'a' => 'Digi-Safe equips parents with practical guides on screen habits, suspicious APK files, social engineering on chat apps, and step-by-step checklists to safeguard family accounts and e-wallets.',
                ],
                [
                    'q' => 'Is my child or family member’s personal information safe on this website?',
                    'a' => 'Yes. Inside Profile Settings, every user can hide their location/bio, hide their email address, hide stats, or switch on Private Mode so only their display name, avatar, and banner are shown publicly.',
                ],
                [
                    'q' => 'What should we do if we receive a suspicious WhatsApp file or bank link?',
                    'a' => 'Never install unknown APK files or enter OTP/PIN codes. Check our Education modules first or post the anonymized situation inside the Community Forum so verified admins and peers can help inspect it.',
                ],
                [
                    'q' => 'Do parents need a technical background to use Digi-Safe?',
                    'a' => 'Not at all. Every guide and minigame explanation uses clear, everyday language designed specifically for families, parents, and beginners.',
                ],
            ],
            'komunitas' => [
                [
                    'q' => 'How can teachers or schools use Digi-Safe in class?',
                    'a' => 'Teachers can assign Education reading modules alongside tiered Minigame levels (Levels 1–5) as hands-on classroom exercises, then review comprehension using the community scoreboard.',
                ],
                [
                    'q' => 'How does the Most Helpful feature in the Community Forum work?',
                    'a' => 'Signed-in members can react once per reply with Upvote (Helpful) or Downvote. Replies with the highest net helpful votes automatically earn the real-time Most Helpful badge.',
                ],
                [
                    'q' => 'Can teachers or administrators monitor learning progress reports?',
                    'a' => 'Authorized Admins and Platform Owners have access to the Admin Panel dashboard, custom Minigame Level management, and instant one-click Excel (.xlsx) participant progress exports.',
                ],
                [
                    'q' => 'How can our school or community in Purwakarta collaborate with Digi-Safe?',
                    'a' => 'Visit the About page to view our Purwakarta contact hub at SMK Negeri 2 Purwakarta or join discussions directly in the Ask Experts topic inside our Community Forum.',
                ],
            ],
        ] : [
            'pelajar' => [
                [
                    'q' => 'Apa itu Digi-Safe Purwakarta dan bagaimana ia membantuku belajar keamanan digital?',
                    'a' => 'Digi-Safe Purwakarta adalah platform edukasi keamanan siber interaktif yang membantu pelajar dan generasi muda mengenali tautan phishing, pesan penipuan, hoaks, serta ancaman malware sebelum mengalaminya langsung.',
                ],
                [
                    'q' => 'Apakah modul edukasi dan seluruh level minigame bisa diakses gratis?',
                    'a' => 'Ya! Seluruh modul bacaan dan forum diskusi gratis diakses. Setelah membuat akun gratis, kamu bisa memainkan seluruh level minigame bertingkat, menyimpan riwayat skor, mengoleksi lencana achievement, dan masuk ke Leaderboard.',
                ],
                [
                    'q' => 'Apa bedanya latihan di Digi-Safe dengan membaca artikel biasa di internet?',
                    'a' => 'Di Digi-Safe kamu tidak hanya membaca teori, tetapi langsung melatih insting melalui simulasi kasus nyata di sekitar warga, mulai dari modus undangan APK, link bansos palsu, hingga jebakan domain tiruan dengan pembahasan instan.',
                ],
                [
                    'q' => 'Apakah soal minigame dan tampilan web bisa diganti ke Bahasa Inggris atau mode gelap?',
                    'a' => 'Tentu bisa! Buka menu Profil Saya lalu klik Pengaturan untuk mengganti bahasa platform (Indonesia / English), mode layar (Terang / Gelap), serta kontrol privasi kapan saja.',
                ],
                [
                    'q' => 'Bagaimana cara membuka Lencana Achievement di kartu profilku?',
                    'a' => 'Selesaikan level minigame dengan akurasi tinggi dan kecepatan respons yang baik. Menyelesaikan misi tanpa salah atau mencapai skor tinggi akan otomatis membuka lencana baru di profilmu.',
                ],
            ],
            'orangtua' => [
                [
                    'q' => 'Bagaimana Digi-Safe membantu orang tua melindungi keluarga di dunia maya?',
                    'a' => 'Digi-Safe menyediakan panduan praktis bagi orang tua untuk memahami bahaya file APK asing, rekayasa sosial di grup chat keluarga, pencurian OTP perbankan, serta kebiasaan digital sehat untuk anak.',
                ],
                [
                    'q' => 'Apakah data pribadi atau alamat rumah kami aman jika mendaftar?',
                    'a' => 'Aman sekali. Di pop-up Pengaturan Profil tersedia opsi untuk menyembunyikan alamat/bio, menyembunyikan email, menyembunyikan statistik skor, hingga Mode Akun Privat yang hanya menampilkan nama, foto profil, dan banner.',
                ],
                [
                    'q' => 'Apa yang harus dilakukan jika keluarga menerima link hadiah atau file mencurigakan?',
                    'a' => 'Jangan pernah klik tautan atau memasang file dari pengirim tak dikenal. Anda dapat menanyakannya di Forum Komunitas Digi-Safe untuk dibantu pengecekan oleh admin dan sesama warga siber Purwakarta.',
                ],
                [
                    'q' => 'Apakah orang tua yang awam teknologi bisa mudah memahami materinya?',
                    'a' => 'Sangat mudah. Bahasa materi dan kuis dirancang ringan tanpa istilah teknis yang rumit sehingga nyaman dipelajari bersama seluruh anggota keluarga.',
                ],
            ],
            'komunitas' => [
                [
                    'q' => 'Bagaimana guru atau sekolah dapat memanfaatkan Digi-Safe untuk kegiatan belajar?',
                    'a' => 'Guru dapat menugaskan siswa membaca kurikulum literasi di halaman Edukasi lalu mempraktikkannya lewat Level 1–5 pada Minigame Phishing Hunter, Cyber Defender, dan Link or Trap.',
                ],
                [
                    'q' => 'Bagaimana sistem penilaian "Most Helpful" di Forum Komunitas bekerja?',
                    'a' => 'Setiap pengguna terdaftar dapat memberikan maksimal satu reaksi Suka (Membantu) atau Tidak Suka per jawaban. Balasan dengan dukungan positif terbanyak akan otomatis mendapat label Most Helpful secara real-time.',
                ],
                [
                    'q' => 'Apakah pengelola atau admin dapat memantau perkembangan peserta?',
                    'a' => 'Ya, akun Admin dan Owner memiliki akses Dashboard Admin untuk mengelola level minigame, menyematkan topik penting forum, serta mengunduh laporan rekap pengguna dalam format Excel (.xlsx) siap pakai.',
                ],
                [
                    'q' => 'Bagaimana komunitas desa atau sekolah di Purwakarta bisa berkolaborasi?',
                    'a' => 'Anda dapat mengunjungi halaman Tentang untuk melihat informasi pengembang dan lokasi di SMK Negeri 2 Purwakarta, atau memulai topik kolaborasi pada kategori Tanya Pakar di Forum.',
                ],
            ],
        ];
    @endphp

    <section id="faq-digisafe" class="faq-section-surface py-20 sm:py-28 bg-white border-t border-gray-100">
        <div
            class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"
            x-data='{
                activeTab: "pelajar",
                openIndex: null,
                groups: @json($faqGroups),
                switchTab(tab) {
                    if (this.activeTab === tab) return;
                    this.activeTab = tab;
                    this.openIndex = null;
                },
                toggleItem(idx) {
                    this.openIndex = (this.openIndex === idx) ? null : idx;
                },
                get currentItems() {
                    return this.groups[this.activeTab] || [];
                }
            }'
        >
            <!-- Judul Q & A -->
            <div class="reveal text-center max-w-2xl mx-auto">
                <h2 class="font-heading font-extrabold text-3xl sm:text-4xl lg:text-[42px] text-ink tracking-tight leading-tight">
                    {{ __('ui.home.faq_heading_1') }}
                    <span class="text-sky">{{ __('ui.home.faq_heading_2') }}</span>
                </h2>
                <p class="mt-3 text-xs sm:text-sm text-muted leading-relaxed">
                    {{ __('ui.home.faq_subtitle') }}
                </p>
            </div>

            <!-- 3 Kartu Kategori Pilihan (Pelajar / Orang tua / Guru & Warga) -->
            <div class="reveal mt-10 grid grid-cols-1 sm:grid-cols-3 gap-3.5 max-w-2xl mx-auto">
                <button
                    type="button"
                    @click="switchTab('pelajar')"
                    :class="activeTab === 'pelajar'
                        ? 'is-active border-sky bg-[#F0F8FF] text-ink shadow-[0_10px_28px_rgba(30,136,229,0.14)] -translate-y-0.5'
                        : 'border-gray-200 bg-[#F8FAFC] text-muted hover:border-sky/40 hover:text-ink'"
                    class="faq-tab-card rounded-2xl border p-3.5 flex items-center gap-3.5 text-left cursor-pointer"
                >
                    <span
                        :class="activeTab === 'pelajar' ? 'bg-sky text-white' : 'bg-white text-sky border border-gray-200'"
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-2xs transition-colors"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                            <path d="M6 12v5c3 3 9 3 12 0v-5" />
                        </svg>
                    </span>
                    <span class="font-heading font-bold text-sm tracking-tight">{{ __('ui.home.faq_tab_student') }}</span>
                </button>

                <button
                    type="button"
                    @click="switchTab('orangtua')"
                    :class="activeTab === 'orangtua'
                        ? 'is-active border-sky bg-[#F0F8FF] text-ink shadow-[0_10px_28px_rgba(30,136,229,0.14)] -translate-y-0.5'
                        : 'border-gray-200 bg-[#F8FAFC] text-muted hover:border-sky/40 hover:text-ink'"
                    class="faq-tab-card rounded-2xl border p-3.5 flex items-center gap-3.5 text-left cursor-pointer"
                >
                    <span
                        :class="activeTab === 'orangtua' ? 'bg-lime text-ink' : 'bg-white text-ink border border-gray-200'"
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-2xs transition-colors"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            <path d="M9 12l2 2 4-4" />
                        </svg>
                    </span>
                    <span class="font-heading font-bold text-sm tracking-tight">{{ __('ui.home.faq_tab_parent') }}</span>
                </button>

                <button
                    type="button"
                    @click="switchTab('komunitas')"
                    :class="activeTab === 'komunitas'
                        ? 'is-active border-sky bg-[#F0F8FF] text-ink shadow-[0_10px_28px_rgba(30,136,229,0.14)] -translate-y-0.5'
                        : 'border-gray-200 bg-[#F8FAFC] text-muted hover:border-sky/40 hover:text-ink'"
                    class="faq-tab-card rounded-2xl border p-3.5 flex items-center gap-3.5 text-left cursor-pointer"
                >
                    <span
                        :class="activeTab === 'komunitas' ? 'bg-ink text-lime' : 'bg-white text-ink border border-gray-200'"
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-2xs transition-colors"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" />
                            <line x1="8" y1="21" x2="16" y2="21" />
                            <line x1="12" y1="17" x2="12" y2="21" />
                        </svg>
                    </span>
                    <span class="font-heading font-bold text-sm tracking-tight">{{ __('ui.home.faq_tab_community') }}</span>
                </button>
            </div>

            <!-- Daftar Accordion Q & A dengan Animasi Smooth -->
            <div class="reveal mt-10 border-t border-b border-gray-200/85 divide-y divide-gray-200/85 faq-row-border">
                <template x-for="(item, idx) in currentItems" :key="activeTab + '-' + idx">
                    <div class="faq-row-border py-1">
                        <button
                            type="button"
                            @click="toggleItem(idx)"
                            :aria-expanded="openIndex === idx"
                            class="w-full py-5 px-2 sm:px-3 flex items-center justify-between gap-4 text-left group cursor-pointer focus:outline-none"
                        >
                            <span
                                :class="openIndex === idx ? 'text-sky font-bold' : 'text-ink/85 font-semibold group-hover:text-sky'"
                                class="font-heading text-sm sm:text-base leading-snug transition-colors duration-200"
                                x-text="item.q"
                            ></span>

                            <span
                                :class="openIndex === idx ? 'bg-sky/10 text-sky border-sky/25' : 'bg-transparent text-muted border-gray-200 group-hover:border-sky/40 group-hover:text-sky'"
                                class="w-8 h-8 rounded-full border flex items-center justify-center shrink-0 transition-colors duration-200"
                            >
                                <svg
                                    :class="openIndex === idx ? 'is-open' : ''"
                                    class="faq-plus-icon w-4 h-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.2"
                                    stroke-linecap="round"
                                >
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                            </span>
                        </button>

                        <div
                            class="faq-accordion-panel"
                            :class="openIndex === idx ? 'is-open' : ''"
                        >
                            <div class="faq-accordion-inner">
                                <p
                                    class="px-2 sm:px-3 pb-5 pt-0.5 text-xs sm:text-sm text-muted leading-relaxed max-w-3xl"
                                    x-text="item.a"
                                ></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

@endsection
