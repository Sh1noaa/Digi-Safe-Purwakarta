@extends('layouts.app')

@section('title', 'Forum Komunitas - Digi-Safe Purwakarta')

@section('content')

    <div x-data="forumKomunitasApp(@js($forumsData), @js($sort), @js(Auth::check()))" class="min-h-screen bg-white">
        <!-- hero section atas & transisi kurva cekung -->
        <section class="relative bg-white pt-28 sm:pt-32 overflow-hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-20">

                <!-- judul utama -->
                <h1
                    class="reveal font-heading font-extrabold text-3xl sm:text-5xl lg:text-[54px] text-ink leading-[1.1] tracking-[-0.03em] max-w-2xl mx-auto">
                    {{ __('ui.forum.hero_title_1') }} <span
                        class="text-sky">{{ __('ui.forum.hero_title_2') }}</span><br>{{ __('ui.forum.hero_title_3') }}
                </h1>

                <!-- subteks -->
                <p class="reveal reveal-delay-1 mt-4 text-xs sm:text-sm text-muted max-w-lg mx-auto leading-relaxed">
                    {{ __('ui.forum.hero_sub') }}
                </p>

                <!-- tombol aksi hero -->
                <div class="reveal reveal-delay-2 mt-7 flex flex-wrap items-center justify-center gap-3">
                    @auth
                        <button type="button" @click="createOpen = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-6 py-3 shadow-sm hover:-translate-y-0.5 transition-all cursor-pointer">
                            <span>{{ __('ui.forum.create_btn') }}</span>
                        </button>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-6 py-3 shadow-sm hover:-translate-y-0.5 transition-all">
                            <span>{{ __('ui.forum.start_asking') }}</span>
                        </a>
                    @endauth

                    <a href="#daftar-diskusi"
                        class="inline-flex items-center gap-2 rounded-xl bg-white border border-gray-200 hover:border-ink text-ink font-heading font-semibold text-xs px-5 py-3 shadow-2xs transition-colors">
                        <svg class="w-4 h-4 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <span>{{ __('ui.forum.explore_btn') }}</span>
                    </a>
                </div>
            </div>

            <!-- area visual kurva cekung biru -->
            <div class="relative mt-6 sm:mt-8">

                <!-- 2 ilustrasi 3D di kiri & kanan (sirine di kanan, kartu identitas di kiri) dengan jarak cukup lebar -->
                <div class="hidden lg:block max-w-6xl mx-auto relative pointer-events-none select-none">
                    <div class="absolute -left-8 xl:-left-16 -top-10 w-52 h-52 xl:w-60 xl:h-60 -rotate-6 z-20">
                        <img src="{{ asset('images/forum-left-3d.png') }}" alt="Ilustrasi Verifikasi Identitas Forum"
                            class="w-full h-full object-contain">
                    </div>

                    <div class="absolute -right-8 xl:-right-16 -top-10 w-52 h-52 xl:w-60 xl:h-60 rotate-6 z-20">
                        <img src="{{ asset('images/forum-right-3d.png') }}" alt="Ilustrasi Pengumuman & Diskusi Forum"
                            class="w-full h-full object-contain">
                    </div>
                </div>

                <!-- garis vertikal tengah & logo Digi-Safe di puncak kurva -->
                <div class="hidden md:flex relative z-20 flex-col items-center">
                    <div class="w-1 h-16 bg-gradient-to-b from-transparent via-sky/40 to-sky"></div>
                    <div
                        class="w-16 h-16 rounded-[20px] bg-white border border-sky/20 shadow-lg ring-4 ring-white flex items-center justify-center p-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Digi-Safe Purwakarta"
                            class="w-full h-full object-contain">
                    </div>
                </div>

                <!-- kurva cekung biru muncul tepat dari bawah logo tanpa melebihi lebar kotak logo -->
                <div class="relative md:-mt-7">
                    <svg class="hidden md:block w-full h-[450px]" viewBox="0 0 1440 450" preserveAspectRatio="none"
                        aria-hidden="true">
                        <defs>
                            <linearGradient id="forumCurveGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop class="forum-curve-stop-1" offset="0%" stop-color="#1565C0" />
                                <stop class="forum-curve-stop-2" offset="62%" stop-color="#1E88E5" />
                                <stop class="forum-curve-stop-3" offset="88%" stop-color="#64B5F6" />
                                <stop class="forum-curve-end-stop" offset="100%" stop-color="#F0F8FF" />
                            </linearGradient>
                        </defs>
                        <path d="M 702 0 C 690 70, 300 155, 0 215 L 0 450 L 1440 450 L 1440 215 C 1140 155, 750 70, 738 0 Z"
                            fill="url(#forumCurveGrad)" />
                    </svg>

                    <!-- konten teks & dock ikon kategori -->
                    <div
                        class="relative md:absolute md:inset-x-0 md:bottom-8 z-20 flex flex-col items-center text-center px-4 pt-6 pb-8 md:pt-0 md:pb-0">
                        <h2
                            class="font-heading font-extrabold text-2xl sm:text-3xl md:text-4xl lg:text-[42px] leading-tight text-sky md:text-white md:drop-shadow-xs max-w-3xl">
                            {{ __('ui.forum.hub_title') }}
                        </h2>
                        <p class="mt-2.5 text-xs sm:text-sm text-sky/85 md:text-white/95 max-w-lg leading-relaxed">
                            {{ __('ui.forum.hub_sub') }}
                        </p>

                        <span
                            class="mt-4 inline-block rounded-lg bg-white border border-sky/20 text-ink font-bold text-[10px] px-3 py-1 shadow-xs">
                            {{ __('ui.forum.filter_label') }}
                        </span>

                        <!-- barisan ikon kategori bergaya referensi -->
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:gap-2.5">
                            <template x-for="cat in categories" :key="cat.name">
                                <button type="button" @click="activeCategory = cat.name" :class="activeCategory === cat.name
                                            ? 'scale-105 md:scale-110 ring-2 ring-sky md:ring-3 md:ring-white shadow-md ' + cat.bg
                                            : 'opacity-90 hover:opacity-100 hover:scale-105 ' + cat.bg"
                                    class="px-3.5 py-2 md:py-2.5 rounded-2xl border border-white/20 text-white font-heading font-bold text-[11px] flex items-center gap-1.5 transition-all cursor-pointer">
                                    <span x-text="cat.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- section daftar forum di bawah kurva -->
        <section id="daftar-diskusi" class="bg-[#F0F8FF] pt-8 pb-24 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">

                <!-- container utama daftar diskusi -->
                <div class="rounded-[28px] bg-white/90 border border-sky/10 p-5 sm:p-8 shadow-neutral">

                    <!-- bar filter tabs ("Terbaru" & "Paling Membantu") + pencarian + buat diskusi -->
                    <div
                        class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="setSort('terbaru')" :class="activeSort === 'terbaru'
                                        ? 'bg-sky text-white border-sky shadow-xs'
                                        : 'bg-[#F0F8FF] border-gray-200/80 text-ink/75 hover:text-ink'"
                                class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round">
                                    <circle cx="12" cy="12" r="9" />
                                    <polyline points="12 7 12 12 15 15" />
                                </svg>
                                <span>{{ __('ui.forum.sort_newest') }}</span>
                            </button>

                            <button type="button" @click="setSort('membantu')" :class="activeSort === 'membantu'
                                        ? 'bg-sky text-white border-sky shadow-xs'
                                        : 'bg-[#F0F8FF] border-gray-200/80 text-ink/75 hover:text-ink'"
                                class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                </svg>
                                <span>{{ __('ui.forum.sort_helpful') }}</span>
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            <span
                                class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-[10px] font-bold text-emerald-700"
                                title="{{ __('ui.forum.live_realtime_title') }}">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ __('ui.forum.live_realtime') }}</span>
                            </span>

                            <div class="relative flex-1 sm:w-60">
                                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8" />
                                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                </svg>
                                <input type="text" x-model="searchQuery"
                                    placeholder="{{ __('ui.forum.search_placeholder') }}"
                                    class="w-full rounded-full bg-[#F7F9FC] border border-gray-200 pl-9 pr-4 py-2 text-xs text-ink placeholder:text-gray-400 focus:outline-none focus:border-sky">
                            </div>

                            @auth
                                <button type="button" @click="createOpen = true"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-lime text-ink font-bold text-xs px-4 py-2 shadow-2xs hover:-translate-y-0.5 transition-transform cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.4" stroke-linecap="round">
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    <span>{{ __('ui.forum.create_btn') }}</span>
                                </button>
                            @endauth
                        </div>
                    </div>

                    <!-- empty state jika belum ada data forum -->
                    <div x-show="filteredForums().length === 0" style="display: none;" class="py-16 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-sky/10 text-sky flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-base text-ink">
                            {{ __('ui.forum.empty_title') }}
                        </h3>
                        <p class="mt-1 text-xs text-muted max-w-sm mx-auto">
                            {{ __('ui.forum.empty_sub') }}
                        </p>
                        <div class="mt-5">
                            @auth
                                <button type="button" @click="createOpen = true"
                                    class="inline-flex items-center gap-2 rounded-full bg-sky text-white font-bold text-xs px-5 py-2.5 cursor-pointer">
                                    <span>{{ __('ui.forum.first_discussion_btn') }}</span>
                                </button>
                            @else
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center gap-2 rounded-full bg-lime text-ink font-bold text-xs px-5 py-2.5">
                                    <span>{{ __('ui.forum.login_to_ask_btn') }}</span>
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- grid card forum (1 kolom di mobile, 2 kolom di desktop) -->
                    <div x-show="filteredForums().length > 0" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <template x-for="item in filteredForums()" :key="item.id">
                            <article :class="item.is_starred
                                        ? 'border-sky ring-2 ring-lime/70 bg-[#FAFDFF]'
                                        : 'border-gray-100 bg-white hover:border-sky/30'"
                                class="rounded-[22px] border p-6 flex flex-col justify-between shadow-2xs hover:-translate-y-1 hover:shadow-neutral transition-all duration-300">
                                <div>
                                    <!-- badge disematkan & badge most helpful + kontrol Pin khusus Admin/Owner -->
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2"
                                        x-show="item.is_starred || item.has_most_helpful || isAdmin">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <template x-if="item.is_starred">
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-ink text-lime px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider">
                                                    <svg class="w-3 h-3 text-lime" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M12 17v5" />
                                                        <path
                                                            d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z" />
                                                    </svg>
                                                    <span>{{ __('ui.forum.pinned') }}</span>
                                                </span>
                                            </template>

                                            <template x-if="item.has_most_helpful">
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-800 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider">
                                                    <svg class="w-3 h-3 text-emerald-700" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path
                                                            d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                                    </svg>
                                                    <span>{{ __('ui.forum.most_helpful') }}</span>
                                                </span>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <template x-if="item.is_starred && !isAdmin">
                                                <span
                                                    class="text-[10px] font-semibold text-sky">{{ __('ui.forum.community_priority') }}</span>
                                            </template>

                                            <template x-if="isAdmin">
                                                <button type="button" @click.stop="togglePinForum(item)"
                                                    :disabled="pinningId === item.id"
                                                    :class="item.is_starred ? 'bg-ink text-lime border-ink' : 'bg-gray-50 hover:bg-sky/10 text-muted hover:text-sky border-gray-200'"
                                                    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-heading font-bold transition-all cursor-pointer disabled:opacity-50"
                                                    :title="item.is_starred ? 'Lepas Pin Diskusi' : 'Pin Diskusi ke Atas'">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M12 17v5" />
                                                        <path
                                                            d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z" />
                                                    </svg>
                                                    <span x-text="item.is_starred ? 'Lepas Pin' : 'Pin Forum'"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- header card: avatar + nama user + waktu + kategori -->
                                    <div class="flex items-center justify-between gap-3">
                                        <a :href="item.author_profile_url || '#'"
                                            class="flex items-center gap-2.5 min-w-0 group/author">
                                            <span class="relative inline-flex w-9 h-9 shrink-0">
                                                <template x-if="item.author_avatar">
                                                    <img :src="item.author_avatar" :alt="item.author_name"
                                                        class="w-full h-full rounded-full object-cover ring-2 ring-sky/20 group-hover/author:scale-105 transition-transform">
                                                </template>
                                                <template x-if="!item.author_avatar">
                                                    <span
                                                        :class="item.author_is_owner ? 'bg-ink text-lime' : (item.author_pastel || 'bg-[#DBEAFE] text-[#1E40AF]')"
                                                        class="w-full h-full rounded-full font-heading font-extrabold text-xs flex items-center justify-center group-hover/author:scale-105 transition-transform"
                                                        x-text="item.author_initial"></span>
                                                </template>
                                                <template x-if="item.author_border_url">
                                                    <img :src="item.author_border_url" alt="" aria-hidden="true"
                                                        class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                                                </template>
                                            </span>

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span
                                                        class="font-heading font-bold text-xs text-ink group-hover/author:text-sky transition-colors truncate"
                                                        x-text="item.author_name"></span>
                                                    <template x-if="item.author_is_owner">
                                                        <span
                                                            class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-2 py-0.5 text-[9px] font-extrabold">
                                                            <svg class="w-2.5 h-2.5 text-lime" viewBox="0 0 24 24"
                                                                fill="currentColor">
                                                                <path
                                                                    d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                                            </svg>
                                                            <span>Owner</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!item.author_is_owner && item.author_is_admin">
                                                        <span
                                                            class="rounded-full bg-lime px-2 py-0.5 text-[9px] font-extrabold text-ink">Admin</span>
                                                    </template>
                                                </div>
                                                <span class="text-[10px] text-muted block" x-text="item.waktu"></span>
                                            </div>
                                        </a>

                                        <!-- tag kategori -->
                                        <span
                                            class="shrink-0 rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider"
                                            :class="badgeColor(item.kategori)" x-text="categoryLabel(item.kategori)"></span>
                                    </div>

                                    <!-- mengatasi blade <script> {{  }} -->
                                    <div class="mt-4">
                                        <h3 @click="openModal(item)" <!-- mencegah ->
                                            class="font-heading font-bold text-base text-ink hover:text-sky
                                            transition-colors cursor-pointer leading-snug"
                                            x-text="item.judul"
                                            ></h3>
                                        <p class="mt-2 text-xs text-muted leading-relaxed line-clamp-2"
                                            x-text="item.pertanyaan"></p>
                                    </div>
                                </div>

                                <!-- footer card: tombol "Pendapat?" + total jawaban + vote -->
                                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2.5 text-[11px] font-semibold text-muted">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-sky" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                            </svg>
                                            <span
                                                x-text="item.jawaban_count + ' ' + @js(__('ui.forum.answers_count'))"></span>
                                        </span>

                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[10px] font-bold">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path
                                                    d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                            </svg>
                                            <span x-text="item.total_up || 0"></span>
                                        </span>

                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-red-50 text-red-600 px-2 py-0.5 text-[10px] font-bold">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path
                                                    d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17" />
                                            </svg>
                                            <span x-text="item.total_down || 0"></span>
                                        </span>
                                    </div>

                                    <button type="button" @click="openModal(item)"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-[#F0F8FF] hover:bg-sky text-sky hover:text-white font-heading font-bold text-xs px-4 py-2 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                                        </svg>
                                        <span>{{ __('ui.forum.opinion_btn') }}</span>
                                    </button>
                                </div>
                            </article>
                        </template>
                    </div>

                </div>

            </div>
        </section>

        <!-- pop-up modal "Pendapat?" -->
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(0, 0, 0, 0.5);"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            @keydown.escape.window="open = false">
            <div @click.outside="open = false"
                class="w-full max-w-2xl max-h-[88vh] rounded-[28px] bg-white/95 backdrop-blur-md border border-white shadow-neutral flex flex-col overflow-hidden">
                <!-- header modal -->
                <div class="p-6 border-b border-gray-100 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                :class="selectedForum ? badgeColor(selectedForum.kategori) : ''"
                                x-text="selectedForum ? categoryLabel(selectedForum.kategori) : ''"></span>
                            <span class="text-[11px] text-muted"
                                x-text="selectedForum ? (@js(__('ui.forum.by_author')) + selectedForum.author_name + ' • ' + selectedForum.waktu) : ''"></span>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-ink leading-snug"
                            x-text="selectedForum ? selectedForum.judul : ''"></h3>
                    </div>

                    <!-- tombol aksi pemilik (edit/hapus) & close (X) -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <template x-if="selectedForum && selectedForum.can_manage">
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="startEditForum()"
                                    class="inline-flex items-center gap-1 rounded-full bg-sky/10 hover:bg-sky hover:text-white text-sky px-3 py-1.5 text-[11px] font-heading font-bold transition-colors cursor-pointer"
                                    title="Edit Diskusi">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                    </svg>
                                    <span
                                        class="hidden sm:inline">{{ app()->getLocale() === 'en' ? 'Edit' : 'Edit' }}</span>
                                </button>
                                <button type="button" @click="deleteSelectedForum()" :disabled="deletingForum"
                                    class="inline-flex items-center gap-1 rounded-full bg-red-50 hover:bg-red-600 hover:text-white text-red-600 px-3 py-1.5 text-[11px] font-heading font-bold transition-colors cursor-pointer disabled:opacity-50"
                                    title="Hapus Diskusi">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                        <path d="M10 11v6M14 11v6" />
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                    </svg>
                                    <span
                                        class="hidden sm:inline">{{ app()->getLocale() === 'en' ? 'Delete' : 'Hapus' }}</span>
                                </button>
                            </div>
                        </template>

                        <button type="button" @click="open = false; editingForum = false"
                            class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center shrink-0 transition-colors cursor-pointer"
                            aria-label="{{ __('ui.forum.close_modal') }}">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M18 6L6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- isi pertanyaan & daftar jawaban -->
                <div class="p-6 overflow-y-auto space-y-5 flex-1">
                    <!-- form edit inline untuk pemilik diskusi -->
                    <form x-show="editingForum" style="display: none;" @submit.prevent="saveEditForum()"
                        class="rounded-2xl bg-sky/5 border border-sky/25 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-heading font-bold text-sky">{{ app()->getLocale() === 'en' ? 'Edit Your Discussion' : 'Edit Diskusi Anda' }}</span>
                            <button type="button" @click="editingForum = false"
                                class="text-[11px] font-semibold text-muted hover:text-ink cursor-pointer">{{ app()->getLocale() === 'en' ? 'Cancel' : 'Batal' }}</button>
                        </div>
                        <input type="text" x-model="editForm.judul" required maxlength="255"
                            class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs font-semibold text-ink focus:outline-none focus:border-sky">
                        <select x-model="editForm.kategori"
                            class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs font-semibold text-ink focus:outline-none focus:border-sky">
                            <option value="Phishing">Phishing</option>
                            <option value="Privasi">{{ __('ui.forum.cat_privacy') }}</option>
                            <option value="Malware">Malware</option>
                            <option value="Penipuan">{{ __('ui.forum.cat_fraud') }}</option>
                            <option value="Tanya Pakar">{{ __('ui.forum.ask_expert') }}</option>
                        </select>
                        <textarea x-model="editForm.pertanyaan" rows="3" required maxlength="3000"
                            class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-sky resize-none"></textarea>
                        <div class="flex justify-end gap-2">
                            <button type="submit" :disabled="savingEdit"
                                class="rounded-full bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-4 py-2 transition-colors cursor-pointer disabled:opacity-50"
                                x-text="savingEdit ? '...' : @js(app()->getLocale() === 'en' ? 'Save Changes' : 'Simpan Perubahan')"></button>
                        </div>
                    </form>

                    <!-- box pertanyaan lengkap -->
                    <div x-show="!editingForum"
                        class="rounded-2xl bg-[#F0F8FF] border border-sky/15 p-4 text-xs sm:text-sm text-ink leading-relaxed"
                        x-text="selectedForum ? selectedForum.pertanyaan : ''"></div>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-muted"
                            x-text="selectedForum ? (@js(__('ui.forum.replies_list')) + ' (' + selectedForum.jawaban.length + ')') : @js(__('ui.forum.replies_list'))">
                        </h4>
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ __('ui.forum.vote_realtime_label') }}</span>
                        </span>
                    </div>

                    <!-- notifikasi jika user tamu mencoba vote -->
                    <div x-show="voteNotice !== ''" style="display: none;"
                        class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-2.5 text-xs font-semibold text-amber-800 flex items-center justify-between gap-3">
                        <span x-text="voteNotice"></span>
                        <a href="{{ route('login') }}"
                            class="shrink-0 rounded-full bg-ink text-lime px-3 py-1 text-[10px] font-extrabold">{{ __('ui.forum.login_short') }}</a>
                    </div>

                    <!-- empty state jika belum ada jawaban -->
                    <template x-if="selectedForum && selectedForum.jawaban.length === 0">
                        <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center">
                            <div
                                class="w-11 h-11 rounded-full bg-gray-100 text-muted flex items-center justify-center mx-auto mb-2.5">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-ink">
                                {{ __('ui.forum.empty_replies') }}
                            </p>
                        </div>
                    </template>

                    <!-- list jawaban dengan tombol vote (1x per user tiap pesan) & label Most Helpful -->
                    <div class="space-y-3" x-show="selectedForum && selectedForum.jawaban.length > 0">
                        <template x-for="ans in (selectedForum ? selectedForum.jawaban : [])" :key="ans.id">
                            <div class="rounded-2xl p-4 border transition-all duration-200"
                                :class="ans.is_most_helpful
                                        ? 'bg-emerald-50/75 border-emerald-400 ring-2 ring-emerald-200/80 shadow-xs'
                                        : ((ans.is_owner || ans.is_admin) ? 'bg-lime/15 border-lime' : 'bg-gray-50/80 border-gray-100')">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a :href="ans.user_profile_url || '#'" class="flex items-center gap-2 group/ans">
                                            <span class="relative inline-flex w-7 h-7 shrink-0">
                                                <template x-if="ans.user_avatar">
                                                    <img :src="ans.user_avatar" :alt="ans.user_name"
                                                        class="w-full h-full rounded-full object-cover group-hover/ans:scale-105 transition-transform">
                                                </template>
                                                <template x-if="!ans.user_avatar">
                                                    <span
                                                        :class="ans.is_owner ? 'bg-ink text-lime' : (ans.user_pastel || 'bg-[#DBEAFE] text-[#1E40AF]')"
                                                        class="w-full h-full rounded-full font-heading font-bold text-[10px] flex items-center justify-center group-hover/ans:scale-105 transition-transform"
                                                        x-text="ans.user_initial"></span>
                                                </template>
                                                <template x-if="ans.user_border_url">
                                                    <img :src="ans.user_border_url" alt="" aria-hidden="true"
                                                        class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                                                </template>
                                            </span>

                                            <span
                                                class="font-heading font-bold text-xs text-ink group-hover/ans:text-sky transition-colors"
                                                x-text="ans.user_name"></span>

                                            <template x-if="ans.is_owner">
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-ink text-lime font-extrabold text-[10px] px-2.5 py-0.5">
                                                    <svg class="w-2.5 h-2.5 text-lime" viewBox="0 0 24 24"
                                                        fill="currentColor">
                                                        <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                                    </svg>
                                                    <span>Owner</span>
                                                </span>
                                            </template>

                                            <template x-if="!ans.is_owner && ans.is_admin">
                                                <span
                                                    class="rounded-full bg-lime text-ink font-extrabold text-[10px] px-2.5 py-0.5">
                                                    Admin
                                                </span>
                                            </template>
                                        </a>

                                        <template x-if="ans.is_most_helpful">
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-emerald-600 text-white font-heading font-extrabold text-[10px] px-2.5 py-0.5 shadow-2xs">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
                                                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
                                                    <path d="M4 22h16" />
                                                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22" />
                                                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22" />
                                                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
                                                </svg>
                                                <span>Most Helpful</span>
                                            </span>
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] text-muted" x-text="ans.waktu"></span>
                                        <template x-if="ans.can_manage">
                                            <button type="button" @click="deleteJawaban(ans)"
                                                class="inline-flex items-center justify-center w-6 h-6 rounded-full text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors cursor-pointer"
                                                title="{{ app()->getLocale() === 'en' ? 'Delete reply' : 'Hapus balasan' }}">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                    <path d="M10 11v6M14 11v6" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <p class="text-xs text-ink/90 leading-relaxed pl-9" x-text="ans.jawaban"></p>

                                <!-- Bar Vote Emoji Thumbs Up & Thumbs Down (Maks 1x vote per user per pesan) -->
                                <div class="mt-3 pl-9 flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="voteJawaban(ans, 'up')"
                                            :disabled="votingId === ans.id"
                                            :class="ans.user_vote === 'up'
                                                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs scale-[1.03]'
                                                    : 'bg-white hover:bg-emerald-50 text-ink border-gray-200 hover:border-emerald-300'"
                                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-heading font-bold transition-all cursor-pointer disabled:opacity-60"
                                            title="{{ __('ui.forum.vote_up_title') }}">
                                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path
                                                    d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                            </svg>
                                            <span>{{ __('ui.forum.helpful_btn') }}</span>
                                            <span
                                                :class="ans.user_vote === 'up' ? 'bg-white/25 text-white' : 'bg-emerald-50 text-emerald-700'"
                                                class="rounded-full px-1.5 py-0.2 text-[10px] font-extrabold"
                                                x-text="ans.up_count || 0"></span>
                                        </button>

                                        <button type="button" @click="voteJawaban(ans, 'down')"
                                            :disabled="votingId === ans.id"
                                            :class="ans.user_vote === 'down'
                                                    ? 'bg-red-600 text-white border-red-600 shadow-2xs scale-[1.03]'
                                                    : 'bg-white hover:bg-red-50 text-ink border-gray-200 hover:border-red-300'"
                                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-heading font-bold transition-all cursor-pointer disabled:opacity-60"
                                            title="{{ __('ui.forum.vote_down_title') }}">
                                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path
                                                    d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17" />
                                            </svg>
                                            <span
                                                :class="ans.user_vote === 'down' ? 'bg-white/25 text-white' : 'bg-red-50 text-red-600'"
                                                class="rounded-full px-1.5 py-0.2 text-[10px] font-extrabold"
                                                x-text="ans.down_count || 0"></span>
                                        </button>
                                    </div>

                                    <span x-show="ans.user_vote" style="display: none;"
                                        class="text-[10px] font-semibold text-muted"
                                        x-text="ans.user_vote === 'up' ? @js(__('ui.forum.vote_rated_up')) : @js(__('ui.forum.vote_rated_down'))"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- form input jawaban di bawah modal -->
                <div class="p-5 bg-gray-50 border-t border-gray-100">
                    @auth
                        <form @submit.prevent="submitJawaban()">
                            <textarea x-model="newJawaban" rows="2" required
                                placeholder="{{ __('ui.forum.write_reply_placeholder') }}"
                                class="w-full rounded-2xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink placeholder:text-gray-400 focus:outline-none focus:border-sky resize-none"></textarea>

                            <div class="mt-2.5 flex items-center justify-between gap-3">
                                <span class="text-[11px] text-emerald-600 font-medium" x-text="feedbackMessage"></span>
                                <button type="submit" :disabled="submitting"
                                    class="inline-flex items-center gap-2 rounded-full bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-5 py-2.5 shadow-xs transition-all cursor-pointer disabled:opacity-50">
                                    <span
                                        x-text="submitting ? @js(__('ui.forum.sending')) : @js(__('ui.forum.send_reply'))"></span>
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="22" y1="2" x2="11" y2="13" />
                                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs font-medium text-muted">{{ __('ui.forum.login_to_reply') }}</span>
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-full bg-lime text-ink font-bold text-xs px-5 py-2">
                                {{ __('ui.forum.login_now') }}
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <!-- modal buat diskusi baru -->
        @auth
            <div x-show="createOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                style="display: none; background: rgba(0, 0, 0, 0.5);"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
                @keydown.escape.window="createOpen = false">
                <div @click.outside="createOpen = false"
                    class="w-full max-w-lg rounded-[28px] bg-white p-6 sm:p-8 shadow-neutral border border-gray-100">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="font-heading font-bold text-lg text-ink">{{ __('ui.forum.create_modal_title') }}</h3>
                        <button type="button" @click="createOpen = false"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M18 6L6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitForumBaru()" class="mt-5 space-y-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-ink mb-1.5">{{ __('ui.forum.question_title_label') }}</label>
                            <input type="text" x-model="newForum.judul" required
                                placeholder="{{ __('ui.forum.question_title_placeholder') }}"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-ink mb-1.5">{{ __('ui.forum.topic_category_label') }}</label>
                            <x-custom-select model="newForum.kategori" :options="[
                    ['value' => 'Phishing', 'label' => 'Phishing', 'dot' => '#F59E0B'],
                    ['value' => 'Privasi', 'label' => __('ui.forum.cat_privacy'), 'dot' => '#8B5CF6'],
                    ['value' => 'Malware', 'label' => 'Malware', 'dot' => '#EF4444'],
                    ['value' => 'Penipuan', 'label' => __('ui.forum.cat_fraud'), 'dot' => '#0EA5E9'],
                    ['value' => 'Tanya Pakar', 'label' => __('ui.forum.ask_expert'), 'dot' => '#10B981'],
                ]" />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-ink mb-1.5">{{ __('ui.forum.question_detail_label') }}</label>
                            <textarea x-model="newForum.pertanyaan" rows="4" required
                                placeholder="{{ __('ui.forum.question_detail_placeholder') }}"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky resize-none"></textarea>
                        </div>

                        <div class="pt-2 flex justify-end gap-2.5">
                            <button type="button" @click="createOpen = false"
                                class="rounded-full border border-gray-200 px-5 py-2.5 text-xs font-semibold text-muted hover:text-ink cursor-pointer">
                                {{ __('ui.forum.cancel') }}
                            </button>
                            <button type="submit" :disabled="creating"
                                class="rounded-full bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-6 py-2.5 shadow-xs cursor-pointer disabled:opacity-50">
                                <span
                                    x-text="creating ? @js(__('ui.forum.saving')) : @js(__('ui.forum.publish_discussion'))"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endauth

    </div>

    <script>
        function forumKomunitasApp(initialForums, initialSort, isAuthenticated) {
            return {
                forums: initialForums || [],
                activeSort: initialSort || 'terbaru',
                isAuthenticated: !!isAuthenticated,
                isAdmin: {{ (Auth::check() && Auth::user()->hasAdminAccess()) ? 'true' : 'false' }},
                pinningId: null,
                activeCategory: 'semua',
                searchQuery: '',
                open: false,
                selectedForum: null,
                newJawaban: '',
                submitting: false,
                votingId: null,
                voteNotice: '',
                feedbackMessage: '',
                createOpen: false,
                creating: false,
                editingForum: false,
                savingEdit: false,
                deletingForum: false,
                editForm: {
                    judul: '',
                    kategori: 'Phishing',
                    pertanyaan: ''
                },
                liveTimer: null,
                newForum: {
                    judul: '',
                    kategori: 'Phishing',
                    pertanyaan: ''
                },
                categories: [
                    { name: 'semua', label: @js(__('ui.forum.all_topics')), bg: 'bg-ink' },
                    { name: 'phishing', label: 'Phishing', bg: 'bg-red-500' },
                    { name: 'privasi', label: @js(__('ui.forum.cat_privacy')), bg: 'bg-purple-deep' },
                    { name: 'malware', label: 'Malware', bg: 'bg-amber-500' },
                    { name: 'penipuan', label: @js(__('ui.forum.cat_fraud')), bg: 'bg-emerald-600' },
                    { name: 'tanya pakar', label: @js(__('ui.forum.ask_expert')), bg: 'bg-sky' }
                ],

                categoryLabel(kat) {
                    const key = (kat || '').toLowerCase();
                    if (key === 'privasi') return @js(__('ui.forum.cat_privacy'));
                    if (key === 'penipuan') return @js(__('ui.forum.cat_fraud'));
                    if (key === 'tanya pakar') return @js(__('ui.forum.ask_expert'));
                    return kat || 'General';
                },

                init() {
                    // Polling sinkronisasi setiap 12 detik saat tab aktif agar server tetap ringan
                    this.liveTimer = setInterval(() => {
                        if (document.visibilityState === 'visible') {
                            this.refreshLiveData();
                        }
                    }, 12000);
                },

                async togglePinForum(item) {
                    if (!this.isAdmin || !item || this.pinningId === item.id) return;
                    this.pinningId = item.id;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(`/admin/forum/${item.id}/star`, {
                            method: 'PATCH',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            item.is_starred = Boolean(data.is_starred);
                            this.refreshLiveData();
                        }
                    } finally {
                        this.pinningId = null;
                    }
                },

                startEditForum() {
                    if (!this.selectedForum || !this.selectedForum.can_manage) return;
                    this.editForm = {
                        judul: this.selectedForum.judul || '',
                        kategori: this.selectedForum.kategori || 'Phishing',
                        pertanyaan: this.selectedForum.pertanyaan || ''
                    };
                    this.editingForum = true;
                },

                async saveEditForum() {
                    if (!this.selectedForum || this.savingEdit) return;
                    this.savingEdit = true;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(`/forum/${this.selectedForum.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify(this.editForm)
                        });
                        const data = await res.json();
                        if (res.ok && data.success && data.forum) {
                            this.selectedForum = data.forum;
                            const idx = this.forums.findIndex(f => f.id === data.forum.id);
                            if (idx !== -1) this.forums[idx] = data.forum;
                            this.editingForum = false;
                            this.feedbackMessage = data.message || '';
                            if (window.digisafeToast && data.message) window.digisafeToast(data.message);
                        }
                    } finally {
                        this.savingEdit = false;
                    }
                },

                async deleteSelectedForum() {
                    if (!this.selectedForum || this.deletingForum) return;
                    const ok = window.confirm(@js(app()->getLocale() === 'en' ? 'Delete this discussion permanently?' : 'Hapus diskusi ini secara permanen?'));
                    if (!ok) return;
                    this.deletingForum = true;
                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const targetId = this.selectedForum.id;
                        const res = await fetch(`/forum/${targetId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.forums = this.forums.filter(f => f.id !== targetId);
                            this.selectedForum = null;
                            this.open = false;
                            this.editingForum = false;
                            if (window.digisafeToast && data.message) window.digisafeToast(data.message);
                        }
                    } finally {
                        this.deletingForum = false;
                    }
                },

                async deleteJawaban(ans) {
                    if (!ans || !ans.can_manage) return;
                    const ok = window.confirm(@js(app()->getLocale() === 'en' ? 'Delete this reply?' : 'Hapus balasan ini?'));
                    if (!ok) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch(`/forum/jawaban/${ans.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success && data.forum) {
                        this.selectedForum = data.forum;
                        const idx = this.forums.findIndex(f => f.id === data.forum.id);
                        if (idx !== -1) this.forums[idx] = data.forum;
                        this.feedbackMessage = data.message || '';
                        if (window.digisafeToast && data.message) window.digisafeToast(data.message);
                    }
                },

                refreshLiveData() {
                    fetch('{{ route('forum.live-data') }}?sort=' + encodeURIComponent(this.activeSort), {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(res => res.ok ? res.json() : null)
                        .then(data => {
                            if (!data || !data.success || !Array.isArray(data.forums)) return;

                            const openId = this.selectedForum ? this.selectedForum.id : null;
                            this.forums = data.forums;

                            if (openId !== null) {
                                const updated = this.forums.find(f => f.id === openId);
                                if (updated) {
                                    this.selectedForum = updated;
                                }
                            }
                        })
                        .catch(() => { });
                },

                setSort(sortType) {
                    this.activeSort = sortType;
                    const url = new URL(window.location);
                    url.searchParams.set('sort', sortType);
                    window.history.replaceState({}, '', url);
                },

                filteredForums() {
                    let list = this.forums.filter(item => {
                        const catMatch = this.activeCategory === 'semua'
                            || (item.kategori || '').toLowerCase().replace('-', ' ') === this.activeCategory;
                        const q = this.searchQuery.trim().toLowerCase();
                        const searchMatch = q === ''
                            || (item.judul || '').toLowerCase().includes(q)
                            || (item.pertanyaan || '').toLowerCase().includes(q)
                            || (item.kategori || '').toLowerCase().includes(q);
                        return catMatch && searchMatch;
                    });

                    if (this.activeSort === 'membantu') {
                        return [...list].sort((a, b) =>
                            ((b.is_starred ? 1 : 0) - (a.is_starred ? 1 : 0))
                            || ((b.helpful_score || 0) - (a.helpful_score || 0))
                            || ((b.total_up || 0) - (a.total_up || 0))
                            || (b.jawaban_count - a.jawaban_count)
                            || (b.created_ts - a.created_ts)
                        );
                    }

                    return [...list].sort((a, b) => ((b.is_starred ? 1 : 0) - (a.is_starred ? 1 : 0)) || (b.created_ts - a.created_ts) || (b.id - a.id));
                },

                badgeColor(kategori) {
                    const k = (kategori || '').toLowerCase();
                    if (k.includes('phishing')) return 'bg-red-50 text-red-600';
                    if (k.includes('privasi')) return 'bg-purple-soft/60 text-purple-deep';
                    if (k.includes('malware')) return 'bg-amber-50 text-amber-700';
                    if (k.includes('penipuan')) return 'bg-emerald-50 text-emerald-700';
                    return 'bg-sky/10 text-sky';
                },

                // pop up jawaban diskusi
                openModal(forumItem) {
                    this.selectedForum = forumItem;
                    this.newJawaban = '';
                    this.voteNotice = '';
                    this.feedbackMessage = '';
                    this.open = true;
                    this.refreshLiveData();
                },

                // sinkronkan forum ter-update ke daftar utama & modal yang sedang terbuka
                applyUpdatedForum(updatedForum) {
                    if (!updatedForum) return;
                    const idx = this.forums.findIndex(f => f.id === updatedForum.id);
                    if (idx !== -1) {
                        this.forums.splice(idx, 1, updatedForum);
                    } else {
                        this.forums.unshift(updatedForum);
                    }
                    if (this.selectedForum && this.selectedForum.id === updatedForum.id) {
                        this.selectedForum = updatedForum;
                    }
                },

                // voting thumbsup baik (up) atau buruk (down) max 1x per user tiap pesan
                voteJawaban(ans, voteType) {
                    if (!this.isAuthenticated) {
                        this.voteNotice = @js(__('ui.forum.vote_login_required'));
                        return;
                    }
                    if (this.votingId === ans.id) return;

                    this.voteNotice = '';
                    this.votingId = ans.id;

                    fetch('/forum/jawaban/' + ans.id + '/vote', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            vote_type: voteType
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.success && data.forum) {
                                this.applyUpdatedForum(data.forum);
                            }
                        })
                        .finally(() => {
                            this.votingId = null;
                        });
                },

                // simpan jawaban baru via ajax
                submitJawaban() {
                    if (!this.selectedForum || !this.newJawaban.trim() || this.submitting) return;
                    this.submitting = true;
                    this.feedbackMessage = '';

                    fetch('/forum/' + this.selectedForum.id + '/jawaban', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            jawaban: this.newJawaban
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.forum) {
                                this.applyUpdatedForum(data.forum);
                                this.newJawaban = '';
                                this.feedbackMessage = data.message || @js(__('ui.forum.sent_fallback'));
                            } else if (data.success && data.jawaban) {
                                this.selectedForum.jawaban.push(data.jawaban);
                                this.selectedForum.jawaban_count = this.selectedForum.jawaban.length;
                                this.newJawaban = '';
                                this.feedbackMessage = data.message || @js(__('ui.forum.sent_fallback'));
                            }
                        })
                        .finally(() => {
                            this.submitting = false;
                        });
                },

                // simpan diskusi baru via ajax
                submitForumBaru() {
                    if (this.creating) return;
                    this.creating = true;

                    fetch('{{ route('forum.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.newForum)
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.forum) {
                                this.forums.unshift(data.forum);
                                this.newForum = { judul: '', kategori: 'Phishing', pertanyaan: '' };
                                this.createOpen = false;
                            }
                        })
                        .finally(() => {
                            this.creating = false;
                        });
                }
            };
        }
    </script>

@endsection