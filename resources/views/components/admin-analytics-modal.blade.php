@props(['analytics'])

@php
    $isEn = app()->getLocale() === 'en';
    $userGrowth = $analytics['user_growth'] ?? [];
    $monthlyUsers = $userGrowth['monthly'] ?? [];
    $dailyUsers = $userGrowth['daily'] ?? [];
    $testiRatings = $analytics['testimoni_ratings'] ?? [];
    $starRows = $testiRatings['stars'] ?? [];
    $monthlyTestiSummary = $testiRatings['monthly_summary'] ?? [];
    $dailyTestiSummary = $testiRatings['daily_summary'] ?? [];

    $analyticsPayload = [
        'total_users' => (int) ($userGrowth['total_users'] ?? 0),
        'monthly' => $monthlyUsers,
        'daily' => $dailyUsers,
        'ratings' => [
            'total' => (int) ($testiRatings['total'] ?? 0),
            'avg_rating' => (float) ($testiRatings['avg_rating'] ?? 0),
            'dominant_star' => (int) ($testiRatings['dominant_star'] ?? 5),
            'dominant_count' => (int) ($testiRatings['dominant_count'] ?? 0),
            'stars' => array_values($starRows),
            'monthly_summary' => $monthlyTestiSummary,
            'daily_summary' => $dailyTestiSummary,
        ],
    ];
@endphp

