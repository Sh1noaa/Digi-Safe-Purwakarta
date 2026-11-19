@extends('layouts.app')

@section('title', 'Admin Panel - Digi-Safe Purwakarta')

@section('content')
    @php
        $activeTab = match (true) {
            request()->routeIs('admin.minigame.*') => 'minigame',
            request()->routeIs('admin.forum.*') => 'forum',
            request()->routeIs('admin.user.*') => 'user',
            default => 'edukasi',
        };
        $analytics = $adminStats['analytics'] ?? \App\Services\AdminAnalyticsService::build();
        $userGrowth = $analytics['user_growth'] ?? [];
        $monthlyUsers = $userGrowth['monthly'] ?? [];
        $dailyUsers = $userGrowth['daily'] ?? [];
        $testiRatings = $analytics['testimoni_ratings'] ?? [];
        $starRows = $testiRatings['stars'] ?? [];
        $monthlyTestiSummary = $testiRatings['monthly_summary'] ?? [];
        $dailyTestiSummary = $testiRatings['daily_summary'] ?? [];
    @endphp

    <!-- kanvas utama #F8FAFC dengan pola kotak grid tipis -->
    <section
        x-data="{
            guideModalOpen: false,
            analyticsPeriod: 'monthly',
            analyticsExpanded: window.innerWidth >= 768,
            confirmModal: { open: false, title: 'Konfirmasi Tindakan', message: '', confirmLabel: 'Ya, Hapus', form: null }
        }"
        @admin-confirm.window="confirmModal = {
            open: true,
            title: $event.detail.title || 'Konfirmasi Hapus',
            message: $event.detail.message || 'Tindakan ini tidak dapat dibatalkan.',
            confirmLabel: $event.detail.confirmLabel || 'Ya, Hapus',
            form: $event.detail.form
        }"
        class="admin-grid-canvas min-h-screen bg-[#F8FAFC] pt-24 sm:pt-28 pb-20 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-7xl mx-auto">

            <!-- header atas & navigasi top tabs di tengah -->
            <div class="flex flex-col items-center text-center mb-8">
                <div class="flex flex-wrap items-center justify-center gap-2.5 mb-3">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-3.5 py-1.5 shadow-2xs">
                        @if (Auth::user()->isOwner())
                            <span class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                <svg class="w-3 h-3 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                </svg>
                                <span>Owner Mode</span>
                            </span>
                        @else
                            <span class="rounded-full bg-lime text-ink px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                Admin Mode
                            </span>
                        @endif
                        <span class="text-xs font-semibold text-ink">Pusat Kendali Digi-Safe Purwakarta</span>
                    </div>

                    <a
                        href="{{ route('admin.report.xlsx') }}"
                        class="inline-flex items-center gap-2 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-heading font-bold text-xs px-4 py-2 shadow-xs hover:-translate-y-0.5 transition-all"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        <span>Download Laporan XLSX</span>
                    </a>
                </div>

                <!-- navigasi tab tengah atas -->
                <div class="w-full max-w-3xl rounded-2xl bg-white border border-gray-200/90 p-1.5 shadow-neutral grid grid-cols-2 sm:flex sm:flex-nowrap items-center justify-center gap-1.5">
                    <a
                        href="{{ route('admin.edukasi.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-heading transition-all {{ $activeTab === 'edukasi' ? 'bg-sky/10 text-sky font-bold border-b-2 border-sky shadow-2xs' : 'text-muted hover:text-ink hover:bg-gray-50 font-semibold' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                        </svg>
                        <span>Manajemen Edukasi</span>
                        @if (($adminStats['pending_edukasi_requests'] ?? 0) > 0)
                            <span class="rounded-full bg-amber-500 text-white px-1.5 py-0.5 text-[9px] font-extrabold">
                                {{ $adminStats['pending_edukasi_requests'] }}
                            </span>
                        @endif
                    </a>

                    <a
                        href="{{ route('admin.minigame.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-heading transition-all {{ $activeTab === 'minigame' ? 'bg-sky/10 text-sky font-bold border-b-2 border-sky shadow-2xs' : 'text-muted hover:text-ink hover:bg-gray-50 font-semibold' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="6" width="20" height="12" rx="2" />
                            <path d="M6 12h4m-2-2v4m7-1h.01m2.99-2h.01" />
                        </svg>
                        <span>Soal Minigame</span>
                    </a>

                    <a
                        href="{{ route('admin.forum.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-heading transition-all {{ $activeTab === 'forum' ? 'bg-sky/10 text-sky font-bold border-b-2 border-sky shadow-2xs' : 'text-muted hover:text-ink hover:bg-gray-50 font-semibold' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        <span>Moderasi Forum</span>
                    </a>

                    <a
                        href="{{ route('admin.user.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-heading transition-all {{ $activeTab === 'user' ? 'bg-sky/10 text-sky font-bold border-b-2 border-sky shadow-2xs' : 'text-muted hover:text-ink hover:bg-gray-50 font-semibold' }}"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <span>Hak Akses User</span>
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 px-5 py-3.5 text-xs text-red-700 shadow-2xs">
                    <p class="font-bold mb-1">Mohon periksa kembali input Anda:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- panel diagram analitik: tren pertumbuhan user (menaik/menurun) & rating testimoni (bintang 5-1) -->
            <div class="mb-6 rounded-[28px] bg-white border border-gray-200/80 shadow-neutral overflow-hidden">
                <!-- toolbar header analitik -->
                <div class="px-5 sm:px-7 py-4 bg-[#F8FAFC] border-b border-gray-200/75 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-sky text-white flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                <polyline points="17 6 23 6 23 12" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="font-heading font-extrabold text-sm sm:text-base text-ink">
                                Diagram Tren Pertumbuhan User &amp; Rating Testimoni
                            </h2>
                            <p class="text-[11px] text-muted">
                                Pantau apakah pendaftar baru sedang menaik atau menurun serta rincian kenaikan ulasan Bintang 5, 4, 3, 2, dan 1.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- pemilih rentang periode analitik -->
                        <div class="inline-flex rounded-xl bg-white border border-gray-200 p-1 gap-1" role="group" aria-label="Filter periode diagram">
                            <button
                                type="button"
                                @click="analyticsPeriod = 'monthly'"
                                :aria-pressed="analyticsPeriod === 'monthly'"
                                :class="analyticsPeriod === 'monthly' ? 'bg-ink text-lime font-bold shadow-2xs' : 'text-muted hover:text-ink font-semibold'"
                                class="rounded-lg px-3 py-1.5 text-[11px] font-heading transition-all cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky"
                            >
                                6 Bulan Terakhir
                            </button>
                            <button
                                type="button"
                                @click="analyticsPeriod = 'daily'"
                                :aria-pressed="analyticsPeriod === 'daily'"
                                :class="analyticsPeriod === 'daily' ? 'bg-ink text-lime font-bold shadow-2xs' : 'text-muted hover:text-ink font-semibold'"
                                class="rounded-lg px-3 py-1.5 text-[11px] font-heading transition-all cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky"
                            >
                                7 Hari Terakhir
                            </button>
                        </div>

                        <button
                            type="button"
                            @click="analyticsExpanded = !analyticsExpanded"
                            :aria-expanded="analyticsExpanded"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-white border border-gray-200 hover:border-gray-300 px-3 py-2 text-[11px] font-heading font-bold text-ink transition-colors cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky"
                        >
                            <span x-text="analyticsExpanded ? 'Sembunyikan Diagram' : 'Tampilkan Diagram'">Sembunyikan Diagram</span>
                            <svg
                                class="w-3.5 h-3.5 transition-transform duration-200"
                                :class="analyticsExpanded ? 'rotate-180' : ''"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- isi 2 diagram berdampingan -->
                <div x-show="analyticsExpanded" x-transition.opacity.duration.200ms class="p-5 sm:p-7">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

                        <!-- DIAGRAM 1 (7 KOLOM): TREN PERTUMBUHAN USER (MENAIK / MENURUN) -->
                        <div class="lg:col-span-7 rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-5 flex flex-col justify-between">
                            <div>
                                <!-- header kartu diagram user -->
                                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                                    <div>
                                        <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider text-sky block">
                                            INDIKATOR PERTUMBUHAN PENGGUNA
                                        </span>
                                        <h3 class="font-heading font-extrabold text-sm sm:text-base text-ink mt-0.5">
                                            Apakah Pendaftar User Menaik atau Menurun?
                                        </h3>
                                    </div>

                                    <!-- badge status tren bulanan -->
                                    <div x-show="analyticsPeriod === 'monthly'">
                                        @php
                                            $mDir = $monthlyUsers['direction'] ?? 'flat';
                                            $mDelta = (int) ($monthlyUsers['delta'] ?? 0);
                                            $mDeltaSign = $mDelta > 0 ? '+' . $mDelta : (string) $mDelta;
                                        @endphp
                                        @if ($mDir === 'up')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-300/70 px-3 py-1 text-xs font-heading font-extrabold">
                                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                                    <polyline points="17 6 23 6 23 12" />
                                                </svg>
                                                <span>Tren Menaik ({{ $mDeltaSign }} user)</span>
                                            </span>
                                        @elseif ($mDir === 'down')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 text-rose-700 border border-rose-300/70 px-3 py-1 text-xs font-heading font-extrabold">
                                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" />
                                                    <polyline points="17 18 23 18 23 12" />
                                                </svg>
                                                <span>Tren Menurun ({{ $mDeltaSign }} user)</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white text-ink border border-gray-200 px-3 py-1 text-xs font-heading font-extrabold">
                                                <svg class="w-3.5 h-3.5 shrink-0 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                    <polyline points="12 5 19 12 12 19" />
                                                </svg>
                                                <span>Tren Stabil (0 selisih)</span>
                                            </span>
                                        @endif
                                    </div>

                                    <!-- badge status tren 7 hari -->
                                    <div x-show="analyticsPeriod === 'daily'" style="display: none;">
                                        @php
                                            $dDir = $dailyUsers['direction'] ?? 'flat';
                                            $dDelta = (int) ($dailyUsers['delta'] ?? 0);
                                            $dDeltaSign = $dDelta > 0 ? '+' . $dDelta : (string) $dDelta;
                                        @endphp
                                        @if ($dDir === 'up')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-300/70 px-3 py-1 text-xs font-heading font-extrabold">
                                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                                    <polyline points="17 6 23 6 23 12" />
                                                </svg>
                                                <span>Tren Menaik ({{ $dDeltaSign }} user)</span>
                                            </span>
                                        @elseif ($dDir === 'down')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 text-rose-700 border border-rose-300/70 px-3 py-1 text-xs font-heading font-extrabold">
                                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" />
                                                    <polyline points="17 18 23 18 23 12" />
                                                </svg>
                                                <span>Tren Menurun ({{ $dDeltaSign }} user)</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white text-ink border border-gray-200 px-3 py-1 text-xs font-heading font-extrabold">
                                                <svg class="w-3.5 h-3.5 shrink-0 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                    <polyline points="12 5 19 12 12 19" />
                                                </svg>
                                                <span>Tren Stabil (0 selisih)</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- legenda singkat -->
                                <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-muted mb-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-ink">
                                            <span class="w-2.5 h-2.5 rounded-xs bg-sky inline-block"></span>
                                            <span>Pendaftar Baru</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-ink">
                                            <span class="w-3 h-0.5 bg-emerald-600 inline-block"></span>
                                            <span>Total Akumulasi ({{ $userGrowth['total_users'] ?? 0 }} User)</span>
                                        </span>
                                    </div>
                                    <span x-show="analyticsPeriod === 'monthly'" class="font-semibold">
                                        Perbandingan: {{ $monthlyUsers['comparison_label'] ?? 'Bulan Ini vs Bulan Lalu' }}
                                    </span>
                                    <span x-show="analyticsPeriod === 'daily'" style="display: none;" class="font-semibold">
                                        Perbandingan: {{ $dailyUsers['comparison_label'] ?? '7 Hari Ini vs 7 Hari Lalu' }}
                                    </span>
                                </div>

                                @if (($userGrowth['total_users'] ?? 0) === 0)
                                    <div class="rounded-xl bg-white border border-dashed border-gray-200 p-8 text-center">
                                        <p class="font-heading font-bold text-xs text-ink">Belum ada data pendaftar pengguna.</p>
                                        <p class="text-[11px] text-muted mt-1">Grafik tren pertumbuhan akan otomatis terisi saat warga mendaftar akun.</p>
                                    </div>
                                @else
                                    <!-- SVG CHART MODE 1: 6 BULAN TERAKHIR -->
                                    <div x-show="analyticsPeriod === 'monthly'" class="rounded-xl bg-white border border-gray-200/80 p-3 sm:p-4">
                                        <svg
                                            viewBox="0 0 530 165"
                                            class="w-full h-44 sm:h-48 overflow-visible"
                                            role="img"
                                            aria-label="Diagram garis dan batang pertumbuhan pengguna 6 bulan terakhir"
                                        >
                                            <defs>
                                                <linearGradient id="userGrowthMonthlyFill" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="{{ ($monthlyUsers['direction'] ?? 'up') === 'down' ? '#E11D48' : '#0284C7' }}" stop-opacity="0.24" />
                                                    <stop offset="100%" stop-color="{{ ($monthlyUsers['direction'] ?? 'up') === 'down' ? '#E11D48' : '#0284C7' }}" stop-opacity="0.0" />
                                                </linearGradient>
                                            </defs>

                                            <!-- garis bantu horizontal -->
                                            <line x1="36" y1="26" x2="504" y2="26" stroke="#CBD5E1" stroke-dasharray="3 3" stroke-width="1" />
                                            <line x1="36" y1="77" x2="504" y2="77" stroke="#CBD5E1" stroke-dasharray="3 3" stroke-width="1" />
                                            <line x1="36" y1="128" x2="504" y2="128" stroke="#94A3B8" stroke-width="1.2" />

                                            <!-- label sumbu Y -->
                                            <text x="28" y="30" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold">{{ $monthlyUsers['max_new'] ?? 1 }}</text>
                                            <text x="28" y="81" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold">{{ max(1, (int) ceil(($monthlyUsers['max_new'] ?? 1) / 2)) }}</text>
                                            <text x="28" y="132" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold">0</text>

                                            <!-- area di bawah kurva pendaftar baru -->
                                            @if (! empty($monthlyUsers['area_new']))
                                                <polygon points="{{ $monthlyUsers['area_new'] }}" fill="url(#userGrowthMonthlyFill)" />
                                            @endif

                                            <!-- batang pendaftar baru per bulan -->
                                            @foreach (($monthlyUsers['buckets'] ?? []) as $b)
                                                <rect
                                                    x="{{ $b['x'] - 13 }}"
                                                    y="{{ $b['bar_y'] }}"
                                                    width="26"
                                                    height="{{ $b['bar_h'] }}"
                                                    rx="5"
                                                    fill="{{ $b['count'] > 0 ? 'rgba(2, 132, 199, 0.22)' : 'rgba(148, 163, 184, 0.25)' }}"
                                                    stroke="{{ $b['count'] > 0 ? '#0284C7' : 'none' }}"
                                                    stroke-width="1"
                                                >
                                                    <title>{{ $b['full_label'] }}: {{ $b['count'] }} pendaftar baru (Total: {{ $b['cumulative'] }} user)</title>
                                                </rect>
                                            @endforeach

                                            <!-- garis putus-putus total akumulasi user -->
                                            @if (! empty($monthlyUsers['polyline_cum']))
                                                <polyline
                                                    fill="none"
                                                    stroke="#059669"
                                                    stroke-width="2"
                                                    stroke-dasharray="4 3"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    points="{{ $monthlyUsers['polyline_cum'] }}"
                                                />
                                            @endif

                                            <!-- garis utama tren pendaftar baru -->
                                            @if (! empty($monthlyUsers['polyline_new']))
                                                <polyline
                                                    fill="none"
                                                    stroke="{{ ($monthlyUsers['direction'] ?? 'up') === 'down' ? '#E11D48' : '#0284C7' }}"
                                                    stroke-width="2.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    points="{{ $monthlyUsers['polyline_new'] }}"
                                                />
                                            @endif

                                            <!-- titik data & angka per bulan -->
                                            @foreach (($monthlyUsers['buckets'] ?? []) as $b)
                                                <circle
                                                    cx="{{ $b['x'] }}"
                                                    cy="{{ $b['y_new'] }}"
                                                    r="4.5"
                                                    fill="#FFFFFF"
                                                    stroke="{{ ($monthlyUsers['direction'] ?? 'up') === 'down' ? '#E11D48' : '#0284C7' }}"
                                                    stroke-width="2.5"
                                                >
                                                    <title>{{ $b['full_label'] }}: {{ $b['count'] }} user baru</title>
                                                </circle>
                                                <text
                                                    x="{{ $b['x'] }}"
                                                    y="{{ max(14, $b['y_new'] - 9) }}"
                                                    text-anchor="middle"
                                                    class="text-[10px] font-bold {{ $b['count'] > 0 ? 'fill-sky-700' : 'fill-slate-400' }}"
                                                >
                                                    +{{ $b['count'] }}
                                                </text>
                                                <text
                                                    x="{{ $b['x'] }}"
                                                    y="148"
                                                    text-anchor="middle"
                                                    class="text-[10px] font-bold fill-slate-600"
                                                >
                                                    {{ $b['label'] }}
                                                </text>
                                            @endforeach
                                        </svg>
                                    </div>

                                    <!-- SVG CHART MODE 2: 7 HARI TERAKHIR -->
                                    <div x-show="analyticsPeriod === 'daily'" style="display: none;" class="rounded-xl bg-white border border-gray-200/80 p-3 sm:p-4">
                                        <svg
                                            viewBox="0 0 530 165"
                                            class="w-full h-44 sm:h-48 overflow-visible"
                                            role="img"
                                            aria-label="Diagram garis dan batang pertumbuhan pengguna 7 hari terakhir"
                                        >
                                            <defs>
                                                <linearGradient id="userGrowthDailyFill" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="{{ ($dailyUsers['direction'] ?? 'up') === 'down' ? '#E11D48' : '#0284C7' }}" stop-opacity="0.24" />
                                                    <stop offset="100%" stop-color="{{ ($dailyUsers['direction'] ?? 'up') === 'down' ? '#E11D48' : '#0284C7' }}" stop-opacity="0.0" />
                                                </linearGradient>
                                            </defs>

                                            <line x1="36" y1="26" x2="504" y2="26" stroke="#CBD5E1" stroke-dasharray="3 3" stroke-width="1" />
                                            <line x1="36" y1="77" x2="504" y2="77" stroke="#CBD5E1" stroke-dasharray="3 3" stroke-width="1" />
                                            <line x1="36" y1="128" x2="504" y2="128" stroke="#94A3B8" stroke-width="1.2" />

                                            <text x="28" y="30" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold">{{ $dailyUsers['max_new'] ?? 1 }}</text>
                                            <text x="28" y="81" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold">{{ max(1, (int) ceil(($dailyUsers['max_new'] ?? 1) / 2)) }}</text>
                                            <text x="28" y="132" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold">0</text>

                                            @if (! empty($dailyUsers['area_new']))
                                                <polygon points="{{ $dailyUsers['area_new'] }}" fill="url(#userGrowthDailyFill)" />
                                            @endif

                                            @foreach (($dailyUsers['buckets'] ?? []) as $b)
                                                <rect
                                                    x="{{ $b['x'] - 11 }}"
                                                    y="{{ $b['bar_y'] }}"
                                                    width="22"
                                                    height="{{ $b['bar_h'] }}"
                                                    rx="5"
                                                    fill="{{ $b['count'] > 0 ? 'rgba(2, 132, 199, 0.22)' : 'rgba(148, 163, 184, 0.25)' }}"
                                                    stroke="{{ $b['count'] > 0 ? '#0284C7' : 'none' }}"
                                                    stroke-width="1"
                                                >
                                                    <title>{{ $b['full_label'] }}: {{ $b['count'] }} pendaftar baru (Total: {{ $b['cumulative'] }} user)</title>
                                                </rect>
                                            @endforeach

                                            @if (! empty($dailyUsers['polyline_cum']))
                                                <polyline
                                                    fill="none"
                                                    stroke="#059669"
                                                    stroke-width="2"
                                                    stroke-dasharray="4 3"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    points="{{ $dailyUsers['polyline_cum'] }}"
                                                />
                                            @endif

                                            @if (! empty($dailyUsers['polyline_new']))
                                                <polyline
                                                    fill="none"
                                                    stroke="{{ ($dailyUsers['direction'] ?? 'up') === 'down' ? '#E11D48' : '#0284C7' }}"
                                                    stroke-width="2.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    points="{{ $dailyUsers['polyline_new'] }}"
                                                />
                                            @endif

                                            @foreach (($dailyUsers['buckets'] ?? []) as $b)
                                                <circle
                                                    cx="{{ $b['x'] }}"
                                                    cy="{{ $b['y_new'] }}"
                                                    r="4.5"
                                                    fill="#FFFFFF"
                                                    stroke="{{ ($dailyUsers['direction'] ?? 'up') === 'down' ? '#E11D48' : '#0284C7' }}"
                                                    stroke-width="2.5"
                                                >
                                                    <title>{{ $b['full_label'] }}: {{ $b['count'] }} user baru</title>
                                                </circle>
                                                <text
                                                    x="{{ $b['x'] }}"
                                                    y="{{ max(14, $b['y_new'] - 9) }}"
                                                    text-anchor="middle"
                                                    class="text-[10px] font-bold {{ $b['count'] > 0 ? 'fill-sky-700' : 'fill-slate-400' }}"
                                                >
                                                    +{{ $b['count'] }}
                                                </text>
                                                <text
                                                    x="{{ $b['x'] }}"
                                                    y="148"
                                                    text-anchor="middle"
                                                    class="text-[10px] font-bold fill-slate-600"
                                                >
                                                    {{ $b['label'] }}
                                                </text>
                                            @endforeach
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- ringkasan 3 kartu metrik perbandingan user di bawah grafik -->
                            <div class="mt-4 grid grid-cols-3 gap-2.5">
                                <div class="rounded-xl bg-white border border-gray-200/80 p-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">
                                        <span x-show="analyticsPeriod === 'monthly'">Bulan Ini</span>
                                        <span x-show="analyticsPeriod === 'daily'" style="display: none;">7 Hari Ini</span>
                                    </span>
                                    <p class="font-heading font-extrabold text-base sm:text-lg text-ink mt-0.5">
                                        <span x-show="analyticsPeriod === 'monthly'">+{{ $monthlyUsers['current_count'] ?? 0 }}</span>
                                        <span x-show="analyticsPeriod === 'daily'" style="display: none;">+{{ $dailyUsers['current_count'] ?? 0 }}</span>
                                        <span class="text-[11px] font-semibold text-muted">user</span>
                                    </p>
                                </div>

                                <div class="rounded-xl bg-white border border-gray-200/80 p-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">
                                        <span x-show="analyticsPeriod === 'monthly'">Bulan Lalu</span>
                                        <span x-show="analyticsPeriod === 'daily'" style="display: none;">7 Hari Lalu</span>
                                    </span>
                                    <p class="font-heading font-extrabold text-base sm:text-lg text-ink mt-0.5">
                                        <span x-show="analyticsPeriod === 'monthly'">+{{ $monthlyUsers['previous_count'] ?? 0 }}</span>
                                        <span x-show="analyticsPeriod === 'daily'" style="display: none;">+{{ $dailyUsers['previous_count'] ?? 0 }}</span>
                                        <span class="text-[11px] font-semibold text-muted">user</span>
                                    </p>
                                </div>

                                <div class="rounded-xl bg-white border border-gray-200/80 p-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Status &amp; Total</span>
                                    <p class="font-heading font-extrabold text-xs sm:text-sm mt-1">
                                        <span x-show="analyticsPeriod === 'monthly'" class="{{ ($monthlyUsers['direction'] ?? 'flat') === 'up' ? 'text-emerald-700' : (($monthlyUsers['direction'] ?? 'flat') === 'down' ? 'text-rose-700' : 'text-ink') }}">
                                            {{ ($monthlyUsers['direction'] ?? 'flat') === 'up' ? 'Menaik' : (($monthlyUsers['direction'] ?? 'flat') === 'down' ? 'Menurun' : 'Stabil') }}
                                            ({{ ($monthlyUsers['delta'] ?? 0) > 0 ? '+' . $monthlyUsers['delta'] : ($monthlyUsers['delta'] ?? 0) }})
                                        </span>
                                        <span x-show="analyticsPeriod === 'daily'" style="display: none;" class="{{ ($dailyUsers['direction'] ?? 'flat') === 'up' ? 'text-emerald-700' : (($dailyUsers['direction'] ?? 'flat') === 'down' ? 'text-rose-700' : 'text-ink') }}">
                                            {{ ($dailyUsers['direction'] ?? 'flat') === 'up' ? 'Menaik' : (($dailyUsers['direction'] ?? 'flat') === 'down' ? 'Menurun' : 'Stabil') }}
                                            ({{ ($dailyUsers['delta'] ?? 0) > 0 ? '+' . $dailyUsers['delta'] : ($dailyUsers['delta'] ?? 0) }})
                                        </span>
                                        <span class="text-[11px] font-semibold text-muted block sm:inline">• {{ $userGrowth['total_users'] ?? 0 }} total</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- DIAGRAM 2 (5 KOLOM): DISTRIBUSI & TREN TESTIMONI BINTANG 5, 4, 3, 2, 1 -->
                        <div class="lg:col-span-5 rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-5 flex flex-col justify-between">
                            <div>
                                <!-- header kartu diagram testimoni -->
                                <div class="flex flex-wrap items-start justify-between gap-2.5 mb-3">
                                    <div>
                                        <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider text-amber-700 block">
                                            ANALITIK KEPUASAN TESTIMONI
                                        </span>
                                        <h3 class="font-heading font-extrabold text-sm sm:text-base text-ink mt-0.5">
                                            Tren Rating Bintang 5, 4, 3, 2, 1
                                        </h3>
                                    </div>

                                    <!-- rata-rata rating keseluruhan -->
                                    <div class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-200 px-3 py-1 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                        <span class="font-heading font-extrabold text-xs text-ink">{{ number_format((float) ($testiRatings['avg_rating'] ?? 0), 2) }}</span>
                                        <span class="text-[11px] text-muted">/ 5.0 ({{ $testiRatings['total'] ?? 0 }})</span>
                                    </div>
                                </div>

                                <!-- highlight banner bintang mana yang sedang menaik -->
                                <div class="mb-4 rounded-xl bg-white border border-gray-200/80 px-3.5 py-2.5 flex items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                                <polyline points="17 6 23 6 23 12" />
                                            </svg>
                                        </span>
                                        <span x-show="analyticsPeriod === 'monthly'" class="font-heading font-bold text-ink truncate">
                                            {{ $monthlyTestiSummary['headline'] ?? 'Belum ada ulasan testimoni' }}
                                        </span>
                                        <span x-show="analyticsPeriod === 'daily'" style="display: none;" class="font-heading font-bold text-ink truncate">
                                            {{ $dailyTestiSummary['headline'] ?? 'Belum ada ulasan testimoni' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- baris diagram batang horizontal per bintang: 5, 4, 3, 2, 1 -->
                                <div class="space-y-2.5" role="list" aria-label="Distribusi dan tren rating bintang 5 sampai 1">
                                    @foreach ($starRows as $row)
                                        @php
                                            $starNum = (int) ($row['star'] ?? 5);
                                            $barColor = match ($starNum) {
                                                5 => 'bg-sky',
                                                4 => 'bg-emerald-600',
                                                3 => 'bg-amber-500',
                                                2 => 'bg-orange-500',
                                                default => 'bg-rose-600',
                                            };
                                            $mInfo = $row['monthly'] ?? ['current' => 0, 'previous' => 0, 'delta' => 0, 'direction' => 'flat'];
                                            $dInfo = $row['daily'] ?? ['current' => 0, 'previous' => 0, 'delta' => 0, 'direction' => 'flat'];
                                        @endphp

                                        <div role="listitem" class="rounded-xl bg-white border border-gray-200/75 px-3.5 py-2.5">
                                            <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                                                <!-- label bintang 5..1 -->
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <span class="font-heading font-extrabold text-ink w-16">
                                                        Bintang {{ $starNum }}
                                                    </span>
                                                    <span class="inline-flex items-center text-amber-500" aria-hidden="true">
                                                        @for ($s = 1; $s <= $starNum; $s++)
                                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                            </svg>
                                                        @endfor
                                                    </span>
                                                </div>

                                                <!-- jumlah total + indikator naik/turun per bintang -->
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <span class="text-[11px] font-semibold text-muted">
                                                        {{ $row['total'] }} ulasan ({{ $row['share_pct'] }}%)
                                                    </span>

                                                    <!-- badge tren bulanan bintang ini -->
                                                    <span x-show="analyticsPeriod === 'monthly'">
                                                        @if ($mInfo['direction'] === 'up')
                                                            <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[10px] font-extrabold" title="Bulan ini: +{{ $mInfo['current'] }} vs Bulan lalu: +{{ $mInfo['previous'] }}">
                                                                &uarr; Naik +{{ $mInfo['delta'] }}
                                                            </span>
                                                        @elseif ($mInfo['direction'] === 'down')
                                                            <span class="inline-flex items-center gap-0.5 rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-[10px] font-extrabold" title="Bulan ini: +{{ $mInfo['current'] }} vs Bulan lalu: +{{ $mInfo['previous'] }}">
                                                                &darr; Turun {{ $mInfo['delta'] }}
                                                            </span>
                                                        @elseif ($mInfo['current'] > 0)
                                                            <span class="inline-flex items-center gap-0.5 rounded-full bg-[#F0F8FF] text-sky px-2 py-0.5 text-[10px] font-extrabold">
                                                                &rarr; Stabil (+{{ $mInfo['current'] }})
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center rounded-full bg-gray-100 text-muted px-2 py-0.5 text-[10px] font-bold">
                                                                • Tetap (0)
                                                            </span>
                                                        @endif
                                                    </span>

                                                    <!-- badge tren 7 hari bintang ini -->
                                                    <span x-show="analyticsPeriod === 'daily'" style="display: none;">
                                                        @if ($dInfo['direction'] === 'up')
                                                            <span class="inline-flex items-center gap-0.5 rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[10px] font-extrabold" title="7 hari ini: +{{ $dInfo['current'] }} vs 7 hari lalu: +{{ $dInfo['previous'] }}">
                                                                &uarr; Naik +{{ $dInfo['delta'] }}
                                                            </span>
                                                        @elseif ($dInfo['direction'] === 'down')
                                                            <span class="inline-flex items-center gap-0.5 rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-[10px] font-extrabold" title="7 hari ini: +{{ $dInfo['current'] }} vs 7 hari lalu: +{{ $dInfo['previous'] }}">
                                                                &darr; Turun {{ $dInfo['delta'] }}
                                                            </span>
                                                        @elseif ($dInfo['current'] > 0)
                                                            <span class="inline-flex items-center gap-0.5 rounded-full bg-[#F0F8FF] text-sky px-2 py-0.5 text-[10px] font-extrabold">
                                                                &rarr; Stabil (+{{ $dInfo['current'] }})
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center rounded-full bg-gray-100 text-muted px-2 py-0.5 text-[10px] font-bold">
                                                                • Tetap (0)
                                                            </span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- progress track proporsi bintang -->
                                            <div class="w-full h-2 rounded-full bg-gray-200/80 overflow-hidden">
                                                <div
                                                    class="h-full rounded-full {{ $barColor }} transition-all duration-300"
                                                    style="width: {{ max($row['total'] > 0 ? 6 : 0, $row['bar_pct']) }}%;"
                                                ></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- footer ringkasan testimoni -->
                            <div class="mt-4 pt-3 border-t border-gray-200/75 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                                <div class="text-muted font-semibold">
                                    <span x-show="analyticsPeriod === 'monthly'">
                                        Bulan ini: <strong class="text-ink">+{{ $monthlyTestiSummary['current_total'] ?? 0 }} ulasan</strong>
                                        (Bulan lalu: {{ $monthlyTestiSummary['previous_total'] ?? 0 }})
                                    </span>
                                    <span x-show="analyticsPeriod === 'daily'" style="display: none;">
                                        7 hari ini: <strong class="text-ink">+{{ $dailyTestiSummary['current_total'] ?? 0 }} ulasan</strong>
                                        (7 hari lalu: {{ $dailyTestiSummary['previous_total'] ?? 0 }})
                                    </span>
                                </div>

                                <a
                                    href="{{ route('testimoni.index') }}"
                                    class="inline-flex items-center gap-1 font-heading font-bold text-sky hover:underline"
                                >
                                    <span>Lihat semua testimoni</span>
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                        <polyline points="12 5 19 12 12 19" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- layout utama 2 kolom: kiri 70% (8 col) & kanan 30% (4 col) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- panel kiri (70%) -->
                <div class="lg:col-span-8 rounded-[28px] bg-white border border-gray-200/80 shadow-neutral overflow-hidden flex flex-col justify-between min-h-[560px]">
                    <div class="p-6 sm:p-8 flex-1">
                        @yield('admin_content')
                    </div>

                    <!-- footer bar tipis di bagian paling bawah panel kiri -->
                    <div class="px-6 sm:px-8 py-4 bg-[#F8FAFC] border-t border-gray-200/70 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 text-xs text-muted">
                            <span class="w-6 h-6 rounded-lg bg-sky/10 text-sky flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                </svg>
                            </span>
                            <span><strong class="text-ink">Butuh bantuan?</strong> Lihat panduan operasional admin untuk mengelola modul, soal, dan moderasi.</span>
                        </div>

                        <button
                            type="button"
                            @click="guideModalOpen = true"
                            class="inline-flex items-center gap-1.5 text-xs font-heading font-bold text-sky hover:text-ink transition-colors cursor-pointer"
                        >
                            <span>Lihat panduan admin</span>
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- panel kanan (30%) sticky bergaya card EXPLORE referensi -->
                <aside class="lg:col-span-4 lg:sticky lg:top-24 space-y-5">
                    <div class="rounded-[28px] bg-white border border-gray-200/80 p-6 shadow-neutral">
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-heading font-extrabold text-[11px] uppercase tracking-[0.16em] text-muted">
                                EXPLORE & STATISTIK
                            </span>
                            <span class="rounded-full bg-[#F8FAFC] border border-gray-200 px-2.5 py-0.5 text-[10px] font-bold text-ink">
                                Real-time DB
                            </span>
                        </div>

                        <div class="space-y-3">
                            <!-- item 1: modul edukasi -->
                            <a
                                href="{{ route('admin.edukasi.index') }}"
                                class="group flex items-center justify-between gap-3 rounded-2xl p-3 border transition-all {{ $activeTab === 'edukasi' ? 'bg-[#F0F8FF] border-sky/30' : 'bg-[#F8FAFC] border-gray-100 hover:border-gray-300' }}"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-10 h-10 rounded-xl bg-sky text-white flex items-center justify-center shrink-0 shadow-2xs">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-heading font-bold text-xs text-ink truncate">Modul Edukasi</p>
                                        <p class="text-[11px] text-muted truncate">
                                            {{ $adminStats['edukasi_teks'] ?? 0 }} Teks • {{ $adminStats['edukasi_link'] ?? 0 }} Link
                                            @if (($adminStats['pending_edukasi_requests'] ?? 0) > 0)
                                                • <span class="text-amber-600 font-bold">{{ $adminStats['pending_edukasi_requests'] }} Request</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-white border border-gray-200 px-2.5 py-1 text-xs font-extrabold text-ink shrink-0">
                                    {{ $adminStats['total_edukasi'] ?? 0 }}
                                </span>
                            </a>

                            <!-- item 2: bank soal minigame -->
                            <a
                                href="{{ route('admin.minigame.index') }}"
                                class="group flex items-center justify-between gap-3 rounded-2xl p-3 border transition-all {{ $activeTab === 'minigame' ? 'bg-[#F0F8FF] border-sky/30' : 'bg-[#F8FAFC] border-gray-100 hover:border-gray-300' }}"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-10 h-10 rounded-xl bg-ink text-lime flex items-center justify-center shrink-0 shadow-2xs">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="6" width="20" height="12" rx="2" />
                                            <path d="M6 12h4m-2-2v4m7-1h.01m2.99-2h.01" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-heading font-bold text-xs text-ink truncate">Soal &amp; Level Minigame</p>
                                        <p class="text-[11px] text-muted truncate">3 Simulasi • {{ $adminStats['total_levels'] ?? 5 }} Level Aktif</p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-white border border-gray-200 px-2.5 py-1 text-xs font-extrabold text-ink shrink-0">
                                    {{ $adminStats['total_soal'] ?? 0 }}
                                </span>
                            </a>

                            <!-- item 3: moderasi forum -->
                            <a
                                href="{{ route('admin.forum.index') }}"
                                class="group flex items-center justify-between gap-3 rounded-2xl p-3 border transition-all {{ $activeTab === 'forum' ? 'bg-[#F0F8FF] border-sky/30' : 'bg-[#F8FAFC] border-gray-100 hover:border-gray-300' }}"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-10 h-10 rounded-xl bg-lime text-ink flex items-center justify-center shrink-0 shadow-2xs">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-heading font-bold text-xs text-ink truncate">Moderasi Forum</p>
                                        <p class="text-[11px] text-muted truncate">{{ $adminStats['forum_starred'] ?? 0 }} Disematkan ke Atas</p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-white border border-gray-200 px-2.5 py-1 text-xs font-extrabold text-ink shrink-0">
                                    {{ $adminStats['total_forum'] ?? 0 }}
                                </span>
                            </a>

                            <!-- item 4: hak akses user -->
                            <a
                                href="{{ route('admin.user.index') }}"
                                class="group flex items-center justify-between gap-3 rounded-2xl p-3 border transition-all {{ $activeTab === 'user' ? 'bg-[#F0F8FF] border-sky/30' : 'bg-[#F8FAFC] border-gray-100 hover:border-gray-300' }}"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-10 h-10 rounded-xl bg-sky/15 text-sky flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-heading font-bold text-xs text-ink truncate">Hak Akses User</p>
                                        <p class="text-[11px] text-muted truncate">
                                            {{ $adminStats['total_admins'] ?? 0 }} Pengelola •
                                            <span class="{{ ($monthlyUsers['direction'] ?? 'flat') === 'up' ? 'text-emerald-700 font-bold' : (($monthlyUsers['direction'] ?? 'flat') === 'down' ? 'text-rose-700 font-bold' : '') }}">
                                                {{ ($monthlyUsers['direction'] ?? 'flat') === 'up' ? 'Menaik' : (($monthlyUsers['direction'] ?? 'flat') === 'down' ? 'Menurun' : 'Stabil') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-white border border-gray-200 px-2.5 py-1 text-xs font-extrabold text-ink shrink-0">
                                    {{ $adminStats['total_users'] ?? 0 }}
                                </span>
                            </a>

                            <!-- item 5: ringkasan testimoni & rating -->
                            <a
                                href="{{ route('testimoni.index') }}"
                                class="group flex items-center justify-between gap-3 rounded-2xl p-3 border bg-[#F8FAFC] border-gray-100 hover:border-gray-300 transition-all"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-heading font-bold text-xs text-ink truncate">Rating Testimoni</p>
                                        <p class="text-[11px] text-muted truncate">
                                            Rata-rata {{ number_format((float) ($testiRatings['avg_rating'] ?? 0), 2) }}/5.0 • Bintang {{ $testiRatings['dominant_star'] ?? 5 }} tertinggi
                                        </p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-white border border-gray-200 px-2.5 py-1 text-xs font-extrabold text-ink shrink-0">
                                    {{ $testiRatings['total'] ?? 0 }}
                                </span>
                            </a>
                        </div>

                        <!-- info akun aktif di bawah explore -->
                        <div class="mt-5 pt-5 border-t border-gray-100 space-y-3">
                            <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/70 p-4">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Ekspor Laporan Excel</span>
                                    <span class="rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[9px] font-extrabold">.XLSX</span>
                                </div>
                                <p class="text-[11px] text-muted leading-relaxed">
                                    Unduh rekap jumlah pendaftar, pencapaian titik milestone, skor minigame, &amp; forum.
                                </p>
                                <a
                                    href="{{ route('admin.report.xlsx') }}"
                                    class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-heading font-bold text-xs px-4 py-2.5 shadow-2xs transition-colors"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    <span>Download Laporan XLSX</span>
                                </a>
                            </div>

                            <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/70 p-4">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Sesi Otoritas Aktif</span>
                                    @if (Auth::user()->isOwner())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-ink text-lime px-2 py-0.5 text-[9px] font-extrabold">
                                            <svg class="w-2.5 h-2.5 text-lime" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14v2H5v-2z" />
                                            </svg>
                                            <span>Owner</span>
                                        </span>
                                    @else
                                        <span class="rounded-full bg-lime text-ink px-2 py-0.5 text-[9px] font-extrabold">Admin</span>
                                    @endif
                                </div>
                                <p class="font-heading font-bold text-xs text-ink truncate">{{ Auth::user()->nama_lengkap ?: Auth::user()->username }}</p>
                                <p class="text-[11px] text-muted truncate mt-0.5">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                    </div>
                </aside>

            </div>

        </div>

        <!-- modal panduan admin -->
        <div
            x-show="guideModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(10, 10, 10, 0.5);"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            @keydown.escape.window="guideModalOpen = false"
        >
            <div
                @click.outside="guideModalOpen = false"
                class="w-full max-w-lg rounded-[28px] bg-white p-6 sm:p-8 shadow-neutral border border-gray-100"
            >
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="font-heading font-extrabold text-lg text-ink">Panduan Operasional Admin</h3>
                    <button
                        type="button"
                        @click="guideModalOpen = false"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center cursor-pointer"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-xs text-muted leading-relaxed">
                    <div class="rounded-xl bg-[#F8FAFC] p-3.5 border border-gray-100">
                        <strong class="text-ink block mb-0.5">1. Manajemen Edukasi</strong>
                        Pilih tipe <em>Teks Manual</em> untuk modul bacaan pop-up di halaman Edukasi, atau <em>Link Eksternal</em> untuk rujukan jurnal/portal luar.
                    </div>
                    <div class="rounded-xl bg-[#F8FAFC] p-3.5 border border-gray-100">
                        <strong class="text-ink block mb-0.5">2. Soal Minigame</strong>
                        Kelola bank soal pilihan ganda (A, B, C, D) berdasarkan kategori game (Phishing Hunter, Cyber Defender, Link or Trap) dan tingkat kesulitan.
                    </div>
                    <div class="rounded-xl bg-[#F8FAFC] p-3.5 border border-gray-100">
                        <strong class="text-ink block mb-0.5">3. Moderasi Forum</strong>
                        Klik ikon Bintang pada diskusi penting agar muncul paling atas di halaman Forum pengguna dengan badge <em>Disematkan</em>.
                    </div>
                    <div class="rounded-xl bg-[#F8FAFC] p-3.5 border border-gray-100">
                        <strong class="text-ink block mb-0.5">4. Hak Akses User & Proteksi Owner</strong>
                        Akun <strong>Owner (Razief Afkar Rusli)</strong> bersifat mutlak dan tidak dapat diubah atau dihapus oleh siapapun.
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        @click="guideModalOpen = false"
                        class="rounded-full bg-ink text-white hover:bg-sky font-heading font-bold text-xs px-6 py-2.5 transition-colors cursor-pointer"
                    >
                        Mengerti
                    </button>
                </div>
            </div>
        </div>

        <!-- Pop-up Konfirmasi Hapus/Aksi Admin (menggantikan window.confirm browser) -->
        <div
            x-show="confirmModal.open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(10, 10, 10, 0.52);"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            @keydown.escape.window="confirmModal.open = false"
        >
            <div
                @click.outside="confirmModal.open = false"
                class="w-full max-w-sm rounded-[24px] bg-white p-6 shadow-neutral border border-gray-100"
            >
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-heading font-extrabold text-base text-ink" x-text="confirmModal.title"></h3>
                        <p class="mt-1 text-xs text-muted leading-relaxed" x-text="confirmModal.message"></p>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="confirmModal.open = false"
                        class="rounded-full bg-gray-100 hover:bg-gray-200 text-ink font-heading font-bold text-xs px-4 py-2 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="if (confirmModal.form) { confirmModal.open = false; confirmModal.form.submit(); }"
                        class="rounded-full bg-red-600 hover:bg-red-700 text-white font-heading font-bold text-xs px-4 py-2 transition-colors cursor-pointer"
                        x-text="confirmModal.confirmLabel"
                    ></button>
                </div>
            </div>
        </div>
    </section>
@endsection
