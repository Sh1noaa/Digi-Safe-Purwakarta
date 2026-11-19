@extends('layouts.app')

@section('title', 'Testimoni & Ulasan - Digi-Safe Purwakarta')

@section('content')

    @php
        $itemsJson = $testimonis->map(function ($t) {
            return [
                'id' => $t->id,
                'user_id' => $t->user_id,
                'profile_url' => (! $t->is_anonymous && $t->user) ? route('profile.show', $t->user->id) : null,
                'nama' => $t->nama_tampil,
                'inisial' => $t->inisial_tampil,
                'avatar' => $t->avatar_tampil,
                'border_url' => (! $t->is_anonymous && $t->user) ? $t->user->avatar_border_url : null,
                'pastel_class' => $t->user?->pastel_class ?: 'bg-[#DBEAFE] text-[#1E40AF]',
                'is_owner' => (! $t->is_anonymous && (bool) $t->user?->isOwner()),
                'is_anonymous' => (bool) $t->is_anonymous,
                'rating' => (int) ($t->rating ?: 5),
                'judul' => $t->judul,
                'cerita' => $t->cerita,
                'waktu' => $t->created_at ? $t->created_at->diffForHumans() : __('ui.forum.just_now'),
            ];
        })->values();
    @endphp

    <div
        x-data="testimoniPageApp(@js($itemsJson))"
        class="bg-white min-h-screen"
    >
        <!-- notifikasi sukses setelah kirim ulasan -->
        <div
            x-show="toastMessage !== ''"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-3"
            style="display: none;"
            class="fixed top-20 right-4 sm:right-6 z-[95] rounded-2xl bg-ink text-white border border-lime/40 px-4 py-3 text-xs font-semibold shadow-[0_14px_34px_rgba(15,23,42,0.22)] flex items-center gap-2.5 max-w-sm"
        >
            <span class="w-5 h-5 rounded-full bg-lime text-ink flex items-center justify-center shrink-0">
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
            </span>
            <span x-text="toastMessage"></span>
        </div>

        <!-- hero section minimalis dengan floating icons & depth-of-field blur -->
        <section class="relative bg-white pt-28 sm:pt-32 pb-12 overflow-hidden">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">

                <!-- kanvas ikon melayang & teks utama -->
                <div class="relative min-h-[380px] sm:min-h-[480px] lg:min-h-[540px] flex flex-col items-center justify-center text-center">

                    <!-- lapisan ikon melayang dengan animasi masuk halus (khusus md+ agar GPU HP tetap ringan) -->
                    <div class="reveal reveal-delay-1 hidden md:block absolute inset-0 pointer-events-none">
                    <!-- ikon pinggir luar (blur 4px untuk efek depth of field) -->
                    <div
                        style="filter: blur(4px);"
                        class="hidden md:flex absolute left-[3%] top-[22%] w-20 h-20 rounded-[22px] bg-amber-400 text-ink shadow-lg -rotate-12 items-center justify-center animate-float-slow pointer-events-none"
                    >
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </div>

                    <div
                        style="filter: blur(3.5px);"
                        class="hidden sm:flex absolute left-[19%] top-[10%] w-14 h-14 rounded-[18px] bg-gradient-to-tr from-amber-300 via-pink-500 to-purple-500 text-white shadow-md -rotate-12 items-center justify-center animate-float-delayed pointer-events-none"
                    >
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                            <circle cx="12" cy="12" r="4" />
                        </svg>
                    </div>

                    <div
                        style="filter: blur(4px);"
                        class="hidden md:flex absolute right-[3%] top-[24%] w-24 h-24 rounded-[24px] bg-ink text-white shadow-xl rotate-12 items-center justify-center animate-float-alt pointer-events-none"
                    >
                        <div class="w-10 h-10 border-[4px] border-white rotate-12 flex items-center justify-center">
                            <div class="w-2.5 h-2.5 bg-white"></div>
                        </div>
                    </div>

                    <div
                        style="filter: blur(2.5px);"
                        class="hidden sm:flex absolute right-[19%] top-[12%] w-14 h-14 rounded-[18px] bg-orange-500 text-white shadow-md rotate-12 items-center justify-center animate-float-slow pointer-events-none"
                    >
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>

                    <div
                        style="filter: blur(3px);"
                        class="hidden sm:flex absolute left-[20%] bottom-[14%] w-16 h-16 rounded-[18px] bg-ink text-emerald-400 shadow-lg rotate-[28deg] items-center justify-center animate-float-alt pointer-events-none"
                    >
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M5 9c4.5-1.5 9.5-1.5 14 0" />
                            <path d="M6.5 13c3.5-1 7.5-1 11 0" />
                            <path d="M8 17c2.5-.8 5.5-.8 8 0" />
                        </svg>
                    </div>

                    <div
                        style="filter: blur(3.5px);"
                        class="hidden sm:flex absolute right-[22%] bottom-[12%] w-12 h-12 rounded-full bg-rose-400 text-white shadow-md -rotate-12 items-center justify-center animate-float-delayed pointer-events-none"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </div>

                    <!-- ikon dekat tengah (tajam) -->
                    <div class="hidden sm:flex absolute left-[29%] top-[26%] w-9 h-9 rounded-xl bg-lime text-ink shadow-xs rotate-6 items-center justify-center pointer-events-none">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>

                    <div class="hidden sm:flex absolute left-[30%] top-[44%] w-8 h-8 rounded-lg bg-gray-100 text-ink shadow-2xs -rotate-12 items-center justify-center pointer-events-none">
                        <span class="font-mono font-bold text-[11px]">&gt;_</span>
                    </div>

                    <div
                        style="filter: blur(1px);"
                        class="hidden sm:flex absolute right-[22%] top-[42%] w-14 h-14 rounded-[18px] bg-sky text-white shadow-md rotate-12 items-center justify-center pointer-events-none"
                    >
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                    </div>
                    </div>

                    <!-- konten inti di tengah hero -->
                    <div class="relative z-10 max-w-2xl mx-auto px-4">
                        <div
                            class="reveal w-16 h-16 rounded-[20px] bg-sky text-white shadow-lg ring-4 ring-sky/15 flex items-center justify-center mx-auto mb-6 pointer-events-none"
                        >
                            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                        </div>

                        <h1 class="reveal font-heading font-extrabold text-3xl sm:text-5xl lg:text-[54px] text-ink tracking-[-0.03em] leading-[1.1]">
                            {{ __('ui.testi.hero_title') }}
                        </h1>

                        <p class="reveal reveal-delay-1 mt-4 text-xs sm:text-base text-muted max-w-md mx-auto leading-relaxed">
                            {{ __('ui.testi.hero_sub') }}
                        </p>

                        <!-- tombol utama "Tulis Ulasan" -->
                        <div class="reveal reveal-delay-2 mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                            <button
                                type="button"
                                @click="openModal()"
                                class="group inline-flex items-center gap-4 rounded-full bg-ink hover:bg-sky text-white border border-slate-700/40 pl-7 pr-3 py-3 sm:pl-9 sm:pr-3.5 sm:py-3.5 ring-4 ring-ink/10 hover:ring-sky/25 shadow-xl hover:scale-105 hover:-translate-y-1 active:scale-95 transition-all duration-300 cursor-pointer"
                            >
                                <div class="text-left">
                                    <span class="block text-[10px] font-bold uppercase tracking-[0.16em] text-lime group-hover:text-white/90 leading-none">
                                        {{ __('ui.testi.cta_kicker') }}
                                    </span>
                                    <span class="font-heading font-extrabold text-base sm:text-lg text-white leading-tight mt-1 block">
                                        {{ __('ui.testi.cta_btn') }}
                                    </span>
                                </div>

                                <span class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-lime text-ink flex items-center justify-center shrink-0 group-hover:rotate-12 transition-transform shadow-sm">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                    </svg>
                                </span>
                            </button>

                            <!-- tombol navigasi langsung ke daftar ulasan di bawah -->
                            <a
                                href="#ulasan-warga"
                                class="inline-flex items-center gap-2.5 rounded-full bg-[#F0F8FF] hover:bg-sky/15 border border-sky/25 text-ink font-heading font-bold text-xs sm:text-sm px-6 py-4 transition-all hover:-translate-y-0.5"
                            >
                                <span>{{ __('ui.testi.view_reviews') }}</span>
                                <span class="rounded-full bg-sky text-white text-[11px] font-extrabold px-2.5 py-0.5" x-text="items.length"></span>
                                <svg class="w-4 h-4 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <polyline points="19 12 12 19 5 12" />
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- jembatan navigasi bawah hero (seperti di forum) yang memberi tahu masih ada ulasan warga di bawah -->
                <div class="reveal reveal-delay-3 mt-4 flex flex-col items-center justify-center text-center">
                    <a
                        href="#ulasan-warga"
                        class="group inline-flex flex-col items-center gap-2 text-xs font-semibold text-muted hover:text-sky transition-colors"
                    >
                        <div class="inline-flex items-center gap-2 rounded-full bg-gray-50 border border-gray-200/80 px-4 py-2 shadow-2xs group-hover:border-sky/40 transition-colors">
                            <div class="flex items-center -space-x-1.5">
                                @forelse ($testimonis->take(3) as $heroRev)
                                    @if (! $heroRev->is_anonymous && $heroRev->avatar_tampil)
                                        <img src="{{ $heroRev->avatar_tampil }}" alt="{{ $heroRev->nama_tampil }}" class="w-6 h-6 rounded-full object-cover ring-2 ring-white">
                                    @else
                                        <span class="w-6 h-6 rounded-full {{ $heroRev->is_anonymous ? 'bg-gray-200 text-muted' : ($heroRev->user?->pastel_class ?: 'bg-sky text-white') }} text-[10px] font-bold flex items-center justify-center ring-2 ring-white">
                                            {{ $heroRev->inisial_tampil }}
                                        </span>
                                    @endif
                                @empty
                                    <span class="w-6 h-6 rounded-full bg-sky text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white">D</span>
                                @endforelse
                            </div>
                            <span class="text-ink font-bold" x-text="'Lihat ' + items.length + ' Ulasan Pengguna Lainnya di Bawah'"></span>
                        </div>

                        <span class="w-9 h-9 rounded-full bg-sky/10 text-sky flex items-center justify-center group-hover:translate-y-0.5 transition-transform mt-1">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </a>
                </div>

            </div>
        </section>

        <!-- bagian ulasan user: efek carousel 3d vertikal & auto-scroll sesuai referensi gambar kedua -->
        <section id="ulasan-warga" class="relative bg-[#F5F6F9] py-20 sm:py-28 overflow-hidden border-t border-gray-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="reveal flex flex-col md:flex-row md:items-end justify-between gap-6 max-w-4xl mx-auto mb-12">
                    <div>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">
                            <span class="text-sky">&bull;</span> {{ __('ui.testi.section_kicker') }}
                        </span>
                        <h2 class="mt-2 font-heading font-extrabold text-2xl sm:text-4xl text-ink tracking-tight">
                            {{ __('ui.testi.section_title') }}
                        </h2>
                    </div>

                    <!-- kontrol navigasi ulasan sebelumnya / berikutnya + pause -->
                    <div x-show="items.length > 0" class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="step(-1)"
                            class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-200 hover:border-ink text-ink text-xs font-bold px-4 py-2.5 shadow-2xs transition-colors cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="18 15 12 9 6 15" />
                            </svg>
                            <span>{{ __('ui.testi.prev') }}</span>
                        </button>

                        <span class="rounded-full bg-sky/10 text-sky font-heading font-extrabold text-xs px-3.5 py-2" x-text="(activeIndex + 1) + ' / ' + items.length"></span>

                        <button
                            type="button"
                            @click="step(1)"
                            class="inline-flex items-center gap-1.5 rounded-full bg-ink hover:bg-sky text-white text-xs font-bold px-4 py-2.5 shadow-2xs transition-colors cursor-pointer"
                        >
                            <span>{{ __('ui.testi.next') }}</span>
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            @click="userPaused = !userPaused"
                            :aria-pressed="userPaused"
                            :title="userPaused ? 'Putar otomatis' : 'Jeda otomatis'"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200 hover:border-sky text-ink text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                        >
                            <svg x-show="!userPaused" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>
                            <svg x-show="userPaused" style="display:none;" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                        </button>
                    </div>
                </div>

                <!-- state kosong jika belum ada testimoni disetujui -->
                <template x-if="items.length === 0">
                    <div class="rounded-[28px] bg-white border border-gray-100 p-12 text-center max-w-lg mx-auto shadow-2xs">
                        <div class="w-12 h-12 rounded-2xl bg-sky/10 text-sky flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                        </div>
                        <p class="font-heading font-bold text-base text-ink">
                            {{ __('ui.testi.no_reviews_title') }}
                        </p>
                        <p class="mt-1 text-xs text-muted">{{ __('ui.testi.no_reviews_desc') }}</p>
                        <button
                            type="button"
                            @click="openModal()"
                            class="mt-5 rounded-full bg-ink text-white text-xs font-bold px-6 py-2.5 cursor-pointer"
                        >
                            {{ __('ui.testi.cta_btn') }}
                        </button>
                    </div>
                </template>

                <!-- panggung fokus 3D vertikal (mendukung swipe sentuh di HP tanpa membajak scroll halaman) -->
                <div
                    x-show="items.length > 0"
                    @mouseenter="paused = true"
                    @mouseleave="paused = false"
                    @touchstart.passive="onTouchStart($event)"
                    @touchend.passive="onTouchEnd($event)"
                    class="reveal reveal-delay-1 relative max-w-4xl mx-auto py-6 flex flex-col items-center justify-center select-none"
                >
                    <!-- item -2 -->
                    <div
                        @click="step(-2)"
                        style="filter: blur(2px);"
                        class="cursor-pointer transition-all duration-200 opacity-20 scale-75 -rotate-6 -translate-y-2 text-center max-w-lg px-4"
                    >
                        <p class="font-heading font-bold text-lg sm:text-2xl text-ink truncate" x-text="itemAtOffset(-2).cerita"></p>
                    </div>

                    <!-- item -1 -->
                    <div
                        @click="step(-1)"
                        style="filter: blur(0.8px);"
                        class="cursor-pointer transition-all duration-200 opacity-40 scale-90 -rotate-3 my-2 text-center max-w-xl px-4"
                    >
                        <p class="font-heading font-bold text-xl sm:text-3xl text-ink truncate" x-text="itemAtOffset(-1).cerita"></p>
                    </div>

                    <!-- kapsul utama di tengah layar -->
                    <div class="w-full max-w-3xl my-4 testi-pill-glass rounded-[36px] sm:rounded-full px-6 sm:px-10 py-6 sm:py-7 flex flex-col sm:flex-row items-center gap-5 sm:gap-7 transition-all duration-200">
                        <div class="relative shrink-0">
                            <template x-if="!currentItem().is_anonymous && currentItem().avatar">
                                <a :href="currentItem().profile_url || '#'" class="block">
                                    <img
                                        :src="currentItem().avatar"
                                        :alt="currentItem().nama"
                                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover ring-4 ring-sky/20 shadow-md hover:scale-105 transition-transform"
                                    >
                                </a>
                            </template>

                            <template x-if="currentItem().is_anonymous || !currentItem().avatar">
                                <a
                                    :href="currentItem().profile_url || '#'"
                                    :class="currentItem().is_anonymous ? 'bg-gray-200 text-muted pointer-events-none' : currentItem().pastel_class + ' hover:scale-105'"
                                    class="w-16 h-16 sm:w-20 sm:h-20 rounded-full flex items-center justify-center shadow-md transition-transform"
                                >
                                    <template x-if="currentItem().is_anonymous">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-3.5 rounded-full bg-gray-500"></span>
                                            <span class="w-2.5 h-3.5 rounded-full bg-gray-500"></span>
                                        </div>
                                    </template>
                                    <template x-if="!currentItem().is_anonymous">
                                        <span class="font-heading font-extrabold text-2xl" x-text="currentItem().inisial"></span>
                                    </template>
                                </a>
                            </template>

                            <template x-if="!currentItem().is_anonymous && currentItem().border_url">
                                <img :src="currentItem().border_url" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                            </template>
                        </div>

                        <div class="flex-1 text-center sm:text-left min-w-0">
                            <div class="flex flex-wrap items-center justify-center sm:justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-2">
                                    <template x-if="currentItem().profile_url">
                                        <a :href="currentItem().profile_url" class="font-heading font-bold text-sm text-ink hover:text-sky transition-colors" x-text="currentItem().nama"></a>
                                    </template>
                                    <template x-if="!currentItem().profile_url">
                                        <span class="font-heading font-bold text-sm text-ink" x-text="currentItem().nama"></span>
                                    </template>
                                    <span class="text-[11px] text-muted" x-text="'• ' + currentItem().waktu"></span>
                                </div>

                                <div class="flex items-center gap-0.5" aria-label="Rating Bintang">
                                    <template x-for="star in 5" :key="star">
                                        <svg
                                            class="w-4 h-4"
                                            :class="star <= currentItem().rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200'"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" />
                                        </svg>
                                    </template>
                                </div>
                            </div>

                            <p class="font-heading font-bold text-base sm:text-xl text-ink leading-snug line-clamp-3" x-text="currentItem().cerita"></p>
                        </div>
                    </div>

                    <!-- item +1 -->
                    <div
                        @click="step(1)"
                        style="filter: blur(0.8px);"
                        class="cursor-pointer transition-all duration-200 opacity-40 scale-90 rotate-3 my-2 text-center max-w-xl px-4"
                    >
                        <p class="font-heading font-bold text-xl sm:text-3xl text-ink truncate" x-text="itemAtOffset(1).cerita"></p>
                    </div>

                    <!-- item +2 -->
                    <div
                        @click="step(2)"
                        style="filter: blur(2px);"
                        class="cursor-pointer transition-all duration-200 opacity-20 scale-75 rotate-6 translate-y-2 text-center max-w-lg px-4"
                    >
                        <p class="font-heading font-bold text-lg sm:text-2xl text-ink truncate" x-text="itemAtOffset(2).cerita"></p>
                    </div>

                    <!-- indikator titik ulasan -->
                    <div class="mt-7 flex flex-wrap items-center justify-center gap-2">
                        <template x-for="(it, idx) in items" :key="it.id">
                            <button
                                type="button"
                                @click="activeIndex = idx"
                                class="h-2.5 rounded-full transition-all cursor-pointer"
                                :class="activeIndex === idx ? 'w-8 bg-sky' : 'w-2.5 bg-gray-300 hover:bg-gray-400'"
                                :aria-label="'Ulasan ' + (idx + 1)"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- daftar ulasan yang bisa di-scroll manual dengan cepat oleh user -->
                <div x-show="items.length > 0" class="mt-12 max-w-3xl mx-auto">
                    <div class="flex items-center justify-between mb-4 px-2">
                        <div class="flex items-center gap-2">
                            <span class="font-heading font-extrabold text-sm sm:text-base text-ink">{{ __('ui.testi.all_reviews_title') }}</span>
                            <span class="rounded-full bg-lime px-2.5 py-0.5 text-[11px] font-extrabold text-ink" x-text="items.length + ' ' + ( @js(app()->getLocale() === 'en') ? 'Reviews' : 'Ulasan')"></span>
                        </div>
                        <span class="text-[11px] font-semibold text-muted">{{ __('ui.testi.all_reviews_sub') }}</span>
                    </div>

                    <div class="max-h-[440px] overflow-y-auto pr-1.5 space-y-3.5 scroll-smooth">
                        <template x-for="(ulasan, uIdx) in items" :key="ulasan.id">
                            <div
                                @click="activeIndex = uIdx"
                                :class="activeIndex === uIdx ? 'border-sky ring-2 ring-sky/15 bg-[#FAFDFF]' : 'border-gray-100 bg-white hover:border-sky/40'"
                                class="rounded-3xl border p-5 shadow-2xs flex items-start gap-4 transition-all cursor-pointer"
                            >
                                <div class="relative shrink-0">
                                    <template x-if="!ulasan.is_anonymous && ulasan.avatar">
                                        <a :href="ulasan.profile_url || '#'" @click.stop class="block">
                                            <img :src="ulasan.avatar" :alt="ulasan.nama" class="w-11 h-11 rounded-full object-cover hover:scale-105 transition-transform">
                                        </a>
                                    </template>
                                    <template x-if="ulasan.is_anonymous || !ulasan.avatar">
                                        <a
                                            :href="ulasan.profile_url || '#'"
                                            @click.stop
                                            class="w-11 h-11 rounded-full flex items-center justify-center font-heading font-bold text-xs transition-transform"
                                            :class="ulasan.is_anonymous ? 'bg-gray-100 text-muted pointer-events-none' : ulasan.pastel_class + ' hover:scale-105'"
                                        >
                                            <template x-if="ulasan.is_anonymous">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                                    <circle cx="12" cy="7" r="4" />
                                                </svg>
                                            </template>
                                            <template x-if="!ulasan.is_anonymous">
                                                <span x-text="ulasan.inisial"></span>
                                            </template>
                                        </a>
                                    </template>
                                    <template x-if="!ulasan.is_anonymous && ulasan.border_url">
                                        <img :src="ulasan.border_url" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                                    </template>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <template x-if="ulasan.profile_url">
                                            <a :href="ulasan.profile_url" @click.stop class="font-heading font-bold text-xs text-ink hover:text-sky transition-colors" x-text="ulasan.nama"></a>
                                        </template>
                                        <template x-if="!ulasan.profile_url">
                                            <span class="font-heading font-bold text-xs text-ink" x-text="ulasan.nama"></span>
                                        </template>
                                        <span class="text-[10px] text-muted" x-text="ulasan.waktu"></span>
                                    </div>

                                    <div class="flex items-center gap-0.5 my-1">
                                        <template x-for="s in 5" :key="s">
                                            <svg
                                                class="w-3.5 h-3.5"
                                                :class="s <= ulasan.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200'"
                                                viewBox="0 0 24 24"
                                            >
                                                <path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" />
                                            </svg>
                                        </template>
                                    </div>

                                    <p class="text-xs text-muted leading-relaxed line-clamp-2" x-text="ulasan.cerita"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </section>

        <!-- form modal "Tulis Ulasan" -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(0, 0, 0, 0.5);"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            @keydown.escape.window="open = false"
        >
            <div
                @click.outside="open = false"
                class="w-full max-w-lg rounded-[28px] bg-white p-6 sm:p-8 shadow-neutral border border-gray-100"
            >
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-heading font-bold text-lg text-ink">{{ __('ui.testi.create_title') }}</h3>
                        <p class="text-xs text-muted mt-0.5">{{ __('ui.testi.create_sub') }}</p>
                    </div>
                    <button
                        type="button"
                        @click="open = false"
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center cursor-pointer"
                        aria-label="{{ __('ui.forum.close_modal') }}"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @auth
                    <!-- form ulasan -->
                    <form @submit.prevent="submitUlasan()" class="mt-5 space-y-5">

                        <!-- input rating bintang interaktif -->
                        <div>
                            <label class="block text-xs font-bold text-ink mb-2">{{ __('ui.testi.rating_label') }}</label>
                            <div class="flex items-center gap-2">
                                <template x-for="star in 5" :key="star">
                                    <button
                                        type="button"
                                        @mouseenter="hoverRating = star"
                                        @mouseleave="hoverRating = 0"
                                        @click="form.rating = star"
                                        class="p-1 focus:outline-none transition-transform hover:scale-110 cursor-pointer"
                                        :aria-label="'Star ' + star"
                                    >
                                        <svg
                                            class="w-8 h-8 transition-colors"
                                            :class="star <= (hoverRating || form.rating) ? 'text-amber-400 fill-amber-400 stroke-amber-500' : 'text-gray-200 fill-gray-200 stroke-gray-300'"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                        >
                                            <path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" />
                                        </svg>
                                    </button>
                                </template>
                                <span class="ml-2 text-xs font-bold text-muted" x-text="(hoverRating || form.rating) + ' / 5'"></span>
                            </div>
                        </div>

                        <!-- textarea cerita -->
                        <div>
                            <label for="cerita" class="block text-xs font-bold text-ink mb-1.5">{{ __('ui.testi.story_label') }}</label>
                            <textarea
                                id="cerita"
                                x-model="form.cerita"
                                rows="4"
                                required
                                placeholder="{{ __('ui.testi.story_placeholder') }}"
                                class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-xs text-ink placeholder:text-gray-400 focus:outline-none focus:border-sky resize-none"
                            ></textarea>
                        </div>

                        <!-- checkbox sembunyikan nama (anonim) -->
                        <div class="flex items-center gap-2.5">
                            <input
                                id="is_anonymous"
                                type="checkbox"
                                x-model="form.is_anonymous"
                                class="w-4 h-4 rounded-sm border-gray-300 text-sky focus:ring-sky cursor-pointer"
                            >
                            <label for="is_anonymous" class="text-xs font-medium text-ink/85 cursor-pointer">
                                {{ __('ui.testi.anonymous') }}
                            </label>
                        </div>

                        <div x-show="errorMsg !== ''" class="text-xs font-semibold text-red-600" x-text="errorMsg"></div>

                        <!-- tombol kirim ulasan -->
                        <div class="pt-2 flex items-center justify-end gap-3">
                            <button
                                type="button"
                                @click="open = false"
                                class="rounded-full border border-gray-200 px-5 py-2.5 text-xs font-semibold text-muted hover:text-ink cursor-pointer"
                            >
                                {{ __('ui.testi.cancel') }}
                            </button>
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="rounded-full bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-7 py-3 shadow-sm hover:-translate-y-0.5 transition-all cursor-pointer disabled:opacity-50"
                            >
                                <span x-text="submitting ? @js(__('ui.testi.saving')) : @js(__('ui.testi.publish_review'))"></span>
                            </button>
                        </div>
                    </form>
                @else
                    <!-- ajakan login bagi tamu agar tidak terkena error 401 -->
                    <div class="mt-5 rounded-2xl bg-[#F8FAFC] border border-gray-200 p-6 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-sky/10 text-sky flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </div>
                        <h4 class="font-heading font-extrabold text-base text-ink">
                            {{ app()->getLocale() === 'en' ? 'Sign in to write a review' : 'Masuk terlebih dahulu untuk menulis ulasan' }}
                        </h4>
                        <p class="text-xs text-muted leading-relaxed">
                            {{ app()->getLocale() === 'en'
                                ? 'To keep community reviews authentic and spam-free, please sign in with your Digi-Safe account.'
                                : 'Untuk menjaga keaslian ulasan komunitas dan mencegah spam, silakan masuk menggunakan akun Digi-Safe Anda.' }}
                        </p>
                        <div class="pt-2 flex flex-wrap items-center justify-center gap-2.5">
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-full bg-sky hover:bg-sky-light text-white font-heading font-bold text-xs px-6 py-2.5 transition-colors">
                                {{ app()->getLocale() === 'en' ? 'Sign In' : 'Masuk Sekarang' }}
                            </a>
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center justify-center rounded-full bg-white border border-gray-300 hover:border-ink text-ink font-heading font-bold text-xs px-6 py-2.5 transition-colors">
                                {{ app()->getLocale() === 'en' ? 'Create Account' : 'Daftar Akun' }}
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

    </div>

    <script>
        function testimoniPageApp(initialItems) {
            return {
                items: initialItems || [],
                activeIndex: 0,
                paused: false,
                userPaused: false,
                touchStartX: 0,
                touchStartY: 0,
                open: false,
                hoverRating: 0,
                submitting: false,
                toastMessage: '',
                errorMsg: '',
                form: {
                    rating: 5,
                    cerita: '',
                    is_anonymous: false
                },

                init() {
                    const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (reduced) {
                        this.userPaused = true;
                    }
                    setInterval(() => {
                        if (document.visibilityState === 'visible' && !this.paused && !this.userPaused && !this.open && this.items.length > 1) {
                            this.step(1);
                        }
                    }, 4200);
                },

                onTouchStart(e) {
                    const t = e.touches && e.touches[0];
                    if (!t) return;
                    this.touchStartX = t.clientX;
                    this.touchStartY = t.clientY;
                },

                onTouchEnd(e) {
                    const t = e.changedTouches && e.changedTouches[0];
                    if (!t || this.items.length <= 1) return;
                    const dx = t.clientX - this.touchStartX;
                    const dy = t.clientY - this.touchStartY;
                    if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.2) {
                        this.step(dx < 0 ? 1 : -1);
                    }
                },

                currentItem() {
                    if (this.items.length === 0) {
                        return { nama: '', inisial: 'A', avatar: null, is_anonymous: true, rating: 5, judul: '', cerita: '', waktu: '' };
                    }
                    return this.items[this.activeIndex] || this.items[0];
                },

                itemAtOffset(offset) {
                    if (this.items.length === 0) return { judul: '', cerita: '' };
                    const len = this.items.length;
                    const idx = (((this.activeIndex + offset) % len) + len) % len;
                    return this.items[idx];
                },

                step(delta) {
                    if (this.items.length === 0) return;
                    const len = this.items.length;
                    this.activeIndex = (((this.activeIndex + delta) % len) + len) % len;
                },

                openModal() {
                    this.errorMsg = '';
                    this.open = true;
                },

                submitUlasan() {
                    if (!this.form.cerita.trim() || this.submitting) return;
                    this.submitting = true;
                    this.errorMsg = '';

                    fetch('{{ route('testimoni.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(async res => {
                        if (res.status === 401) {
                            this.errorMsg = @js(app()->getLocale() === 'en' ? 'Please sign in first to publish a review.' : 'Silakan masuk terlebih dahulu untuk menerbitkan ulasan.');
                            return null;
                        }
                        if (res.status === 429) {
                            this.errorMsg = @js(app()->getLocale() === 'en' ? 'Too many submissions. Please wait a minute.' : 'Terlalu banyak pengiriman. Mohon tunggu sebentar.');
                            return null;
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (!data) return;
                        if (data.success) {
                            if (data.item) {
                                this.items.unshift(data.item);
                                this.activeIndex = 0;
                            }
                            this.open = false;
                            this.form = { rating: 5, cerita: '', is_anonymous: false };
                            this.toastMessage = data.message || @js(__('ui.testi.publish_review'));
                            setTimeout(() => {
                                this.toastMessage = '';
                            }, 5000);
                        } else {
                            this.errorMsg = data.message || @js(__('ui.testi.error_empty'));
                        }
                    })
                    .catch(() => {
                        this.errorMsg = @js(__('ui.testi.error_network'));
                    })
                    .finally(() => {
                        this.submitting = false;
                    });
                }
            };
        }
    </script>

@endsection