<template x-teleport="body">
    <div
        x-show="analyticsModalOpen"
        x-effect="document.body.classList.toggle('overflow-hidden', analyticsModalOpen)"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="if (analyticsModalOpen) analyticsModalOpen = false"
        style="display: none; z-index: 9999;"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-5 bg-slate-900/65 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
        aria-labelledby="analyticsModalTitle"
    >
    <div
        @click.outside="analyticsModalOpen = false"
        x-data="{
            data: @js($analyticsPayload),
            period: 'monthly',
            selectedIdx: null,
            hoveredIdx: null,
            showNewLine: true,
            showCumLine: true,
            showBars: true,
            selectedStar: null,
            isEn: @js($isEn),

            init() {
                this.resetPointSelection();
            },

            get currentSeries() {
                return this.period === 'daily' ? this.data.daily : this.data.monthly;
            },

            get buckets() {
                return Array.isArray(this.currentSeries?.buckets) ? this.currentSeries.buckets : [];
            },

            get activeIdx() {
                if (this.hoveredIdx !== null && this.buckets[this.hoveredIdx]) return this.hoveredIdx;
                if (this.selectedIdx !== null && this.buckets[this.selectedIdx]) return this.selectedIdx;
                return Math.max(0, this.buckets.length - 1);
            },

            get activeBucket() {
                return this.buckets[this.activeIdx] || null;
            },

            get prevBucket() {
                return this.activeIdx > 0 ? (this.buckets[this.activeIdx - 1] || null) : null;
            },

            get activeDelta() {
                if (!this.activeBucket) return 0;
                if (!this.prevBucket) return this.activeBucket.count;
                return this.activeBucket.count - this.prevBucket.count;
            },

            get peakIdx() {
                if (!this.buckets.length) return 0;
                let best = 0;
                for (let i = 1; i < this.buckets.length; i++) {
                    if (this.buckets[i].count >= this.buckets[best].count) best = i;
                }
                return best;
            },

            get averageNew() {
                if (!this.buckets.length) return '0.0';
                const sum = this.buckets.reduce((acc, b) => acc + (b.count || 0), 0);
                return (sum / this.buckets.length).toFixed(1);
            },

            get lineColor() {
                return this.currentSeries?.direction === 'down' ? '#E11D48' : '#0284C7';
            },

            get ratingSummary() {
                return this.period === 'daily'
                    ? (this.data.ratings?.daily_summary || {})
                    : (this.data.ratings?.monthly_summary || {});
            },

            get activeStarObj() {
                const stars = this.data.ratings?.stars || [];
                if (this.selectedStar !== null) {
                    return stars.find(s => s.star === this.selectedStar) || null;
                }
                return stars.find(s => s.star === this.data.ratings?.dominant_star) || stars[0] || null;
            },

            setPeriod(p) {
                this.period = p;
                this.hoveredIdx = null;
                this.resetPointSelection();
            },

            resetPointSelection() {
                const len = this.buckets.length;
                this.selectedIdx = len > 0 ? len - 1 : 0;
            },

            selectPoint(idx) {
                if (idx >= 0 && idx < this.buckets.length) {
                    this.selectedIdx = idx;
                }
            },

            stepPoint(dir) {
                if (!this.buckets.length) return;
                const next = Math.min(this.buckets.length - 1, Math.max(0, this.activeIdx + dir));
                this.selectedIdx = next;
                this.hoveredIdx = null;
            },

            toggleStar(starNum) {
                this.selectedStar = this.selectedStar === starNum ? null : starNum;
            },

            starPeriodInfo(row) {
                return this.period === 'daily' ? (row.daily || {}) : (row.monthly || {});
            },

            starBarColor(starNum) {
                if (starNum === 5) return '#0284C7';
                if (starNum === 4) return '#059669';
                if (starNum === 3) return '#F59E0B';
                if (starNum === 2) return '#F97316';
                return '#E11D48';
            }
        }"
        @keydown.arrow-left.window="if (analyticsModalOpen) stepPoint(-1)"
        @keydown.arrow-right.window="if (analyticsModalOpen) stepPoint(1)"
        class="w-full max-w-6xl max-h-[92vh] rounded-[28px] bg-white border border-gray-200/90 shadow-2xl overflow-hidden flex flex-col"
    >
        <!-- Modal Header -->
        <div class="px-5 sm:px-7 py-4 bg-[#F8FAFC] border-b border-gray-200/80 flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-10 h-10 rounded-xl bg-ink text-lime flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                        <polyline points="17 6 23 6 23 12" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 id="analyticsModalTitle" class="font-heading font-extrabold text-base sm:text-lg text-ink truncate">
                        {{ $isEn ? 'Interactive Analytics & Trend Inspector' : 'Analitik Interaktif & Diagram Tren' }}
                    </h2>
                    <p class="text-[11px] text-muted truncate">
                        {{ $isEn ? 'Click any data point, metric card, legend, or star tier to inspect detailed metrics.' : 'Klik titik grafik, kartu metrik, legenda, atau baris rating untuk menganalisa data secara detail.' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <div class="inline-flex rounded-xl bg-white border border-gray-200 p-1 gap-1" role="group" aria-label="{{ $isEn ? 'Chart period' : 'Periode diagram' }}">
                    <button
                        type="button"
                        @click="setPeriod('monthly')"
                        :aria-pressed="period === 'monthly'"
                        :class="period === 'monthly' ? 'bg-ink text-lime font-bold' : 'text-muted hover:text-ink font-semibold'"
                        class="rounded-lg px-3 py-1.5 text-[11px] font-heading transition-colors cursor-pointer"
                    >
                        {{ $isEn ? 'Last 6 Months' : '6 Bulan Terakhir' }}
                    </button>
                    <button
                        type="button"
                        @click="setPeriod('daily')"
                        :aria-pressed="period === 'daily'"
                        :class="period === 'daily' ? 'bg-ink text-lime font-bold' : 'text-muted hover:text-ink font-semibold'"
                        class="rounded-lg px-3 py-1.5 text-[11px] font-heading transition-colors cursor-pointer"
                    >
                        {{ $isEn ? 'Last 7 Days' : '7 Hari Terakhir' }}
                    </button>
                </div>

                <button
                    type="button"
                    @click="analyticsModalOpen = false"
                    aria-label="{{ $isEn ? 'Close analytics modal' : 'Tutup popup analitik' }}"
                    class="w-9 h-9 rounded-full bg-white border border-gray-200 hover:bg-gray-100 text-ink flex items-center justify-center transition-colors cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-5 sm:p-7 overflow-y-auto space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- KOLOM KIRI (7 COL): INTERACTIVE LINE + DOT CHART -->
                <div class="lg:col-span-7 rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-5 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider text-sky block">
                                {{ $isEn ? 'USER GROWTH ANALYSIS' : 'ANALISA PERTUMBUHAN PENGGUNA' }}
                            </span>
                            <h3 class="font-heading font-extrabold text-sm sm:text-base text-ink mt-0.5">
                                {{ $isEn ? 'Interactive Registration Trend (Line & Points)' : 'Tren Pendaftar User (Garis & Titik Interaktif)' }}
                            </h3>
                        </div>

                        <!-- Interactive Status Badge -->
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-heading font-extrabold border"
                            :class="currentSeries.direction === 'up'
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                : (currentSeries.direction === 'down'
                                    ? 'bg-rose-50 text-rose-700 border-rose-200'
                                    : 'bg-white text-ink border-gray-200')"
                        >
                            <span
                                x-text="currentSeries.direction === 'up'
                                    ? (isEn ? 'Rising' : 'Menaik')
                                    : (currentSeries.direction === 'down' ? (isEn ? 'Declining' : 'Menurun') : (isEn ? 'Stable' : 'Stabil'))"
                            ></span>
                            <span x-text="'(' + (currentSeries.delta > 0 ? '+' + currentSeries.delta : currentSeries.delta) + ')'"></span>
                        </span>
                    </div>

                    <!-- Clickable Series Toggles (Legend) -->
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button
                                type="button"
                                @click="showNewLine = !showNewLine"
                                :aria-pressed="showNewLine"
                                :class="showNewLine ? 'bg-white border-sky text-ink font-bold' : 'bg-gray-100 border-gray-200 text-muted line-through'"
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] transition-colors cursor-pointer"
                            >
                                <span class="w-2.5 h-2.5 rounded-full inline-block" :style="'background:' + lineColor"></span>
                                <span>{{ $isEn ? 'New Users Line' : 'Garis & Titik Baru' }}</span>
                            </button>

                            <button
                                type="button"
                                @click="showCumLine = !showCumLine"
                                :aria-pressed="showCumLine"
                                :class="showCumLine ? 'bg-white border-emerald-500 text-ink font-bold' : 'bg-gray-100 border-gray-200 text-muted line-through'"
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] transition-colors cursor-pointer"
                            >
                                <span class="w-3 h-0.5 bg-emerald-600 inline-block"></span>
                                <span>{{ $isEn ? 'Cumulative' : 'Akumulasi' }}</span>
                            </button>

                            <button
                                type="button"
                                @click="showBars = !showBars"
                                :aria-pressed="showBars"
                                :class="showBars ? 'bg-white border-gray-300 text-ink font-bold' : 'bg-gray-100 border-gray-200 text-muted line-through'"
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] transition-colors cursor-pointer"
                            >
                                <span class="w-2.5 h-2.5 rounded-xs bg-sky/30 border border-sky inline-block"></span>
                                <span>{{ $isEn ? 'Guide Bars' : 'Batang Bantu' }}</span>
                            </button>
                        </div>

                        <span class="text-[11px] font-semibold text-muted" x-text="currentSeries.comparison_label"></span>
                    </div>

                    <!-- Interactive SVG Chart -->
                    <div class="rounded-xl bg-white border border-gray-200/80 p-3 sm:p-4">
                        <template x-if="buckets.length === 0">
                            <div class="py-10 text-center text-xs text-muted">
                                {{ $isEn ? 'No user registration data available yet.' : 'Belum ada data pendaftar pengguna.' }}
                            </div>
                        </template>

                        <template x-if="buckets.length > 0">
                            <svg
                                viewBox="0 0 530 180"
                                class="w-full h-52 sm:h-56 overflow-visible select-none"
                                role="img"
                                aria-label="{{ $isEn ? 'Interactive user growth chart' : 'Diagram garis dan titik pertumbuhan pengguna interaktif' }}"
                            >
                                <defs>
                                    <linearGradient id="modalGrowthFill" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" :stop-color="lineColor" stop-opacity="0.22" />
                                        <stop offset="100%" :stop-color="lineColor" stop-opacity="0.0" />
                                    </linearGradient>
                                </defs>

                                <!-- Horizontal Gridlines -->
                                <line x1="36" y1="26" x2="504" y2="26" stroke="#E2E8F0" stroke-dasharray="3 3" stroke-width="1" />
                                <line x1="36" y1="77" x2="504" y2="77" stroke="#E2E8F0" stroke-dasharray="3 3" stroke-width="1" />
                                <line x1="36" y1="128" x2="504" y2="128" stroke="#94A3B8" stroke-width="1.2" />

                                <!-- Y-Axis Labels -->
                                <text x="28" y="30" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold" x-text="currentSeries.max_new || 1"></text>
                                <text x="28" y="81" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold" x-text="Math.max(1, Math.ceil((currentSeries.max_new || 1) / 2))"></text>
                                <text x="28" y="132" text-anchor="end" class="text-[10px] fill-slate-500 font-semibold">0</text>

                                <!-- Vertical Active Crosshair Line -->
                                <template x-if="activeBucket">
                                    <line
                                        :x1="activeBucket.x"
                                        y1="16"
                                        :x2="activeBucket.x"
                                        y2="132"
                                        stroke="#0F172A"
                                        stroke-opacity="0.22"
                                        stroke-dasharray="3 3"
                                        stroke-width="1.5"
                                    />
                                </template>

                                <!-- Area Fill -->
                                <polygon
                                    x-show="showNewLine && currentSeries.area_new"
                                    :points="currentSeries.area_new"
                                    fill="url(#modalGrowthFill)"
                                />

                                <!-- Guide Bars (clickable) -->
                                <template x-for="(b, idx) in buckets" :key="'bar-' + b.key">
                                    <rect
                                        x-show="showBars"
                                        :x="b.x - 13"
                                        :y="b.bar_y"
                                        width="26"
                                        :height="b.bar_h"
                                        rx="5"
                                        :fill="activeIdx === idx ? 'rgba(2, 132, 199, 0.34)' : 'rgba(2, 132, 199, 0.14)'"
                                        :stroke="activeIdx === idx ? '#0284C7' : 'none'"
                                        stroke-width="1.2"
                                        class="cursor-pointer transition-all"
                                        @click="selectPoint(idx)"
                                        @mouseenter="hoveredIdx = idx"
                                        @mouseleave="hoveredIdx = null"
                                    />
                                </template>

                                <!-- Cumulative Dashed Line -->
                                <polyline
                                    x-show="showCumLine && currentSeries.polyline_cum"
                                    fill="none"
                                    stroke="#059669"
                                    stroke-width="2"
                                    stroke-dasharray="4 3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :points="currentSeries.polyline_cum"
                                />

                                <!-- Primary New Users Line -->
                                <polyline
                                    x-show="showNewLine && currentSeries.polyline_new"
                                    fill="none"
                                    :stroke="lineColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :points="currentSeries.polyline_new"
                                />

                                <!-- Cumulative Dots -->
                                <template x-for="(b, idx) in buckets" :key="'cdot-' + b.key">
                                    <circle
                                        x-show="showCumLine"
                                        :cx="b.x"
                                        :cy="b.y_cum"
                                        :r="activeIdx === idx ? 4.5 : 3"
                                        fill="#059669"
                                        stroke="#FFFFFF"
                                        stroke-width="1.5"
                                        class="cursor-pointer"
                                        @click="selectPoint(idx)"
                                        @mouseenter="hoveredIdx = idx"
                                        @mouseleave="hoveredIdx = null"
                                    />
                                </template>

                                <!-- Interactive Clickable Data Points + Labels -->
                                <template x-for="(b, idx) in buckets" :key="'pt-' + b.key">
                                    <g
                                        class="cursor-pointer"
                                        @click="selectPoint(idx)"
                                        @mouseenter="hoveredIdx = idx"
                                        @mouseleave="hoveredIdx = null"
                                    >
                                        <!-- Wide invisible hit column for easy tapping/clicking -->
                                        <rect
                                            :x="b.x - 24"
                                            y="10"
                                            width="48"
                                            height="155"
                                            fill="transparent"
                                        />

                                        <!-- Active Outer Halo Ring -->
                                        <circle
                                            x-show="showNewLine && activeIdx === idx"
                                            :cx="b.x"
                                            :cy="b.y_new"
                                            r="10"
                                            :fill="lineColor"
                                            fill-opacity="0.16"
                                        />

                                        <!-- Main Data Dot -->
                                        <circle
                                            x-show="showNewLine"
                                            :cx="b.x"
                                            :cy="b.y_new"
                                            :r="activeIdx === idx ? 6 : 4.5"
                                            :fill="activeIdx === idx ? lineColor : '#FFFFFF'"
                                            :stroke="activeIdx === idx ? '#FFFFFF' : lineColor"
                                            stroke-width="2.5"
                                        />

                                        <!-- Value Label above Dot -->
                                        <text
                                            x-show="showNewLine"
                                            :x="b.x"
                                            :y="Math.max(13, b.y_new - 11)"
                                            text-anchor="middle"
                                            class="text-[10px] font-extrabold"
                                            :fill="activeIdx === idx ? '#0F172A' : (b.count > 0 ? '#0369A1' : '#94A3B8')"
                                            x-text="'+' + b.count"
                                        ></text>

                                        <!-- X-Axis Clickable Label -->
                                        <text
                                            :x="b.x"
                                            y="150"
                                            text-anchor="middle"
                                            class="text-[10px] font-bold"
                                            :fill="activeIdx === idx ? '#0F172A' : '#64748B'"
                                            x-text="b.label"
                                        ></text>

                                        <!-- Active Underline Indicator under X Label -->
                                        <rect
                                            x-show="activeIdx === idx"
                                            :x="b.x - 14"
                                            y="156"
                                            width="28"
                                            height="3"
                                            rx="1.5"
                                            :fill="lineColor"
                                        />
                                    </g>
                                </template>
                            </svg>
                        </template>
                    </div>

                    <!-- Live Selected Point Inspector -->
                    <template x-if="activeBucket">
                        <div class="rounded-xl bg-white border border-gray-200/90 p-3.5 flex flex-wrap items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-ink text-lime px-2 py-0.5 text-[10px] font-heading font-extrabold uppercase tracking-wider">
                                        {{ $isEn ? 'Selected Point' : 'Titik Terpilih' }}
                                    </span>
                                    <span class="font-heading font-extrabold text-xs sm:text-sm text-ink" x-text="activeBucket.full_label"></span>
                                </div>
                                <p class="text-[11px] text-muted">
                                    {{ $isEn ? 'New:' : 'Pendaftar Baru:' }}
                                    <strong class="text-ink" x-text="'+' + activeBucket.count"></strong>
                                    &middot;
                                    {{ $isEn ? 'Cumulative:' : 'Total Akumulasi:' }}
                                    <strong class="text-emerald-700" x-text="activeBucket.cumulative + ' user'"></strong>
                                    &middot;
                                    {{ $isEn ? 'vs Prev:' : 'vs Sebelumnya:' }}
                                    <strong
                                        :class="activeDelta > 0 ? 'text-emerald-700' : (activeDelta < 0 ? 'text-rose-700' : 'text-ink')"
                                        x-text="(activeDelta > 0 ? '+' : '') + activeDelta"
                                    ></strong>
                                </p>
                            </div>

                            <div class="inline-flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="stepPoint(-1)"
                                    :disabled="activeIdx <= 0"
                                    aria-label="{{ $isEn ? 'Previous point' : 'Titik sebelumnya' }}"
                                    class="w-8 h-8 rounded-lg bg-[#F8FAFC] border border-gray-200 hover:border-sky text-ink flex items-center justify-center cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="15 18 9 12 15 6" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="stepPoint(1)"
                                    :disabled="activeIdx >= buckets.length - 1"
                                    aria-label="{{ $isEn ? 'Next point' : 'Titik berikutnya' }}"
                                    class="w-8 h-8 rounded-lg bg-[#F8FAFC] border border-gray-200 hover:border-sky text-ink flex items-center justify-center cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Clickable Quick-Jump KPI Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <button
                            type="button"
                            @click="selectPoint(buckets.length - 1)"
                            class="text-left rounded-xl bg-white border border-gray-200/80 hover:border-sky p-3 transition-colors cursor-pointer"
                        >
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted block" x-text="period === 'daily' ? (isEn ? 'Last 7 Days' : '7 Hari Ini') : (isEn ? 'This Month' : 'Bulan Ini')"></span>
                            <p class="font-heading font-extrabold text-base text-ink mt-0.5" x-text="'+' + (currentSeries.current_count || 0)"></p>
                        </button>

                        <button
                            type="button"
                            @click="selectPoint(Math.max(0, buckets.length - 2))"
                            class="text-left rounded-xl bg-white border border-gray-200/80 hover:border-sky p-3 transition-colors cursor-pointer"
                        >
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted block" x-text="period === 'daily' ? (isEn ? 'Prev 7 Days' : '7 Hari Lalu') : (isEn ? 'Last Month' : 'Bulan Lalu')"></span>
                            <p class="font-heading font-extrabold text-base text-ink mt-0.5" x-text="'+' + (currentSeries.previous_count || 0)"></p>
                        </button>

                        <button
                            type="button"
                            @click="selectPoint(peakIdx)"
                            class="text-left rounded-xl bg-white border border-gray-200/80 hover:border-sky p-3 transition-colors cursor-pointer"
                        >
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sky block">{{ $isEn ? 'Peak (Click)' : 'Puncak (Klik)' }}</span>
                            <p class="font-heading font-extrabold text-base text-ink mt-0.5" x-text="buckets[peakIdx] ? ('+' + buckets[peakIdx].count + ' • ' + buckets[peakIdx].label) : '0'"></p>
                        </button>

                        <div class="rounded-xl bg-white border border-gray-200/80 p-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">{{ $isEn ? 'Avg / Period' : 'Rata-rata' }}</span>
                            <p class="font-heading font-extrabold text-base text-ink mt-0.5" x-text="averageNew + ' / ' + (period === 'daily' ? (isEn ? 'day' : 'hari') : (isEn ? 'mo' : 'bln'))"></p>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN (5 COL): INTERACTIVE RATING TESTIMONI BINTANG 5-1 -->
                <div class="lg:col-span-5 rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-5 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-2.5">
                        <div>
                            <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider text-amber-700 block">
                                {{ $isEn ? 'TESTIMONIAL SATISFACTION' : 'ANALITIK KEPUASAN TESTIMONI' }}
                            </span>
                            <h3 class="font-heading font-extrabold text-sm sm:text-base text-ink mt-0.5">
                                {{ $isEn ? 'Star Ratings Breakdown (5★ – 1★)' : 'Distribusi & Tren Rating (5★ – 1★)' }}
                            </h3>
                        </div>

                        <div class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-200 px-3 py-1">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            <span class="font-heading font-extrabold text-xs text-ink" x-text="Number(data.ratings.avg_rating || 0).toFixed(2)"></span>
                            <span class="text-[11px] text-muted" x-text="'/ 5.0 (' + (data.ratings.total || 0) + ')'"></span>
                        </div>
                    </div>

                    <!-- Interactive Stacked Proportion Bar (Clickable Segments) -->
                    <div class="space-y-1.5">
                        <div class="w-full h-3.5 rounded-full bg-gray-200/80 overflow-hidden flex">
                            <template x-for="row in data.ratings.stars" :key="'seg-' + row.star">
                                <button
                                    type="button"
                                    x-show="row.share_pct > 0"
                                    @click="toggleStar(row.star)"
                                    :style="'width:' + row.share_pct + '%; background-color:' + starBarColor(row.star)"
                                    :class="selectedStar === row.star ? 'ring-2 ring-ink ring-inset' : 'hover:opacity-85'"
                                    :title="row.label + ': ' + row.total + ' (' + row.share_pct + '%)'"
                                    class="h-full transition-all cursor-pointer"
                                ></button>
                            </template>
                        </div>
                        <p class="text-[11px] font-semibold text-muted" x-text="ratingSummary.headline || (isEn ? 'Click a star row to inspect details' : 'Klik baris bintang untuk analisa detail')"></p>
                    </div>

                    <!-- Clickable Star Rows 5..1 -->
                    <div class="space-y-2">
                        <template x-for="row in data.ratings.stars" :key="'row-' + row.star">
                            <button
                                type="button"
                                @click="toggleStar(row.star)"
                                :class="selectedStar === row.star ? 'border-ink bg-white ring-1 ring-ink/15' : 'border-gray-200/80 bg-white hover:border-sky/60'"
                                class="w-full text-left rounded-xl border px-3.5 py-2.5 transition-all cursor-pointer"
                            >
                                <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-heading font-extrabold text-ink" x-text="(isEn ? 'Star ' : 'Bintang ') + row.star"></span>
                                        <span class="text-amber-500 text-[11px]" x-text="'★'.repeat(row.star)"></span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-semibold text-muted" x-text="row.total + ' (' + row.share_pct + '%)'"></span>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-[10px] font-extrabold"
                                            :class="starPeriodInfo(row).direction === 'up'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : (starPeriodInfo(row).direction === 'down'
                                                    ? 'bg-rose-100 text-rose-700'
                                                    : 'bg-gray-100 text-muted')"
                                            x-text="starPeriodInfo(row).direction === 'up'
                                                ? ('↑ +' + starPeriodInfo(row).delta)
                                                : (starPeriodInfo(row).direction === 'down'
                                                    ? ('↓ ' + starPeriodInfo(row).delta)
                                                    : ('• ' + (starPeriodInfo(row).current || 0)))"
                                        ></span>
                                    </div>
                                </div>

                                <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-300"
                                        :style="'width:' + (row.total > 0 ? Math.max(6, row.bar_pct) : 0) + '%; background-color:' + starBarColor(row.star)"
                                    ></div>
                                </div>
                            </button>
                        </template>
                    </div>

                    <!-- Selected Star Detail Inspector -->
                    <template x-if="activeStarObj">
                        <div class="rounded-xl bg-white border border-gray-200/90 p-3.5 text-xs space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-heading font-extrabold text-ink" x-text="(isEn ? 'Inspecting: Star ' : 'Analisa: Bintang ') + activeStarObj.star + ' (' + activeStarObj.share_pct + '%)'"></span>
                                <span class="text-[11px] font-bold text-muted" x-text="activeStarObj.total + (isEn ? ' total reviews' : ' total ulasan')"></span>
                            </div>
                            <p class="text-[11px] text-muted">
                                <span x-text="period === 'daily' ? (isEn ? 'Last 7 days:' : '7 hari ini:') : (isEn ? 'This month:' : 'Bulan ini:')"></span>
                                <strong class="text-ink" x-text="'+' + (starPeriodInfo(activeStarObj).current || 0)"></strong>
                                &middot;
                                <span x-text="period === 'daily' ? (isEn ? 'Prev 7 days:' : '7 hari lalu:') : (isEn ? 'Last month:' : 'Bulan lalu:')"></span>
                                <strong class="text-ink" x-text="starPeriodInfo(activeStarObj).previous || 0"></strong>
                            </p>
                        </div>
                    </template>

                    <div class="pt-1 flex items-center justify-between text-[11px]">
                        <span class="text-muted font-semibold" x-text="(isEn ? 'Total in period: +' : 'Total periode ini: +') + (ratingSummary.current_total || 0)"></span>
                        <a href="{{ route('testimoni.index') }}" class="font-heading font-bold text-sky hover:underline">
                            {{ $isEn ? 'View all testimonials →' : 'Lihat semua testimoni →' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</template>
