@extends('layouts.app')

@section('title', 'Minigame Keamanan Digital - Digi-Safe Purwakarta')

@section('content')

    @php
        $jam = (int) now()->format('H');
        $isEn = app()->getLocale() === 'en';
        $sapaan = $isEn
            ? match (true) {
                $jam >= 4 && $jam < 11 => 'Good morning',
                $jam >= 11 && $jam < 15 => 'Good afternoon',
                $jam >= 15 && $jam < 18 => 'Good evening',
                default => 'Good night',
            }
            : match (true) {
                $jam >= 4 && $jam < 11 => 'Selamat pagi',
                $jam >= 11 && $jam < 15 => 'Selamat siang',
                $jam >= 15 && $jam < 18 => 'Selamat sore',
                default => 'Selamat malam',
            };
        $namaSapaan = Auth::check()
            ? explode(' ', trim(Auth::user()->nama_lengkap ?: Auth::user()->username))[0]
            : ($isEn ? 'Cyber Citizen' : 'Warga Siber');
    @endphp

    <section
        x-data="{ search: '' }"
        class="min-h-screen bg-[#F8FAFC] pt-24 sm:pt-28 pb-20 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">

                <!-- kolom kiri utama (70%) -->
                <div class="lg:col-span-8 space-y-7">

                    <!-- bar pencarian atas -->
                    <div class="reveal flex items-center gap-3">
                        <div class="relative flex-1">
                            <svg class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input
                                type="text"
                                x-model="search"
                                placeholder="{{ __('ui.game.search_placeholder') }}"
                                class="w-full rounded-full bg-white border border-gray-200/80 pl-11 pr-5 py-3 text-xs text-ink placeholder:text-gray-400 shadow-2xs focus:outline-none focus:border-sky"
                            >
                        </div>

                        <a
                            href="#daftar-game"
                            class="hidden sm:inline-flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200/80 text-muted hover:text-sky shadow-2xs transition-colors"
                            aria-label="Filter Game"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                            </svg>
                        </a>
                    </div>

                    <!-- banner gambar monitor retro minigame (tanpa teks tambahan karena sudah menyatu di gambar) -->
                    <a
                        href="#daftar-game"
                        aria-label="Scroll to play Minigame Digi-Safe"
                        class="reveal block rounded-[28px] overflow-hidden shadow-neutral border border-gray-200/80 hover:-translate-y-0.5 transition-transform duration-300"
                    >
                        <img
                            src="{{ asset('images/minigame-banner.jpg') }}"
                            alt="Minigame Digi-Safe - Scroll to play!"
                            class="w-full h-auto block"
                        >
                    </a>

                    <!-- 3 kapsul ringkasan kategori di bawah banner -->
                    <div class="reveal grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div class="rounded-2xl bg-white border border-gray-100 p-3.5 flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-[#DBEAFE] text-sky flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                        <polyline points="22,6 12,13 2,6" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="text-[10px] font-medium text-muted block">{{ __('ui.game.cap_1_sub') }}</span>
                                    <span class="font-heading font-bold text-xs text-ink">{{ __('ui.game.cap_1_title') }}</span>
                                </div>
                            </div>
                            <span class="text-gray-300">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="5" r="1" /><circle cx="12" cy="12" r="1" /><circle cx="12" cy="19" r="1" />
                                </svg>
                            </span>
                        </div>

                        <div class="rounded-2xl bg-white border border-gray-100 p-3.5 flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-sky text-white flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="text-[10px] font-medium text-muted block">{{ __('ui.game.cap_2_sub') }}</span>
                                    <span class="font-heading font-bold text-xs text-ink">{{ __('ui.game.cap_2_title') }}</span>
                                </div>
                            </div>
                            <span class="text-gray-300">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="5" r="1" /><circle cx="12" cy="12" r="1" /><circle cx="12" cy="19" r="1" />
                                </svg>
                            </span>
                        </div>

                        <div class="rounded-2xl bg-white border border-gray-100 p-3.5 flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-lime text-ink flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="text-[10px] font-medium text-muted block">{{ __('ui.game.cap_3_sub') }}</span>
                                    <span class="font-heading font-bold text-xs text-ink">{{ __('ui.game.cap_3_title') }}</span>
                                </div>
                            </div>
                            <span class="text-gray-300">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="5" r="1" /><circle cx="12" cy="12" r="1" /><circle cx="12" cy="19" r="1" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- grid 3 kartu pemilihan minigame bergaya pop-out ufo, roket, & satelit sesuai referensi -->
                    <div id="daftar-game" class="reveal space-y-4 pt-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="font-heading font-extrabold text-lg sm:text-xl text-ink">{{ __('ui.game.choose_mission') }}</h2>
                                <p class="text-xs text-muted">{{ __('ui.game.choose_mission_sub') }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-lime px-3.5 py-1 text-[11px] font-extrabold text-ink">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <rect x="2" y="6" width="20" height="12" rx="2" />
                                    <path d="M6 12h4m-2-2v4m7-1h.01m2.99-2h.01" />
                                </svg>
                                <span>{{ __('ui.game.games_badge', ['count' => $levels->count()]) }}</span>
                            </span>
                        </div>

                        @guest
                            <div class="rounded-2xl bg-[#F0F8FF] border border-sky/25 px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 text-xs font-semibold text-ink">
                                    <span class="w-7 h-7 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                    </span>
                                    <span>{{ __('ui.game.guest_mission_notice') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('login') }}" class="rounded-full bg-ink hover:bg-sky text-white text-[11px] font-bold px-3.5 py-1.5 transition-colors">
                                        {{ __('ui.forum.login_short') }}
                                    </a>
                                    <a href="{{ route('register') }}" class="rounded-full bg-lime text-ink text-[11px] font-extrabold px-3.5 py-1.5 hover:opacity-90 transition-opacity">
                                        {{ app()->getLocale() === 'en' ? 'Register' : 'Daftar' }}
                                    </a>
                                </div>
                            </div>
                        @endguest

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-8">

                            <!-- GAME 1: PHISHING HUNTER (Desain Referensi Kiri: UFO & Sorot Cahaya di Atas Awan) -->
                            <article
                                x-show="search === '' || 'phishing hunter analisis pesan email chat'.includes(search.toLowerCase())"
                                class="group relative rounded-[24px] bg-white border border-gray-200/80 shadow-neutral hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between overflow-visible"
                            >
                                <div>
                                    <!-- area langit biru & awan putih -->
                                    <div
                                        class="relative h-40 rounded-t-[24px] overflow-visible"
                                        style="background: linear-gradient(135deg, #0047AB 0%, #1E88E5 100%);"
                                    >
                                        <!-- sorot cahaya ufo (beam) -->
                                        <svg class="absolute inset-0 w-full h-full rounded-t-[24px] overflow-hidden pointer-events-none" viewBox="0 0 280 160" preserveAspectRatio="none">
                                            <polygon points="215,18 95,155 245,155" fill="url(#ufoBeamGrad)" />
                                            <defs>
                                                <linearGradient id="ufoBeamGrad" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.55" />
                                                    <stop offset="100%" stop-color="#DBEAFE" stop-opacity="0.08" />
                                                </linearGradient>
                                            </defs>
                                        </svg>

                                        <!-- awan putih bergelombang dari kiri atas ke kanan bawah sesuai referensi -->
                                        <svg class="absolute inset-x-0 -bottom-1 w-full h-32 pointer-events-none" viewBox="0 0 280 125" preserveAspectRatio="none">
                                            <path d="M0,25 Q28,15 38,42 Q65,30 78,56 Q108,45 120,72 Q152,60 168,85 Q198,74 212,96 Q248,82 280,98 L280,125 L0,125 Z" fill="#FFFFFF" />
                                            <path d="M22,55 Q42,46 54,64 M74,72 Q96,62 108,82 M142,88 Q165,78 178,96" stroke="#1E88E5" stroke-width="2" stroke-linecap="round" fill="none" opacity="0.55" />
                                        </svg>

                                        <!-- ilustrasi UFO keluar dari batas kanan-atas kartu -->
                                        <div class="absolute -top-8 -right-3 w-28 h-20 pointer-events-none group-hover:rotate-6 group-hover:scale-110 transition-transform duration-300 z-20">
                                            <svg viewBox="0 0 120 85" class="w-full h-full drop-shadow-md">
                                                <g transform="rotate(16 60 42)">
                                                    <!-- kubah kaca atas -->
                                                    <path d="M36 36 C36 14, 84 14, 84 36 Z" fill="#93C5FD" stroke="#1E3A8A" stroke-width="2" />
                                                    <!-- badan piringan ufo -->
                                                    <ellipse cx="60" cy="40" rx="46" ry="15" fill="#60A5FA" stroke="#1E3A8A" stroke-width="2" />
                                                    <ellipse cx="60" cy="43" rx="34" ry="9" fill="#1D4ED8" />
                                                    <!-- lampu sorot bawah -->
                                                    <ellipse cx="60" cy="44" rx="22" ry="6" fill="#FEF9C3" />
                                                    <!-- titik lampu mengelilingi ufo -->
                                                    <circle cx="28" cy="36" r="2.5" fill="#E0F2FE" />
                                                    <circle cx="48" cy="31" r="2.5" fill="#E0F2FE" />
                                                    <circle cx="72" cy="31" r="2.5" fill="#E0F2FE" />
                                                    <circle cx="92" cy="36" r="2.5" fill="#E0F2FE" />
                                                </g>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- konten teks tengah (rata kiri seperti kartu kiri referensi) -->
                                    <div class="px-6 pt-3 pb-5 text-left">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#DBEAFE] text-sky px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                            <span>{{ __('ui.game.badge_1') }}</span>
                                        </span>
                                        <h3 class="mt-2.5 font-heading font-extrabold text-lg text-ink group-hover:text-sky transition-colors">
                                            Phishing Hunter
                                        </h3>
                                        <p class="mt-1.5 text-xs text-muted leading-relaxed">
                                            {{ __('ui.game.desc_1') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- bar aksi bawah penuh sesuai referensi (dikunci jika belum login) -->
                                @auth
                                    <a
                                        href="{{ route('minigame.play', 'phishing') }}"
                                        class="w-full py-4 px-6 border-t border-gray-100 rounded-b-[24px] bg-white group-hover:bg-sky text-center font-heading font-extrabold text-xs text-sky group-hover:text-white flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <span>{{ __('ui.game.play_now') }}</span>
                                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="5 3 19 12 5 21 5 3" />
                                        </svg>
                                    </a>
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="w-full py-4 px-5 border-t border-gray-100 rounded-b-[24px] bg-gray-50 hover:bg-sky text-center font-heading font-bold text-[11px] text-ink/85 hover:text-white flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        <span>{{ __('ui.game.guest_lock_1') }}</span>
                                    </a>
                                @endauth
                            </article>

                            <!-- GAME 2: CYBER DEFENDER (Desain Referensi Kanan: Roket Meluncur Tegak di Tengah) -->
                            <article
                                x-show="search === '' || 'cyber defender pertahanan sistem virus malware'.includes(search.toLowerCase())"
                                class="group relative rounded-[24px] bg-white border border-gray-200/80 shadow-neutral hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between overflow-visible"
                            >
                                <div>
                                    <!-- area langit malam & kubah bumi biru -->
                                    <div
                                        class="relative h-40 rounded-t-[24px] overflow-visible"
                                        style="background: linear-gradient(180deg, #0B154B 0%, #172554 100%);"
                                    >
                                        <!-- kubah planet biru & kepulan awan putih di tengah bawah -->
                                        <svg class="absolute inset-x-0 -bottom-1 w-full h-28 pointer-events-none" viewBox="0 0 280 110" preserveAspectRatio="none">
                                            <!-- lengkungan planet biru -->
                                            <path d="M18,110 C55,28 225,28 262,110 Z" fill="#1E88E5" />
                                            <!-- kepulan awan peluncuran putih -->
                                            <path d="M55,110 Q72,78 98,88 Q115,62 140,70 Q165,62 182,88 Q208,78 225,110 Z" fill="#FFFFFF" />
                                            <rect x="0" y="104" width="280" height="8" fill="#FFFFFF" />
                                        </svg>

                                        <!-- ilustrasi roket meluncur tegak menembus batas atas kartu -->
                                        <div class="absolute -top-11 left-1/2 -translate-x-1/2 w-24 h-36 pointer-events-none group-hover:-translate-y-2 transition-transform duration-300 z-20">
                                            <svg viewBox="0 0 100 150" class="w-full h-full drop-shadow-md">
                                                <!-- semburan api roket -->
                                                <path d="M42 106 L50 138 L58 106 Z" fill="#F97316" />
                                                <path d="M45 106 L50 126 L55 106 Z" fill="#FACC15" />
                                                <!-- sirip kiri & kanan -->
                                                <path d="M34 78 L18 102 L36 98 Z" fill="#F97316" />
                                                <path d="M66 78 L82 102 L64 98 Z" fill="#F97316" />
                                                <!-- badan roket putih -->
                                                <path d="M50 8 C30 34, 30 76, 36 104 L64 104 C70 76, 70 34, 50 8 Z" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5" />
                                                <!-- ujung hidung roket kuning -->
                                                <path d="M50 8 C43 17, 40 24, 38 30 L62 30 C60 24, 57 17, 50 8 Z" fill="#FACC15" />
                                                <!-- jendela bulat tengah -->
                                                <circle cx="50" cy="52" r="10" fill="#93C5FD" stroke="#F97316" stroke-width="2.5" />
                                                <!-- sirip tengah -->
                                                <rect x="47.5" y="80" width="5" height="26" rx="2.5" fill="#EA580C" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- konten teks tengah (rata tengah seperti kartu kanan referensi) -->
                                    <div class="px-6 pt-3 pb-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#DBEAFE] text-sky px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                            <span>{{ __('ui.game.badge_2') }}</span>
                                        </span>
                                        <h3 class="mt-2.5 font-heading font-extrabold text-lg text-ink group-hover:text-sky transition-colors">
                                            Cyber Defender
                                        </h3>
                                        <p class="mt-1.5 text-xs text-muted leading-relaxed">
                                            {{ __('ui.game.desc_2') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- bar aksi bawah penuh sesuai referensi (dikunci jika belum login) -->
                                @auth
                                    <a
                                        href="{{ route('minigame.play', 'cyber-defender') }}"
                                        class="w-full py-4 px-6 border-t border-gray-100 rounded-b-[24px] bg-white group-hover:bg-ink text-center font-heading font-extrabold text-xs text-ink group-hover:text-lime flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <span>{{ __('ui.game.start_defense') }}</span>
                                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="5 3 19 12 5 21 5 3" />
                                        </svg>
                                    </a>
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="w-full py-4 px-5 border-t border-gray-100 rounded-b-[24px] bg-gray-50 hover:bg-ink text-center font-heading font-bold text-[11px] text-ink/85 hover:text-lime flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        <span>{{ __('ui.game.guest_lock_2') }}</span>
                                    </a>
                                @endauth
                            </article>

                            <!-- GAME 3: LINK OR TRAP? (Variasi Serasi: Satelit Radar Pemindai URL di Atas Awan) -->
                            <article
                                x-show="search === '' || 'link or trap inspeksi tautan url domain'.includes(search.toLowerCase())"
                                class="group relative rounded-[24px] bg-white border border-gray-200/80 shadow-neutral hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between overflow-visible"
                            >
                                <div>
                                    <!-- area langit biru siber & gelombang radar -->
                                    <div
                                        class="relative h-40 rounded-t-[24px] overflow-visible"
                                        style="background: linear-gradient(135deg, #0369A1 0%, #0EA5E9 100%);"
                                    >
                                        <!-- gelombang sinyal radar -->
                                        <svg class="absolute inset-0 w-full h-full rounded-t-[24px] overflow-hidden pointer-events-none" viewBox="0 0 280 160" preserveAspectRatio="none">
                                            <circle cx="85" cy="25" r="45" stroke="rgba(198,244,50,0.45)" stroke-width="2" stroke-dasharray="5 5" fill="none" />
                                            <circle cx="85" cy="25" r="78" stroke="rgba(255,255,255,0.3)" stroke-width="1.5" fill="none" />
                                            <polygon points="85,25 35,155 175,155" fill="rgba(198,244,50,0.16)" />
                                        </svg>

                                        <!-- awan putih mengalir dari kanan ke kiri -->
                                        <svg class="absolute inset-x-0 -bottom-1 w-full h-32 pointer-events-none" viewBox="0 0 280 125" preserveAspectRatio="none">
                                            <path d="M280,32 Q250,20 235,48 Q205,36 190,62 Q158,50 142,76 Q108,65 92,90 Q52,78 0,98 L0,125 L280,125 Z" fill="#FFFFFF" />
                                            <path d="M245,60 Q225,50 212,68 M185,78 Q165,68 152,86" stroke="#0284C7" stroke-width="2" stroke-linecap="round" fill="none" opacity="0.5" />
                                        </svg>

                                        <!-- ilustrasi satelit perisai scanner keluar dari batas kiri-atas kartu -->
                                        <div class="absolute -top-9 left-4 w-28 h-24 pointer-events-none group-hover:-rotate-6 group-hover:scale-110 transition-transform duration-300 z-20">
                                            <svg viewBox="0 0 120 100" class="w-full h-full drop-shadow-md">
                                                <g transform="rotate(-14 60 50)">
                                                    <!-- sayap panel surya kiri & kanan -->
                                                    <rect x="6" y="38" width="28" height="18" rx="3" fill="#38BDF8" stroke="#0F172A" stroke-width="2" />
                                                    <line x1="15" y1="38" x2="15" y2="56" stroke="#0F172A" stroke-width="1.5" />
                                                    <line x1="25" y1="38" x2="25" y2="56" stroke="#0F172A" stroke-width="1.5" />

                                                    <rect x="86" y="38" width="28" height="18" rx="3" fill="#38BDF8" stroke="#0F172A" stroke-width="2" />
                                                    <line x1="95" y1="38" x2="95" y2="56" stroke="#0F172A" stroke-width="1.5" />
                                                    <line x1="105" y1="38" x2="105" y2="56" stroke="#0F172A" stroke-width="1.5" />

                                                    <!-- lengan penghubung -->
                                                    <line x1="34" y1="47" x2="42" y2="47" stroke="#0F172A" stroke-width="3" />
                                                    <line x1="78" y1="47" x2="86" y2="47" stroke="#0F172A" stroke-width="3" />

                                                    <!-- modul inti satelit -->
                                                    <rect x="42" y="28" width="36" height="38" rx="8" fill="#FFFFFF" stroke="#0F172A" stroke-width="2" />
                                                    <!-- lensa radar lime -->
                                                    <circle cx="60" cy="47" r="10" fill="#C6F432" stroke="#0F172A" stroke-width="2" />
                                                    <circle cx="60" cy="47" r="4" fill="#0F172A" />
                                                    <!-- antena bawah -->
                                                    <line x1="60" y1="66" x2="60" y2="78" stroke="#0F172A" stroke-width="2.5" />
                                                    <circle cx="60" cy="80" r="4" fill="#F97316" />
                                                </g>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- konten teks tengah -->
                                    <div class="px-6 pt-3 pb-5 text-left">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-lime/55 text-ink px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                            <span>{{ __('ui.game.badge_3') }}</span>
                                        </span>
                                        <h3 class="mt-2.5 font-heading font-extrabold text-lg text-ink group-hover:text-sky transition-colors">
                                            Link or Trap?
                                        </h3>
                                        <p class="mt-1.5 text-xs text-muted leading-relaxed">
                                            {{ __('ui.game.desc_3') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- bar aksi bawah penuh sesuai referensi (dikunci jika belum login) -->
                                @auth
                                    <a
                                        href="{{ route('minigame.play', 'link-trap') }}"
                                        class="w-full py-4 px-6 border-t border-gray-100 rounded-b-[24px] bg-white group-hover:bg-sky text-center font-heading font-extrabold text-xs text-sky group-hover:text-white flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <span>{{ __('ui.game.test_url') }}</span>
                                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="5 3 19 12 5 21 5 3" />
                                        </svg>
                                    </a>
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="w-full py-4 px-5 border-t border-gray-100 rounded-b-[24px] bg-gray-50 hover:bg-sky text-center font-heading font-bold text-[11px] text-ink/85 hover:text-white flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        <span>{{ __('ui.game.guest_lock_3') }}</span>
                                    </a>
                                @endauth
                            </article>

                            <!-- GAME 4: PASSWORD STRENGTH CHECKER (Gembok & Kunci Cyber Pop-Out di Atas Awan) -->
                            <article
                                x-show="search === '' || 'password strength checker sandi kuat gembok keamanan'.includes(search.toLowerCase())"
                                class="group relative rounded-[24px] bg-white border border-gray-200/80 shadow-neutral hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between overflow-visible"
                            >
                                <div>
                                    <div
                                        class="relative h-40 rounded-t-[24px] overflow-visible"
                                        style="background: linear-gradient(135deg, #0F172A 0%, #1D4ED8 100%);"
                                    >
                                        <!-- aura perisai & garis sandi -->
                                        <svg class="absolute inset-0 w-full h-full rounded-t-[24px] overflow-hidden pointer-events-none" viewBox="0 0 280 160" preserveAspectRatio="none">
                                            <circle cx="205" cy="40" r="56" stroke="rgba(198,244,50,0.35)" stroke-width="1.5" stroke-dasharray="5 5" fill="none" />
                                            <circle cx="205" cy="40" r="32" fill="rgba(56,189,248,0.16)" />
                                            <circle cx="52" cy="42" r="2.5" fill="rgba(255,255,255,0.65)" />
                                            <circle cx="88" cy="24" r="2" fill="#C6F432" />
                                            <circle cx="122" cy="54" r="2" fill="rgba(255,255,255,0.5)" />
                                        </svg>

                                        <!-- awan putih bergelombang selaras kartu lainnya -->
                                        <svg class="absolute inset-x-0 -bottom-1 w-full h-32 pointer-events-none" viewBox="0 0 280 125" preserveAspectRatio="none">
                                            <path d="M0,32 Q30,18 46,46 Q74,34 90,60 Q124,48 142,74 Q174,62 194,86 Q230,72 280,96 L280,125 L0,125 Z" fill="#FFFFFF" />
                                            <path d="M30,60 Q52,50 66,68 M104,76 Q128,66 142,84 M176,90 Q198,80 214,96" stroke="#1E88E5" stroke-width="2" stroke-linecap="round" fill="none" opacity="0.5" />
                                        </svg>

                                        <!-- ilustrasi gembok & kunci siber keluar dari batas kanan-atas kartu -->
                                        <div class="absolute -top-9 right-3 w-28 h-28 pointer-events-none group-hover:rotate-6 group-hover:scale-110 transition-transform duration-300 z-20">
                                            <svg viewBox="0 0 120 115" class="w-full h-full drop-shadow-md">
                                                <g transform="rotate(10 60 58)">
                                                    <!-- lengkungan shackle gembok -->
                                                    <path d="M38 46 V31 C38 15, 82 15, 82 31 V46" fill="none" stroke="#E2E8F0" stroke-width="10" stroke-linecap="round" />
                                                    <path d="M38 46 V31 C38 15, 82 15, 82 31 V46" fill="none" stroke="#1E3A8A" stroke-width="2" stroke-linecap="round" />
                                                    <!-- badan gembok -->
                                                    <rect x="26" y="44" width="68" height="52" rx="14" fill="#38BDF8" stroke="#0F172A" stroke-width="2.5" />
                                                    <rect x="33" y="51" width="54" height="38" rx="9" fill="#1D4ED8" />
                                                    <!-- lubang kunci neon lime -->
                                                    <circle cx="60" cy="66" r="8" fill="#C6F432" stroke="#0F172A" stroke-width="2" />
                                                    <path d="M57 72 L55 83 H65 L63 72 Z" fill="#C6F432" stroke="#0F172A" stroke-width="1.6" stroke-linejoin="round" />
                                                    <!-- kilau kunci kecil -->
                                                    <circle cx="96" cy="30" r="6" fill="#C6F432" stroke="#0F172A" stroke-width="1.8" />
                                                    <path d="M96 27 V33 M93 30 H99" stroke="#0F172A" stroke-width="1.6" stroke-linecap="round" />
                                                </g>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- konten teks tengah -->
                                    <div class="px-6 pt-3 pb-5 text-left">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 text-emerald-800 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                            <span>{{ $isEn ? 'PASSWORD CHECKER' : 'CEK KEKUATAN SANDI' }}</span>
                                        </span>
                                        <h3 class="mt-2.5 font-heading font-extrabold text-lg text-ink group-hover:text-sky transition-colors">
                                            Password Strength Checker
                                        </h3>
                                        <p class="mt-1.5 text-xs text-muted leading-relaxed">
                                            {{ $isEn
                                                ? 'Type a common password and get instant smart upgrade suggestions based on your phrase.'
                                                : 'Tulis password biasa dan dapatkan saran kombinasi kata sandi jauh lebih kuat secara otomatis.' }}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('minigame.password-checker') }}"
                                    class="w-full py-4 px-6 border-t border-gray-100 rounded-b-[24px] bg-white group-hover:bg-sky text-center font-heading font-extrabold text-xs text-sky group-hover:text-white flex items-center justify-center gap-2 transition-colors"
                                >
                                    <span>{{ $isEn ? 'Check & Upgrade Password' : 'Cek & Perkuat Sandi' }}</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3" />
                                    </svg>
                                </a>
                            </article>

                            <!-- GAME 5: SIMULASI CHAT WHATSAPP & FILE .APK (15 KONTAK · MULTI-TURN · MULTI-ENDING) -->
                            <article
                                x-show="search === '' || 'whatsapp wa apk penipuan chat kurir tilang undangan simulasi pinjol bpjs'.includes(search.toLowerCase())"
                                class="md:col-span-2 group relative rounded-[24px] bg-white border border-emerald-200/90 shadow-neutral hover:-translate-y-1.5 transition-all duration-300 overflow-hidden"
                            >
                                <div class="grid grid-cols-1 sm:grid-cols-12 items-stretch">
                                     <div class="sm:col-span-5 relative min-h-[200px] sm:min-h-[230px] bg-[#FAF8F3] overflow-hidden border-b sm:border-b-0 sm:border-r border-gray-100">
                                         <img
                                             src="{{ asset('images/thumb-wa-simulator.jpg') }}"
                                             alt="{{ $isEn ? 'WhatsApp & .APK Scam Simulator Thumbnail' : 'Thumbnail Simulasi Chat WhatsApp & .APK' }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                                         >
                                         <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-ink/85 backdrop-blur-xs text-white px-3 py-1 text-[10px] font-heading font-extrabold uppercase tracking-wider shadow-xs">
                                             <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                                             {{ $isEn ? '15 Contacts · Multi-Ending' : '15 Orang · Multi-Ending' }}
                                         </span>
                                     </div>
                                     <div class="sm:col-span-7 p-6 flex flex-col justify-between gap-4">
                                         <div>
                                             <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 text-emerald-800 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                                 {{ $isEn ? 'INTERACTIVE CHAT SIMULATOR' : 'SIMULASI CHAT INTERAKTIF' }}
                                             </span>
                                             <h3 class="mt-2 font-heading font-extrabold text-lg sm:text-xl text-ink group-hover:text-emerald-700 transition-colors">
                                                 {{ $isEn ? 'WhatsApp & .APK Scam Simulator' : 'Simulasi Waspada Penipuan WhatsApp & File .APK' }}
                                             </h3>
                                             <p class="mt-1.5 text-xs text-muted leading-relaxed">
                                                 {{ $isEn
                                                     ? 'Chat directly with 15 realistic scam personas across extended multi-turn dialogues (fake couriers, wedding .APK, E-TLE tickets, bank fee scams, illegal loan sharks, BPJS .APK, customs giveaways). Every choice branches into a different ending!'
                                                     : 'Rasakan pengalaman chat bertahap yang panjang layaknya WhatsApp bersama 15 kontak penipu berbeda (kurir .APK, undangan nikah, tilang E-TLE, CS bank palsu, pinjol salah transfer, BPJS .APK, hingga lelang bea cukai). Setiap pilihan menuju ending berbeda!' }}
                                             </p>
                                         </div>
                                         <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100">
                                             <span class="text-[11px] font-semibold text-muted">
                                                 {{ $isEn ? '15 Personas · Bilingual ID/EN · Safe Sandbox' : '15 Kontak · Bilingual ID/EN · 100% Simulasi Aman' }}
                                             </span>
                                             <a
                                                 href="{{ route('minigame.wa-simulator') }}"
                                                 class="inline-flex items-center gap-2 rounded-full bg-[#075E54] hover:bg-ink text-white font-heading font-extrabold text-xs px-6 py-3 transition-colors"
                                             >
                                                 <span>{{ $isEn ? 'Open Chat Simulator' : 'Buka Simulasi Chat WA' }}</span>
                                                 <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                     <polygon points="5 3 19 12 5 21 5 3" />
                                                 </svg>
                                             </a>
                                         </div>
                                     </div>
                                </div>
                            </article>

                            <!-- GAME 6: PEMINDAI TAUTAN & QR CODE PHISHING (LINK & QR SCANNER) -->
                            <article
                                x-show="search === '' || 'link qr scanner phishing url domain qris barcode apk cek tautan'.includes(search.toLowerCase())"
                                class="md:col-span-2 group relative rounded-[24px] bg-white border border-sky-200/90 shadow-neutral hover:-translate-y-1.5 transition-all duration-300 overflow-hidden"
                            >
                                <div class="grid grid-cols-1 sm:grid-cols-12 items-stretch">
                                    <div class="sm:col-span-5 relative min-h-[200px] sm:min-h-[230px] bg-[#FAF8F3] overflow-hidden border-b sm:border-b-0 sm:border-r border-gray-100">
                                        <img
                                            src="{{ asset('images/thumb-link-scanner.jpg') }}"
                                            alt="{{ $isEn ? 'Link & QR Phishing Scanner Thumbnail' : 'Thumbnail Pemindai Tautan & QR Phishing' }}"
                                            loading="lazy"
                                            class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                                        >
                                        <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-ink/85 backdrop-blur-xs text-white px-3 py-1 text-[10px] font-heading font-extrabold uppercase tracking-wider shadow-xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-lime"></span>
                                            {{ $isEn ? 'URL & QR Forensics' : 'Forensik URL & QR Code' }}
                                        </span>
                                    </div>
                                    <div class="sm:col-span-7 p-6 flex flex-col justify-between gap-4">
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-sky/10 text-sky px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                                {{ $isEn ? 'CYBER SECURITY UTILITY' : 'ALAT DETEKSI SIBER' }}
                                            </span>
                                            <h3 class="mt-2 font-heading font-extrabold text-lg sm:text-xl text-ink group-hover:text-sky transition-colors">
                                                {{ $isEn ? 'Link & QR Code Phishing Scanner' : 'Pemindai Tautan & QR Code Phishing' }}
                                            </h3>
                                            <p class="mt-1.5 text-xs text-muted leading-relaxed">
                                                {{ $isEn
                                                    ? 'Paste any suspicious link or upload a QR Code image to dissect its domain anatomy, detect brand impersonation (BRI, BCA, JNE, DANA, .go.id), uncover hidden .APK/.EXE malware files, and test sample QR phishing cases.'
                                                    : 'Tempel tautan mencurigakan atau unggah gambar QR Code untuk membedah anatomi domain, mendeteksi pencatutan nama bank/instansi (BRI, BCA, JNE, DANA, .go.id), membongkar file .APK berbahaya, serta menguji simulasi QRIS palsu.' }}
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100">
                                            <span class="text-[11px] font-semibold text-muted">
                                                {{ $isEn ? 'Instant heuristic analysis · Zero data stored' : 'Analisis heuristik instan · Aman tanpa membuka link' }}
                                            </span>
                                            <a
                                                href="{{ route('minigame.link-scanner') }}"
                                                class="inline-flex items-center gap-2 rounded-full bg-sky hover:bg-ink text-white font-heading font-extrabold text-xs px-6 py-3 transition-colors"
                                            >
                                                <span>{{ $isEn ? 'Open Link & QR Scanner' : 'Buka Pemindai Link & QR' }}</span>
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="11" cy="11" r="8" />
                                                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                        </div>
                    </div>

                    <!-- daftar tingkatan level minigame aktif (di bawah pilihan game) -->
                    <div class="reveal rounded-[24px] bg-white border border-gray-100 p-5 sm:p-6 shadow-2xs">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky block">{{ __('ui.game.level_system') }}</span>
                                <h2 class="font-heading font-extrabold text-base sm:text-lg text-ink">
                                    {{ __('ui.game.level_system_title', ['count' => $levels->count()]) }}
                                </h2>
                            </div>

                            @if (Auth::check() && Auth::user()->hasAdminAccess())
                                <a
                                    href="{{ route('admin.minigame.index') }}"
                                    class="inline-flex items-center gap-2 rounded-full bg-ink hover:bg-sky text-white text-xs font-heading font-bold px-4 py-2 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5 text-lime" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                    </svg>
                                    <span>{{ __('ui.game.manage_levels_admin') }}</span>
                                </a>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            @foreach ($levels as $lvlItem)
                                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/75 p-3.5 flex flex-col justify-between">
                                    <div>
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[9px] font-extrabold uppercase {{ $lvlItem->badge_classes }}">
                                            {{ $lvlItem->badge_label }}
                                        </span>
                                        <h3 class="mt-2 font-heading font-bold text-xs text-ink">{{ $lvlItem->nama_level }}</h3>
                                        <p class="mt-1 text-[11px] text-muted leading-snug line-clamp-2">{{ $lvlItem->deskripsi }}</p>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-gray-200/60 flex items-center justify-between text-[10px] font-bold text-ink">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-sky shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                                            <span>{{ $lvlItem->waktu_detik }}{{ __('ui.game.sec_short') }}</span>
                                        </span>
                                        <span class="text-emerald-600">+{{ $lvlItem->poin_benar }} pts</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- tabel riwayat bermain -->
                    <div class="reveal rounded-[24px] bg-white border border-gray-100 p-6 shadow-2xs">
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h2 class="font-heading font-bold text-base sm:text-lg text-ink">{{ __('ui.game.history_title') }}</h2>
                                <p class="text-xs text-muted mt-0.5">{{ __('ui.game.history_sub') }}</p>
                            </div>
                            @auth
                                <span class="rounded-full bg-[#DBEAFE] text-sky text-[11px] font-bold px-3 py-1">
                                    {{ __('ui.game.sessions_count', ['count' => $stats['total_main']]) }}
                                </span>
                            @endauth
                        </div>

                        @guest
                            <div class="rounded-2xl bg-[#F8FAFC] border border-dashed border-gray-200 p-8 text-center">
                                <div class="w-11 h-11 rounded-full bg-[#DBEAFE] text-sky flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </div>
                                <p class="font-heading font-bold text-sm text-ink">{{ __('ui.game.login_to_save') }}</p>
                                <p class="text-xs text-muted mt-1 max-w-sm mx-auto">
                                    {{ __('ui.game.login_to_save_desc') }}
                                </p>
                                <a
                                    href="{{ route('login') }}"
                                    class="mt-4 inline-flex items-center gap-2 rounded-full bg-lime text-ink font-bold text-xs px-5 py-2.5 shadow-2xs hover:-translate-y-0.5 transition-transform"
                                >
                                    <span>{{ __('ui.game.join_login_now') }}</span>
                                </a>
                            </div>
                        @endguest

                        @auth
                            @if ($riwayat->isEmpty())
                                <div class="rounded-2xl bg-[#F8FAFC] p-7 text-center">
                                    <p class="text-xs font-semibold text-muted">{{ __('ui.game.empty_history') }}</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-gray-100 text-[10px] font-bold uppercase tracking-wider text-muted">
                                                <th class="pb-3 pr-4">{{ __('ui.game.col_game') }}</th>
                                                <th class="pb-3 px-4">{{ __('ui.game.col_score') }}</th>
                                                <th class="pb-3 px-4">{{ __('ui.game.col_result') }}</th>
                                                <th class="pb-3 px-4">{{ __('ui.game.col_time') }}</th>
                                                <th class="pb-3 pl-4 text-right">{{ __('ui.game.col_action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 text-xs">
                                            @foreach ($riwayat as $item)
                                                @php
                                                    $estBenar = max(1, (int) round(max(0, $item->skor) / 10));
                                                    $estSalah = $item->skor >= 80 ? 1 : ($item->skor >= 50 ? 2 : 3);
                                                    $slugUlang = match ($item->game_type) {
                                                        'cyber-defender', 'level2' => 'cyber-defender',
                                                        'link-trap', 'level3' => 'link-trap',
                                                        default => 'phishing',
                                                    };
                                                @endphp
                                                <tr class="hover:bg-gray-50/70 transition-colors">
                                                    <td class="py-3.5 pr-4">
                                                        <div class="flex items-center gap-3">
                                                            <span class="w-8 h-8 rounded-full bg-[#DBEAFE] text-sky flex items-center justify-center font-bold text-[11px]">
                                                                {{ substr($item->nama_game, 0, 1) }}
                                                            </span>
                                                            <div>
                                                                <span class="font-heading font-bold text-ink block">{{ $item->nama_game }}</span>
                                                                <span class="text-[10px] text-muted">{{ __('ui.game.record_prefix') }} {{ $item->skor_tertinggi }} pts</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3.5 px-4">
                                                        <span class="inline-flex items-center rounded-full bg-[#DBEAFE] text-sky font-bold text-[11px] px-2.5 py-0.5">
                                                            {{ $item->skor }} pts
                                                        </span>
                                                    </td>
                                                    <td class="py-3.5 px-4">
                                                        <div class="flex items-center gap-2 text-[11px] font-semibold">
                                                            <span class="text-emerald-600">{{ $estBenar }} {{ __('ui.game.correct') }}</span>
                                                            <span class="text-gray-300">/</span>
                                                            <span class="text-red-500">{{ $estSalah }} {{ __('ui.game.wrong') }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-3.5 px-4 text-muted text-[11px]">
                                                        {{ $item->played_at ? $item->played_at->format('d M Y, H:i') : '-' }}
                                                    </td>
                                                    <td class="py-3.5 pl-4 text-right">
                                                        <a
                                                            href="{{ route('minigame.play', $slugUlang) }}"
                                                            class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-gray-200 text-muted hover:text-sky hover:border-sky transition-colors"
                                                            title="{{ __('ui.game.replay') }}"
                                                        >
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <line x1="7" y1="17" x2="17" y2="7" />
                                                                <polyline points="7 7 17 7 17 17" />
                                                            </svg>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endauth
                    </div>

                </div>

                <!-- kolom kanan: panel statistik & leaderboard (30%) -->
                <aside class="lg:col-span-4 space-y-6">

                    <!-- card profil & statistik bar chart -->
                    <div class="reveal rounded-[26px] bg-white border border-gray-100 p-6 shadow-2xs">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-heading font-bold text-sm text-ink">{{ __('ui.game.stats_title') }}</h2>
                            @auth
                                <a href="{{ route('profile.edit') }}" class="text-xs font-semibold text-sky hover:underline">{{ __('ui.game.view_profile') }}</a>
                            @endauth
                        </div>

                        <!-- foto profil bulat dengan ring biru & badge akurasi -->
                        <div class="flex flex-col items-center text-center">
                            <div class="relative w-24 h-24 rounded-full p-1.5 border-[3px] border-[#BFDBFE] flex items-center justify-center">
                                @auth
                                    <a href="{{ route('profile.edit') }}" class="w-full h-full block">
                                        <x-user-avatar :user="Auth::user()" size="xl" class="!w-full !h-full" />
                                    </a>
                                @else
                                    <x-user-avatar :name="__('ui.game.citizen_default')" size="xl" class="!w-full !h-full" />
                                @endauth

                                <span class="absolute -top-1 -right-1 rounded-full bg-sky text-white text-[10px] font-bold px-2 py-0.5 shadow-xs">
                                    {{ $stats['akurasi'] }}%
                                </span>
                            </div>

                            <h3 class="mt-3.5 font-heading font-bold text-base text-ink">
                                {{ $sapaan }}, {{ $namaSapaan }}
                            </h3>
                            <p class="mt-1 text-[11px] text-muted leading-relaxed">
                                {{ __('ui.game.stats_motto') }}
                            </p>
                        </div>

                        <!-- css bar chart skor per game dengan palet biru & lime -->
                        @php
                            $maxChart = max(100, $stats['phishing_max'], $stats['defender_max'], $stats['link_max']);
                            $hPhishing = max(18, (int) round(($stats['phishing_max'] / $maxChart) * 100));
                            $hDefender = max(18, (int) round(($stats['defender_max'] / $maxChart) * 100));
                            $hLink = max(18, (int) round(($stats['link_max'] / $maxChart) * 100));
                        @endphp
                        <div class="mt-6 rounded-2xl bg-[#F8FAFC] p-4">
                            <div class="flex items-end justify-between gap-3 h-28 pt-4 px-2 border-b border-gray-200/70">
                                <!-- bar phishing -->
                                <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                                    <span class="text-[10px] font-bold text-ink">{{ $stats['phishing_max'] }}</span>
                                    <div class="w-full max-w-[34px] rounded-t-xl bg-sky transition-all duration-500" style="height: {{ $hPhishing }}%;"></div>
                                </div>

                                <!-- bar cyber defender -->
                                <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                                    <span class="text-[10px] font-bold text-ink">{{ $stats['defender_max'] }}</span>
                                    <div class="w-full max-w-[34px] rounded-t-xl bg-[#60A5FA] transition-all duration-500" style="height: {{ $hDefender }}%;"></div>
                                </div>

                                <!-- bar link trap -->
                                <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                                    <span class="text-[10px] font-bold text-ink">{{ $stats['link_max'] }}</span>
                                    <div class="w-full max-w-[34px] rounded-t-xl bg-lime border border-ink/10 transition-all duration-500" style="height: {{ $hLink }}%;"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-2.5 text-center text-[10px] font-semibold text-muted">
                                <span>Phishing</span>
                                <span>Defender</span>
                                <span>Link Trap</span>
                            </div>
                        </div>

                        <!-- 4 kotak ringkasan angka total -->
                        <div class="mt-4 grid grid-cols-2 gap-2.5">
                            <div class="rounded-xl bg-[#F8FAFC] p-3">
                                <span class="text-[10px] font-medium text-muted block">{{ __('ui.game.total_games') }}</span>
                                <span class="font-heading font-extrabold text-base text-ink mt-0.5 block">{{ $stats['total_main'] }}</span>
                            </div>
                            <div class="rounded-xl bg-[#F8FAFC] p-3">
                                <span class="text-[10px] font-medium text-muted block">{{ __('ui.game.accuracy') }}</span>
                                <span class="font-heading font-extrabold text-base text-sky mt-0.5 block">{{ $stats['akurasi'] }}%</span>
                            </div>
                            <div class="rounded-xl bg-emerald-50/70 p-3">
                                <span class="text-[10px] font-medium text-emerald-700 block">{{ __('ui.game.total_correct') }}</span>
                                <span class="font-heading font-extrabold text-base text-emerald-700 mt-0.5 block">{{ $stats['total_benar'] }}</span>
                            </div>
                            <div class="rounded-xl bg-red-50/70 p-3">
                                <span class="text-[10px] font-medium text-red-600 block">{{ __('ui.game.total_wrong') }}</span>
                                <span class="font-heading font-extrabold text-base text-red-600 mt-0.5 block">{{ $stats['total_salah'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- mini leaderboard top 3 pemain dengan sinkronisasi avatar & link profil -->
                    <div class="reveal reveal-delay-1 rounded-[26px] bg-white border border-gray-100 p-6 shadow-2xs">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-heading font-bold text-sm text-ink">{{ __('ui.game.mini_leaderboard') }}</h2>
                            <span class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-sky">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                            </span>
                        </div>

                        <div class="space-y-3.5">
                            @forelse ($leaderboard as $idx => $leader)
                                @php
                                    $leaderName = $leader->nama_lengkap ?: $leader->username;
                                @endphp
                                <div class="flex items-center justify-between gap-3">
                                    <a href="{{ route('profile.show', $leader->id) }}" class="flex items-center gap-3 min-w-0 group/leader">
                                        <div class="relative shrink-0">
                                            <x-user-avatar :user="$leader" size="md" class="!w-10 !h-10 group-hover/leader:scale-105 transition-transform" />
                                            <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-ink text-lime text-[9px] font-bold flex items-center justify-center">
                                                {{ $idx + 1 }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-heading font-bold text-xs text-ink group-hover/leader:text-sky transition-colors truncate">{{ $leaderName }}</p>
                                            <p class="text-[10px] text-muted">{{ __('ui.game.sessions_played', ['count' => $leader->matches_played]) }}</p>
                                        </div>
                                    </a>

                                    <span class="shrink-0 rounded-full border border-sky/25 bg-[#DBEAFE]/60 text-sky font-bold text-[11px] px-3 py-1">
                                        {{ $leader->top_score }} pts
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-muted text-center py-4">{{ __('ui.game.no_ranking') }}</p>
                            @endforelse
                        </div>

                        <div class="mt-5 flex flex-col gap-2">
                            <a
                                href="#leaderboard-komunitas"
                                class="w-full inline-flex items-center justify-center rounded-full bg-[#1E63E9] hover:bg-ink text-white font-bold text-xs py-2.5 transition-colors"
                            >
                                {{ app()->getLocale() === 'en' ? 'Open Full Leaderboard ↓' : 'Buka Leaderboard Lengkap ↓' }}
                            </a>
                            <a
                                href="#daftar-game"
                                class="w-full inline-flex items-center justify-center rounded-full bg-[#DBEAFE] hover:bg-sky hover:text-white text-sky font-bold text-xs py-2.5 transition-colors"
                            >
                                {{ __('ui.game.challenge_top') }}
                            </a>
                        </div>
                    </div>

                </aside>

            </div>

            <!-- papan leaderboard interaktif multi-kategori penuh di bagian bawah minigame hub -->
            <div id="leaderboard-komunitas" class="reveal mt-12">
                <x-leaderboard-board :datasets="$leaderboardData" />
            </div>
        </div>
    </section>

@endsection
