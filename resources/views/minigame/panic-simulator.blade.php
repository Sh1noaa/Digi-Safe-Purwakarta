@extends('layouts.app')

@section('title', app()->getLocale() === 'en' ? '60 Seconds to Save Your Phone - Digi-Safe Purwakarta' : '60 Detik Selamatkan HP-mu - Digi-Safe Purwakarta')

@section('content')
    @php
        $isEn = app()->getLocale() === 'en';
        $isDark = ($currentTheme ?? 'light') === 'dark';
        $isAuth = Auth::check();
    @endphp

    <style>
        /* Scoped styling & choreographed motion for the Android OS Panic Simulator */
        .panic-root {
            --panic-ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        .panic-phone-shell {
            background: linear-gradient(165deg, #272E3B 0%, #141923 48%, #0E121B 100%);
            box-shadow: 0 24px 48px -16px rgba(10, 15, 28, 0.42), inset 0 1px 1px rgba(255, 255, 255, 0.14);
        }

        .panic-phone-screen {
            background-color: #0F172A;
            color: #F8FAFC;
        }

        /* Flashlight subtle warm beam along top chassis rim */
        .panic-flashlight-beam {
            background: radial-gradient(ellipse at 50% 0%, rgba(254, 240, 138, 0.52) 0%, rgba(254, 240, 138, 0.16) 42%, transparent 74%);
            transition: opacity 240ms var(--panic-ease);
        }

        /* Quick Settings shade spring transition (supports live drag-down & click toggle) */
        .panic-shade {
            transition: transform 320ms var(--panic-ease), opacity 220ms ease;
            will-change: transform, opacity;
        }

        .panic-shade.is-dragging {
            transition: none !important;
        }

        /* App window transition */
        @keyframes panicScreenIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.985);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .panic-screen-animate {
            animation: panicScreenIn 230ms var(--panic-ease) both;
        }

        /* Heads-up notification drop-in */
        @keyframes panicToastDrop {
            from {
                opacity: 0;
                transform: translateY(-18px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .panic-toast-animate {
            animation: panicToastDrop 260ms var(--panic-ease) both;
        }

        /* One-shot tactile resistance shake when malware blocks out-of-order uninstall */
        @keyframes panicMalwareShake {
            0%, 100% { transform: translateX(0); }
            18% { transform: translateX(-7px); }
            36% { transform: translateX(6px); }
            54% { transform: translateX(-5px); }
            72% { transform: translateX(4px); }
            88% { transform: translateX(-2px); }
        }

        .panic-phone-shell.is-shaking {
            animation: panicMalwareShake 320ms ease-out 1;
        }

        /* Single highlight pulse on balance drain tick (never loops) */
        @keyframes panicDrainFlash {
            0% { background-color: rgba(220, 38, 38, 0.36); }
            100% { background-color: rgba(220, 38, 38, 0.14); }
        }

        .panic-drain-tick {
            animation: panicDrainFlash 420ms ease-out 1;
        }

        /* Guided Coach Target Highlight (static high-contrast lime ring, no annoying endless bounce) */
        .panic-guide-ring {
            box-shadow: 0 0 0 2.5px #C6F432, 0 0 0 5px rgba(198, 244, 50, 0.22);
        }

        .panic-guide-ring-light {
            box-shadow: 0 0 0 2.5px #1E63E9, 0 0 0 5px rgba(30, 99, 233, 0.18);
        }

        /* Tactile press feedback for interactive phone controls */
        .panic-tap {
            cursor: pointer;
            transition: transform 110ms ease, background-color 160ms ease, border-color 160ms ease, color 160ms ease;
        }

        .panic-tap:active:not(:disabled) {
            transform: scale(0.96);
        }

        .panic-tap:focus-visible,
        .panic-root button:focus-visible,
        .panic-root a:focus-visible,
        .panic-root input:focus-visible {
            outline: 2px solid #1E63E9;
            outline-offset: 2px;
        }

        /* Guard authentic OS surfaces inside the virtual phone from global html.dark overrides */
        html.dark .panic-os-light-surface {
            background-color: #F8FAFC !important;
            color: #0F172A !important;
        }

        html.dark .panic-os-light-card {
            background-color: #FFFFFF !important;
            color: #0F172A !important;
            border-color: #E2E8F0 !important;
        }

        html.dark .panic-os-muted {
            color: #475569 !important;
        }

        html.dark .panic-os-wa-bg {
            background-color: #ECE5DD !important;
            color: #0F172A !important;
        }

        html.dark .panic-os-wa-header {
            background-color: #075E54 !important;
            color: #FFFFFF !important;
        }

        html.dark .panic-os-lime-badge {
            background-color: #C6F432 !important;
            color: #0A0A0A !important;
        }

        /* Explicit Reduce Motion Mode (user toggle OR OS prefers-reduced-motion) */
        .panic-root.panic-reduced-motion *,
        .panic-root.panic-reduced-motion *::before,
        .panic-root.panic-reduced-motion *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }

        .panic-root.panic-reduced-motion .panic-tap:active:not(:disabled) {
            transform: none !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .panic-root *,
            .panic-root *::before,
            .panic-root *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }

            .panic-root .panic-tap:active:not(:disabled) {
                transform: none !important;
            }
        }
    </style>

    <section
        x-data="panicSimulator({
            isEn: @js($isEn),
            isAuth: @js($isAuth),
            recordUrl: @js(route('minigame.panic-simulator.record')),
            csrfToken: @js(csrf_token())
        })"
        x-init="init()"
        @keydown.escape.window="handleEscape()"
        @pointermove.window="onShadeDragMove($event)"
        @pointerup.window="onShadeDragEnd()"
        @pointercancel.window="onShadeDragEnd()"
        :class="{ 'panic-reduced-motion': reduceMotion }"
        class="panic-root min-h-screen pt-24 sm:pt-28 pb-16 px-4 sm:px-6 lg:px-8 {{ $isDark ? 'bg-[#0B1120] text-slate-100' : 'bg-[#F3F5F9] text-ink' }}"
    >
        <div class="max-w-6xl mx-auto space-y-5">

            <!-- Top Command Bar: Navigation, Title, Difficulty, Guided Mode & Reduce Motion -->
            <header class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 pb-4 border-b {{ $isDark ? 'border-slate-800' : 'border-slate-200/90' }}">
                <div class="flex items-start sm:items-center gap-3.5">
                    <a
                        href="{{ route('minigame.index') }}"
                        aria-label="{{ $isEn ? 'Back to Minigame Hub' : 'Kembali ke Arena Minigame' }}"
                        class="panic-tap shrink-0 inline-flex items-center justify-center w-11 h-11 rounded-xl border {{ $isDark ? 'bg-slate-900 border-slate-700 text-slate-100 hover:border-sky' : 'bg-white border-slate-300 text-ink hover:border-sky' }}"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5" />
                            <path d="M12 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="font-heading font-extrabold text-xl sm:text-2xl tracking-tight {{ $isDark ? 'text-white' : 'text-ink' }}">
                            {{ $isEn ? '60 Seconds to Save Your Phone' : '60 Detik Selamatkan HP-mu!' }}
                        </h1>
                        <p class="mt-0.5 text-xs sm:text-sm {{ $isDark ? 'text-slate-300' : 'text-slate-600' }}">
                            {{ $isEn
                                ? 'Pick 1 of 3 real emergency cyber cases and rescue the device directly on the interactive Android screen.'
                                : 'Pilih 1 dari 3 kasus darurat siber nyata, lalu selamatkan perangkat langsung di layar HP Android interaktif.' }}
                        </p>
                    </div>
                </div>

                <!-- Right Controls: Mode Switcher + Guided Beginner Coach + Reduce Motion Toggle -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Difficulty Mode Switcher -->
                    <div
                        role="group"
                        aria-label="{{ $isEn ? 'Simulation difficulty mode' : 'Mode kesulitan simulasi' }}"
                        class="inline-flex items-center rounded-xl border p-1 {{ $isDark ? 'bg-slate-900 border-slate-700' : 'bg-white border-slate-300' }}"
                    >
                        <button
                            type="button"
                            @click="selectMode('standard')"
                            :aria-pressed="mode === 'standard'"
                            :class="mode === 'standard'
                                ? 'bg-[#1E63E9] text-white'
                                : '{{ $isDark ? 'text-slate-300 hover:text-white' : 'text-slate-700 hover:text-ink' }}'"
                            class="panic-tap min-h-[38px] px-3 py-1.5 rounded-lg text-xs font-heading font-bold"
                        >
                            {{ $isEn ? 'Standard · 60s' : 'Standar · 60d' }}
                        </button>
                        <button
                            type="button"
                            @click="selectMode('extreme')"
                            :aria-pressed="mode === 'extreme'"
                            :class="mode === 'extreme'
                                ? 'bg-[#DC2626] text-white'
                                : '{{ $isDark ? 'text-slate-300 hover:text-white' : 'text-slate-700 hover:text-ink' }}'"
                            class="panic-tap min-h-[38px] px-3 py-1.5 rounded-lg text-xs font-heading font-bold"
                        >
                            {{ $isEn ? 'Extreme · 45s' : 'Ekstrem · 45d' }}
                        </button>
                    </div>

                    <!-- Beginner Guided Coach Toggle -->
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="guidedMode"
                        @click="guidedMode = !guidedMode"
                        class="panic-tap inline-flex items-center gap-2 min-h-[46px] px-3.5 py-2 rounded-xl border text-xs font-heading font-bold {{ $isDark ? 'bg-slate-900 border-slate-700 text-slate-100 hover:border-slate-500' : 'bg-white border-slate-300 text-ink hover:border-slate-400' }}"
                    >
                        <span x-text="guidedMode
                            ? (isEn ? 'Guide: On' : 'Panduan Pemula: Aktif')
                            : (isEn ? 'Guide: Off' : 'Panduan Pemula: Mati')"></span>
                        <span
                            class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors"
                            :class="guidedMode ? 'bg-[#047857]' : 'bg-slate-400'"
                        >
                            <span
                                class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow-xs transition-transform"
                                :class="guidedMode ? 'translate-x-4.5' : 'translate-x-1'"
                            ></span>
                        </span>
                    </button>

                    <!-- Explicit Motion Setting Toggle (Disable / Enable Animations) -->
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="!reduceMotion"
                        @click="toggleMotion()"
                        class="panic-tap inline-flex items-center gap-2 min-h-[46px] px-3.5 py-2 rounded-xl border text-xs font-heading font-bold {{ $isDark ? 'bg-slate-900 border-slate-700 text-slate-100 hover:border-slate-500' : 'bg-white border-slate-300 text-ink hover:border-slate-400' }}"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" :class="reduceMotion ? 'text-slate-400' : 'text-[#1E63E9]'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                            </svg>
                            <span x-text="reduceMotion
                                ? (isEn ? 'Motion: Off' : 'Animasi: Nonaktif')
                                : (isEn ? 'Motion: Smooth' : 'Animasi: Halus')"></span>
                        </span>
                        <span
                            class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors"
                            :class="reduceMotion ? 'bg-slate-400' : 'bg-[#1E63E9]'"
                        >
                            <span
                                class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow-xs transition-transform"
                                :class="reduceMotion ? 'translate-x-1' : 'translate-x-4.5'"
                            ></span>
                        </span>
                    </button>
                </div>
            </header>

            <!-- 3-CASE SCENARIO SELECTOR BAR (Always accessible to pick 1 of 3 emergency cases) -->
            <div aria-label="{{ $isEn ? 'Choose emergency case' : 'Pilih kasus darurat' }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <template x-for="sc in scenarios" :key="sc.id">
                    <button
                        type="button"
                        @click="selectScenario(sc.id)"
                        :aria-pressed="scenarioId === sc.id"
                        :class="scenarioId === sc.id
                            ? '{{ $isDark ? 'bg-slate-900 border-[#1E63E9] ring-2 ring-[#1E63E9]/35' : 'bg-white border-[#1E63E9] ring-2 ring-[#1E63E9]/20 shadow-xs' }}'
                            : '{{ $isDark ? 'bg-slate-900/60 border-slate-800 hover:border-slate-600' : 'bg-white/80 border-slate-200 hover:border-slate-300' }}'"
                        class="panic-tap rounded-2xl border p-3.5 text-left flex flex-col justify-between gap-2"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="rounded-md px-2 py-0.5 text-[10px] font-heading font-extrabold uppercase tracking-wide"
                                :class="scenarioId === sc.id ? 'bg-[#1E63E9] text-white' : '{{ $isDark ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-700' }}'"
                                x-text="sc.badge"
                            ></span>
                            <span
                                x-show="scenarioId === sc.id"
                                class="text-[10px] font-extrabold text-[#1E63E9]"
                            >{{ $isEn ? 'Selected' : 'Dipilih' }}</span>
                        </div>
                        <div>
                            <h2 class="font-heading font-extrabold text-sm leading-snug {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="sc.title"></h2>
                            <p class="mt-1 text-xs leading-relaxed {{ $isDark ? 'text-slate-300' : 'text-slate-600' }}" x-text="sc.shortDesc"></p>
                        </div>
                    </button>
                </template>
            </div>

            <!-- MOBILE COMPACT LIVE HUD (Visible only on < lg while playing so mobile users never lose sight of Timer & Next Step) -->
            <div
                x-show="phase === 'playing'"
                x-cloak
                class="lg:hidden rounded-2xl border p-3 space-y-2 {{ $isDark ? 'bg-slate-900 border-slate-700' : 'bg-white border-slate-200 shadow-xs' }}"
            >
                <div class="flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-heading font-extrabold text-base tabular-nums" :class="timeLeft <= 15 ? 'text-[#DC2626]' : '{{ $isDark ? 'text-white' : 'text-ink' }}'" x-text="timeLeft + 's'"></span>
                        <span class="text-slate-400">·</span>
                        <span class="font-heading font-bold tabular-nums {{ $isDark ? 'text-slate-200' : 'text-ink' }}" x-text="formatRupiah(balance)"></span>
                    </div>
                    <span class="rounded-md bg-[#C6F432] text-[#0A0A0A] px-2 py-0.5 text-[11px] font-heading font-extrabold tabular-nums" x-text="completedCount + ' / 5'"></span>
                </div>
                <div x-show="guidedMode && currentGuideStep" class="rounded-xl bg-[#1E63E9]/10 border border-[#1E63E9]/30 px-3 py-2 text-xs {{ $isDark ? 'text-sky-200' : 'text-[#0F294A]' }}">
                    <span class="font-heading font-extrabold" x-text="(isEn ? 'Next: ' : 'Sekarang: ')"></span>
                    <span x-text="currentGuideStep?.coach"></span>
                </div>
            </div>

            <!-- Main Two-Column Interactive Workspace: Phone Chassis (Left) + Forensic Console (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- LEFT COLUMN: Live Interactive Android Smartphone Chassis -->
                <div class="lg:col-span-5 flex flex-col items-center space-y-3">

                    <!-- ACTIVE BEGINNER COACH PILL ABOVE PHONE (Shows 1 clear next action when Guided Mode is ON) -->
                    <div
                        x-show="phase === 'playing' && guidedMode && currentGuideStep"
                        x-cloak
                        class="w-full max-w-[388px] rounded-2xl border px-3.5 py-2.5 flex items-start gap-2.5 {{ $isDark ? 'bg-slate-900 border-[#C6F432]/60 text-slate-100' : 'bg-white border-[#1E63E9]/50 text-ink shadow-xs' }}"
                    >
                        <span class="mt-0.5 shrink-0 rounded-lg bg-[#C6F432] text-[#0A0A0A] px-2 py-0.5 text-[10px] font-heading font-extrabold uppercase">
                            <span x-text="(isEn ? 'Step ' : 'Langkah ') + (currentGuideStep?.num || 1)"></span>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-heading font-bold leading-snug" x-text="currentGuideStep?.coach"></p>
                            <p class="text-[11px] mt-0.5 leading-snug {{ $isDark ? 'text-slate-300' : 'text-slate-600' }}" x-text="currentGuideStep?.where"></p>
                        </div>
                    </div>

                    <div class="relative w-full max-w-[388px]">
                        <!-- Flashlight Top Beam Effect (visible only when Flashlight tile is ON) -->
                        <div
                            x-show="flashlight"
                            x-cloak
                            aria-hidden="true"
                            class="panic-flashlight-beam pointer-events-none absolute -top-6 left-1/2 -translate-x-1/2 w-[260px] h-14 rounded-full"
                        ></div>

                        <!-- Hardware Frame -->
                        <div
                            :class="{ 'is-shaking': shaking }"
                            class="panic-phone-shell relative rounded-[42px] p-[11px] border border-slate-700/80 select-none"
                        >
                            <!-- Side Hardware Rockers (visual hardware detail) -->
                            <div aria-hidden="true" class="hidden sm:block absolute -right-[3px] top-28 w-[3px] h-14 rounded-r bg-slate-600"></div>
                            <div aria-hidden="true" class="hidden sm:block absolute -right-[3px] top-46 w-[3px] h-9 rounded-r bg-slate-600"></div>

                            <!-- Inner Phone Display -->
                            <div
                                :style="brightness !== 100 ? 'filter: brightness(' + brightness + '%)' : ''"
                                class="panic-phone-screen relative rounded-[32px] overflow-hidden flex flex-col h-[646px] sm:h-[676px] border border-slate-800"
                            >
                                <!-- ANDROID STATUS BAR (Supports BOTH real Pull-Down Drag Gesture AND 1-Click!) -->
                                <div
                                    @pointerdown="onStatusBarPointerDown($event)"
                                    style="touch-action: none;"
                                    :class="isGuideTarget('status_bar') ? 'panic-guide-ring' : ''"
                                    class="relative z-40 shrink-0 bg-[#090D16] text-white cursor-grab active:cursor-grabbing"
                                >
                                    <button
                                        type="button"
                                        @click="onStatusBarClick()"
                                        :aria-expanded="shadeOpen"
                                        aria-label="{{ $isEn ? 'Drag down or tap to open Quick Settings' : 'Tarik ke bawah atau ketuk untuk membuka Quick Settings' }}"
                                        class="panic-tap w-full flex items-center justify-between px-4 pt-2.5 pb-1 text-[11px] font-semibold tracking-tight hover:bg-white/5"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span class="tabular-nums font-bold" x-text="clockLabel">09:41</span>
                                            <span
                                                x-show="phase === 'playing' && !isNetworkIsolated"
                                                class="inline-flex items-center gap-1 rounded bg-[#DC2626] px-1.5 py-0.5 text-[9px] font-extrabold text-white"
                                                x-text="scenarioId === 'wa_hijack' ? 'WA' : 'OTP'"
                                            ></span>
                                            <span x-show="flashlight" class="text-[9px] font-bold text-amber-300">
                                                {{ $isEn ? 'TORCH' : 'SENTER' }}
                                            </span>
                                        </div>

                                        <!-- Punch-hole Camera -->
                                        <span aria-hidden="true" class="w-3 h-3 rounded-full bg-black border border-slate-700"></span>

                                        <!-- Signal & Battery Cluster -->
                                        <div class="flex items-center gap-1.5 text-slate-200">
                                            <template x-if="airplaneMode">
                                                <span class="inline-flex items-center gap-1 text-[#C6F432] font-bold text-[10px]">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                                    </svg>
                                                </span>
                                            </template>
                                            <template x-if="!airplaneMode">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <svg x-show="wifi" class="w-3.5 h-3.5" :class="scenarioId === 'phishing_bank' ? 'text-amber-300' : 'text-sky-400'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                                        <path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>
                                                    </svg>
                                                    <span x-show="mobileData" class="text-[10px] font-extrabold text-white">4G</span>
                                                    <span x-show="!wifi && !mobileData" class="text-[10px] font-bold text-slate-400">OFF</span>
                                                </span>
                                            </template>
                                            <span class="tabular-nums text-[10px] font-bold text-slate-300" x-text="battery + '%'">74%</span>
                                        </div>
                                    </button>

                                    <!-- Pull-down bar handle (Clear Drag OR Click affordance) -->
                                    <button
                                        type="button"
                                        @click="onStatusBarClick()"
                                        aria-label="{{ $isEn ? 'Pull down Quick Settings' : 'Tarik ke bawah Quick Settings' }}"
                                        class="panic-tap w-full flex items-center justify-center gap-1.5 pb-1.5 pt-0.5 text-[10px] font-bold"
                                        :class="isGuideTarget('status_bar') ? 'text-[#C6F432]' : 'text-slate-300 hover:text-white'"
                                    >
                                        <span class="w-8 h-1 rounded-full" :class="isGuideTarget('status_bar') ? 'bg-[#C6F432]' : 'bg-slate-500'"></span>
                                        <span x-text="shadeOpen
                                            ? (isEn ? 'Close Quick Settings ▲' : 'Tutup Quick Settings ▲')
                                            : (isEn ? 'Drag / Tap Quick Settings ▼' : 'Tarik / Klik Quick Settings ▼')"></span>
                                    </button>
                                </div>

                                <!-- PULL-DOWN QUICK SETTINGS SHADE (Interactive drag-down & swipe-up) -->
                                <div
                                    :class="{ 'is-dragging': isDraggingShade }"
                                    :style="shadeTransformStyle"
                                    class="panic-shade absolute inset-x-0 top-[56px] bottom-0 z-30 bg-[#0B1220]/97 p-4 flex flex-col justify-between"
                                >
                                    <div class="space-y-3.5 overflow-y-auto">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-heading font-bold text-slate-200">
                                                {{ $isEn ? 'Quick Settings' : 'Pengaturan Cepat' }}
                                            </span>
                                            <button
                                                type="button"
                                                @click="shadeOpen = false"
                                                class="panic-tap rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 px-2.5 py-1.5 text-[11px] font-bold"
                                            >
                                                {{ $isEn ? 'Close' : 'Tutup' }}
                                            </button>
                                        </div>

                                        <!-- 4 Working Quick Settings Tiles -->
                                        <div class="grid grid-cols-2 gap-2.5">
                                            <!-- Tile 1: Mode Pesawat -->
                                            <button
                                                type="button"
                                                @click="toggleAirplaneMode()"
                                                :aria-pressed="airplaneMode"
                                                :class="[
                                                    airplaneMode ? 'bg-[#1E63E9] text-white border-[#3B82F6]' : 'bg-slate-800/95 text-slate-200 border-slate-700 hover:bg-slate-700',
                                                    isGuideTarget('tile_airplane') ? 'panic-guide-ring' : ''
                                                ]"
                                                class="panic-tap min-h-[64px] rounded-2xl border p-3 text-left flex items-center gap-2.5"
                                            >
                                                <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="airplaneMode ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-200'">
                                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                                    </svg>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-xs font-heading font-bold truncate">
                                                        {{ $isEn ? 'Airplane Mode' : 'Mode Pesawat' }}
                                                    </span>
                                                    <span class="block text-[10px]" :class="airplaneMode ? 'text-blue-100' : 'text-slate-400'" x-text="airplaneMode ? (isEn ? 'On' : 'Aktif') : (isEn ? 'Off' : 'Mati')"></span>
                                                </span>
                                            </button>

                                            <!-- Tile 2: Wi-Fi -->
                                            <button
                                                type="button"
                                                @click="toggleWifi()"
                                                :aria-pressed="wifi && !airplaneMode"
                                                :class="[
                                                    wifi && !airplaneMode ? 'bg-[#1E63E9] text-white border-[#3B82F6]' : 'bg-slate-800/95 text-slate-200 border-slate-700 hover:bg-slate-700',
                                                    isGuideTarget('tile_wifi') ? 'panic-guide-ring' : ''
                                                ]"
                                                class="panic-tap min-h-[64px] rounded-2xl border p-3 text-left flex items-center gap-2.5"
                                            >
                                                <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="wifi && !airplaneMode ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-200'">
                                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>
                                                    </svg>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-xs font-heading font-bold truncate">Wi-Fi</span>
                                                    <span class="block text-[10px] truncate" :class="wifi && !airplaneMode ? 'text-blue-100' : 'text-slate-400'" x-text="wifi && !airplaneMode ? wifiSsid : (isEn ? 'Off' : 'Mati')"></span>
                                                </span>
                                            </button>

                                            <!-- Tile 3: Mobile Data -->
                                            <button
                                                type="button"
                                                @click="toggleMobileData()"
                                                :aria-pressed="mobileData && !airplaneMode"
                                                :class="mobileData && !airplaneMode
                                                    ? 'bg-[#1E63E9] text-white border-[#3B82F6]'
                                                    : 'bg-slate-800/95 text-slate-200 border-slate-700 hover:bg-slate-700'"
                                                class="panic-tap min-h-[64px] rounded-2xl border p-3 text-left flex items-center gap-2.5"
                                            >
                                                <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="mobileData && !airplaneMode ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-200'">
                                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M7 17V7"/><path d="M3 11l4-4 4 4"/><path d="M17 7v10"/><path d="M13 13l4 4 4-4"/>
                                                    </svg>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-xs font-heading font-bold truncate">
                                                        {{ $isEn ? 'Mobile Data' : 'Data Seluler' }}
                                                    </span>
                                                    <span class="block text-[10px]" :class="mobileData && !airplaneMode ? 'text-blue-100' : 'text-slate-400'" x-text="mobileData && !airplaneMode ? '4G LTE' : (isEn ? 'Off' : 'Mati')"></span>
                                                </span>
                                            </button>

                                            <!-- Tile 4: Flashlight -->
                                            <button
                                                type="button"
                                                @click="toggleFlashlight()"
                                                :aria-pressed="flashlight"
                                                :class="flashlight
                                                    ? 'bg-amber-400 text-[#0A0A0A] border-amber-300'
                                                    : 'bg-slate-800/95 text-slate-200 border-slate-700 hover:bg-slate-700'"
                                                class="panic-tap min-h-[64px] rounded-2xl border p-3 text-left flex items-center gap-2.5"
                                            >
                                                <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="flashlight ? 'bg-black/10 text-[#0A0A0A]' : 'bg-slate-700 text-slate-200'">
                                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                                                    </svg>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-xs font-heading font-bold truncate">
                                                        {{ $isEn ? 'Flashlight' : 'Senter' }}
                                                    </span>
                                                    <span class="block text-[10px]" :class="flashlight ? 'text-slate-900' : 'text-slate-400'" x-text="flashlight ? (isEn ? 'On' : 'Menyala') : (isEn ? 'Off' : 'Mati')"></span>
                                                </span>
                                            </button>
                                        </div>

                                        <!-- Working Screen Brightness Slider -->
                                        <div class="rounded-2xl bg-slate-800/80 border border-slate-700 p-3">
                                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-300 mb-2">
                                                <label for="panic-brightness">{{ $isEn ? 'Screen Brightness' : 'Kecerahan Layar' }}</label>
                                                <span class="tabular-nums" x-text="brightness + '%'"></span>
                                            </div>
                                            <input
                                                id="panic-brightness"
                                                type="range"
                                                min="75"
                                                max="105"
                                                x-model.number="brightness"
                                                class="w-full accent-[#1E63E9] cursor-pointer"
                                            >
                                        </div>

                                        <!-- Active Notification Summary inside Shade -->
                                        <div class="rounded-2xl bg-slate-900 border border-slate-700 p-3 space-y-2">
                                            <p class="text-[10px] font-heading font-bold uppercase tracking-wider text-slate-400">
                                                {{ $isEn ? 'System Alerts' : 'Status Jaringan' }}
                                            </p>
                                            <div x-show="!isNetworkIsolated" class="rounded-xl bg-red-950/80 border border-red-700/70 p-2.5 text-xs text-red-100">
                                                <p class="font-bold" x-text="scenarioId === 'phishing_bank'
                                                    ? (isEn ? 'Unsafe Public Wi-Fi Active' : 'Terhubung ke Wi-Fi Publik Berbahaya')
                                                    : (isEn ? 'Suspicious Background Network Active' : 'Aktivitas Jaringan Mencurigakan')"></p>
                                                <p class="text-[11px] text-red-200 mt-0.5" x-text="scenarioId === 'phishing_bank'
                                                    ? (isEn ? 'Turn off Free_WiFi_Cafe_Gratis or enable Airplane Mode immediately.' : 'Matikan Free_WiFi_Cafe_Gratis atau aktifkan Mode Pesawat sekarang.')
                                                    : (isEn ? 'Enable Airplane Mode to sever remote attacker access.' : 'Aktifkan Mode Pesawat untuk memutus kendali jarak jauh peretas.')"></p>
                                            </div>
                                            <div x-show="isNetworkIsolated" class="rounded-xl bg-emerald-950/80 border border-emerald-700/70 p-2.5 text-xs text-emerald-100">
                                                <p class="font-bold">{{ $isEn ? 'Network Secured' : 'Koneksi Berbahaya Diputus' }}</p>
                                                <p class="text-[11px] text-emerald-200 mt-0.5">
                                                    {{ $isEn ? 'Continue completing the remaining steps on screen.' : 'Lanjutkan menuntaskan langkah penyelamatan lainnya.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Swipe-Up Close Handle -->
                                    <button
                                        type="button"
                                        @click="shadeOpen = false"
                                        @pointerdown="onShadeClosePointerDown($event)"
                                        style="touch-action: none;"
                                        class="panic-tap mt-3 w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-heading font-bold text-slate-200"
                                    >
                                        {{ $isEn ? 'Swipe Up / Tap to Return' : 'Geser Atas / Ketuk untuk Kembali' }}
                                    </button>
                                </div>

                                <!-- HEADS-UP LIVE NOTIFICATION TOAST (OTP / Trap / System Feedback) -->
                                <div
                                    x-show="activeToast !== null"
                                    x-cloak
                                    class="panic-toast-animate absolute top-[62px] inset-x-3 z-[35]"
                                >
                                    <div
                                        :class="activeToast?.kind === 'trap'
                                            ? 'bg-[#7F1D1D] border-red-400 text-white'
                                            : (activeToast?.kind === 'success'
                                                ? 'bg-[#064E3B] border-emerald-400 text-white'
                                                : 'bg-[#1E293B] border-slate-600 text-white')"
                                        class="rounded-2xl border p-3 shadow-xl flex items-start gap-2.5"
                                    >
                                        <button
                                            type="button"
                                            @click="clickToast()"
                                            class="panic-tap flex-1 text-left min-w-0"
                                        >
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider" :class="activeToast?.kind === 'trap' ? 'text-amber-300' : 'text-sky-300'" x-text="activeToast?.sender"></span>
                                                <span class="text-[10px] text-slate-300">{{ $isEn ? 'now' : 'baru saja' }}</span>
                                            </div>
                                            <p class="text-xs font-bold text-white mt-0.5 leading-snug" x-text="activeToast?.title"></p>
                                            <p class="text-[11px] text-slate-200 mt-0.5 leading-snug" x-text="activeToast?.body"></p>
                                        </button>
                                        <button
                                            type="button"
                                            @click="dismissToast()"
                                            aria-label="{{ $isEn ? 'Dismiss notification' : 'Tutup notifikasi' }}"
                                            class="panic-tap shrink-0 w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white text-xs font-bold"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                </div>

                                <!-- MALWARE RESISTANCE / SYSTEM ALERT DIALOG OVERLAY -->
                                <div
                                    x-show="alertModal !== null"
                                    x-cloak
                                    role="alertdialog"
                                    aria-modal="true"
                                    class="absolute inset-0 z-[45] bg-black/75 flex items-center justify-center p-4"
                                >
                                    <div class="panic-screen-animate w-full max-w-[314px] rounded-2xl bg-[#111827] border border-red-500/70 p-4 text-white shadow-2xl space-y-3">
                                        <div class="flex items-center gap-2 text-red-400">
                                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                            </svg>
                                            <h3 class="font-heading font-extrabold text-sm text-white" x-text="alertModal?.title"></h3>
                                        </div>
                                        <p class="text-xs text-slate-200 leading-relaxed" x-text="alertModal?.body"></p>
                                        <p x-show="mode === 'standard'" class="text-[11px] text-slate-400">
                                            {{ $isEn ? 'Timer paused while you read this notice (Standard Mode).' : 'Waktu dijeda sebentar saat Anda membaca peringatan ini (Mode Standar).' }}
                                        </p>
                                        <button
                                            type="button"
                                            @click="alertModal = null"
                                            class="panic-tap w-full min-h-[42px] rounded-xl bg-[#DC2626] hover:bg-red-700 text-white text-xs font-heading font-bold"
                                        >
                                            {{ $isEn ? 'Understood & Continue' : 'Mengerti & Lanjutkan' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- INTRO BRIEFING SCREEN (Reflects the selected Case 1 / 2 / 3) -->
                                <div
                                    x-show="phase === 'intro'"
                                    class="relative z-20 flex-1 bg-[#0B1220] p-5 flex flex-col justify-between overflow-y-auto"
                                >
                                    <div class="space-y-3.5">
                                        <div class="inline-flex items-center gap-2 rounded-lg bg-red-600/20 border border-red-500/50 px-3 py-1.5 text-[11px] font-heading font-bold text-red-200">
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                            <span x-text="activeScenario.badge + ' · ' + maxDuration + (isEn ? ' SECONDS' : ' DETIK')"></span>
                                        </div>

                                        <div class="space-y-1.5">
                                            <h2 class="font-heading font-extrabold text-lg sm:text-xl text-white leading-tight" x-text="activeScenario.headline"></h2>
                                            <p class="text-xs text-slate-300 leading-relaxed" x-text="activeScenario.story"></p>
                                        </div>

                                        <!-- Easy 3-Step How To Play Box for New Players -->
                                        <div class="rounded-2xl bg-slate-900 border border-slate-700/90 p-3.5 space-y-2">
                                            <p class="text-[11px] font-heading font-bold text-[#C6F432]">
                                                {{ $isEn ? 'How to Play (Easy for First-Timers):' : 'Cara Main (Mudah untuk Pemula):' }}
                                            </p>
                                            <ul class="space-y-1.5 text-xs text-slate-200">
                                                <li class="flex items-start gap-2">
                                                    <span class="font-bold text-sky-400">1.</span>
                                                    <span>{{ $isEn ? 'Follow the lime highlight box on the phone if Beginner Guide is active.' : 'Ikuti kotak sorotan hijau-limau di layar HP saat Panduan Pemula aktif.' }}</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="font-bold text-sky-400">2.</span>
                                                    <span>{{ $isEn ? 'Drag down (or tap) the top Status Bar anytime to open Quick Settings.' : 'Tarik ke bawah (atau ketuk) bilah atas HP untuk membuka Quick Settings.' }}</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="font-bold text-sky-400">3.</span>
                                                    <span>{{ $isEn ? 'Complete all 5 rescue actions inside the phone before balance or time hits zero.' : 'Tuntaskan 5 langkah penyelamatan di dalam HP sebelum saldo atau waktu habis.' }}</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <div x-show="mode === 'extreme'" class="rounded-xl bg-amber-500/15 border border-amber-400/40 p-3 text-[11px] text-amber-100 leading-relaxed">
                                            <strong class="font-bold">{{ $isEn ? 'Extreme Warning:' : 'Peringatan Mode Ekstrem:' }}</strong>
                                            {{ $isEn
                                                ? '45-second limit, faster drain, and fake bank CS pop-up notifications will try to trick you. Close them instead of tapping.'
                                                : 'Batas 45 detik, pengurasan lebih cepat, dan akan muncul notifikasi jebakan CS Bank palsu. Tutup notifikasi tersebut, jangan diklik.' }}
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="startSimulation()"
                                        class="panic-tap mt-4 w-full min-h-[48px] rounded-2xl bg-[#C6F432] hover:bg-[#b5e025] text-[#0A0A0A] font-heading font-extrabold text-sm flex items-center justify-center gap-2"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <polygon points="5 3 19 12 5 21 5 3" />
                                        </svg>
                                        <span x-text="isEn ? 'Start Emergency Rescue' : 'Mulai Simulasi Sekarang'"></span>
                                    </button>
                                </div>

                                <!-- ACTIVE ANDROID VIEWPORT (phase !== 'intro') -->
                                <div x-show="phase !== 'intro'" x-cloak class="relative flex-1 flex flex-col overflow-hidden">

                                    <!-- SCREEN 1: ANDROID HOME SCREEN -->
                                    <div
                                        x-show="screen === 'home'"
                                        class="panic-screen-animate flex-1 bg-gradient-to-b from-[#0F172A] via-[#13203B] to-[#0B1220] p-4 flex flex-col justify-between overflow-y-auto"
                                    >
                                        <div class="space-y-4">
                                            <!-- Live Exposure / Balance Widget -->
                                            <button
                                                type="button"
                                                @click="openApp(scenarioId === 'wa_hijack' ? 'whatsapp' : 'mbanking')"
                                                :class="{ 'panic-drain-tick': drainTick }"
                                                class="panic-tap w-full rounded-2xl border p-3.5 text-left transition-colors"
                                                :style="isDrainStopped
                                                    ? 'background-color: rgba(6, 78, 59, 0.45); border-color: rgba(52, 211, 153, 0.55);'
                                                    : 'background-color: rgba(127, 29, 29, 0.35); border-color: rgba(248, 113, 113, 0.55);'"
                                            >
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="text-[10px] font-heading font-bold uppercase tracking-wider text-slate-200" x-text="activeScenario.widgetLabel"></span>
                                                    <span
                                                        class="rounded-md px-2 py-0.5 text-[10px] font-extrabold"
                                                        :class="isDrainStopped ? 'bg-[#C6F432] text-[#0A0A0A]' : 'bg-[#DC2626] text-white'"
                                                        x-text="isDrainStopped
                                                            ? (isEn ? 'DRAIN STOPPED' : 'AMAN TERHENTI')
                                                            : (isEn ? 'DRAINING LIVE' : 'TERKURAS AKTIF')"
                                                    ></span>
                                                </div>
                                                <p class="mt-1.5 font-heading font-extrabold text-2xl text-white tabular-nums" x-text="formatRupiah(balance)"></p>
                                                <p class="mt-1 text-[11px] text-slate-300" x-text="isDrainStopped ? activeScenario.widgetSafeText : activeScenario.widgetDangerText"></p>
                                            </button>

                                            <!-- Quick Action Cue for Status Bar Pull-Down when Step 1 needs Quick Settings -->
                                            <button
                                                x-show="phase === 'playing' && isGuideTarget('status_bar')"
                                                type="button"
                                                @click="shadeOpen = true"
                                                class="panic-tap w-full rounded-xl bg-[#C6F432]/15 hover:bg-[#C6F432]/25 border border-[#C6F432]/60 px-3 py-2 text-left flex items-center justify-between gap-2"
                                            >
                                                <span class="text-[11px] font-bold text-white" x-text="scenarioId === 'phishing_bank'
                                                    ? (isEn ? 'Drag Status Bar down or tap here to turn off Public Wi-Fi' : 'Tarik bilah atas atau ketuk di sini untuk matikan Wi-Fi Publik')
                                                    : (isEn ? 'Drag Status Bar down or tap here to enable Airplane Mode' : 'Tarik bilah atas atau ketuk di sini untuk Mode Pesawat')"></span>
                                                <span class="text-[10px] font-extrabold text-[#C6F432] shrink-0">{{ $isEn ? 'OPEN' : 'BUKA' }}</span>
                                            </button>

                                            <!-- App Grid (5 Real Interactive Apps + Optional Malware Icon) -->
                                            <div class="grid grid-cols-3 gap-3 pt-1">
                                                <!-- App 1: Pengaturan / Settings -->
                                                <button
                                                    type="button"
                                                    @click="openApp('settings')"
                                                    :class="isGuideTarget('app_settings') ? 'panic-guide-ring bg-white/10' : 'hover:bg-white/10'"
                                                    class="panic-tap relative flex flex-col items-center gap-1.5 p-2 rounded-2xl"
                                                >
                                                    <span class="relative w-13 h-13">
                                                        <span class="w-full h-full rounded-2xl bg-slate-700 border border-slate-500/60 flex items-center justify-center text-white shadow-md">
                                                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <circle cx="12" cy="12" r="3"/>
                                                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.6.85 1 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                                                            </svg>
                                                        </span>
                                                        <span x-show="settingsBadgeCount > 0" class="absolute -top-1.5 -right-1.5 z-10 min-w-[20px] h-5 px-1 rounded-full bg-[#DC2626] ring-2 ring-[#0F172A] text-white text-[10px] font-extrabold flex items-center justify-center" x-text="settingsBadgeCount"></span>
                                                    </span>
                                                    <span class="text-[11px] font-semibold text-slate-100">{{ $isEn ? 'Settings' : 'Pengaturan' }}</span>
                                                </button>

                                                <!-- App 2: WhatsApp (FIXED: Badge placed OUTSIDE overflow-hidden image clip!) -->
                                                <button
                                                    type="button"
                                                    @click="openApp('whatsapp')"
                                                    :class="isGuideTarget('app_whatsapp') ? 'panic-guide-ring bg-white/10' : 'hover:bg-white/10'"
                                                    class="panic-tap relative flex flex-col items-center gap-1.5 p-2 rounded-2xl"
                                                >
                                                    <span class="relative w-13 h-13">
                                                        <span class="block w-full h-full rounded-2xl overflow-hidden border border-emerald-400/50 shadow-md bg-[#25D366]">
                                                            <img
                                                                src="{{ asset('images/icons/wa-logo.jpg') }}"
                                                                alt="WhatsApp"
                                                                width="52"
                                                                height="52"
                                                                class="w-full h-full object-cover"
                                                            >
                                                        </span>
                                                        <span x-show="waBadgeCount > 0" class="absolute -top-1.5 -right-1.5 z-10 min-w-[20px] h-5 px-1 rounded-full bg-[#DC2626] ring-2 ring-[#0F172A] text-white text-[10px] font-extrabold flex items-center justify-center" x-text="waBadgeCount"></span>
                                                    </span>
                                                    <span class="text-[11px] font-semibold text-slate-100">WhatsApp</span>
                                                </button>

                                                <!-- App 3: Telepon / Phone -->
                                                <button
                                                    type="button"
                                                    @click="openApp('phone')"
                                                    :class="isGuideTarget('app_phone') ? 'panic-guide-ring bg-white/10' : 'hover:bg-white/10'"
                                                    class="panic-tap relative flex flex-col items-center gap-1.5 p-2 rounded-2xl"
                                                >
                                                    <span class="relative w-13 h-13">
                                                        <span class="w-full h-full rounded-2xl bg-[#1E63E9] border border-sky-400/50 flex items-center justify-center text-white shadow-md">
                                                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/>
                                                            </svg>
                                                        </span>
                                                        <span x-show="!steps.s5" class="absolute -top-1.5 -right-1.5 z-10 min-w-[20px] h-5 px-1 rounded-full bg-amber-400 ring-2 ring-[#0F172A] text-[#0A0A0A] text-[10px] font-extrabold flex items-center justify-center">1</span>
                                                    </span>
                                                    <span class="text-[11px] font-semibold text-slate-100">{{ $isEn ? 'Phone' : 'Telepon' }}</span>
                                                </button>

                                                <!-- App 4: Pesan SMS -->
                                                <button
                                                    type="button"
                                                    @click="openApp('sms')"
                                                    :class="isGuideTarget('app_sms') ? 'panic-guide-ring bg-white/10' : 'hover:bg-white/10'"
                                                    class="panic-tap relative flex flex-col items-center gap-1.5 p-2 rounded-2xl"
                                                >
                                                    <span class="relative w-13 h-13">
                                                        <span class="w-full h-full rounded-2xl bg-sky-600 border border-sky-400/50 flex items-center justify-center text-white shadow-md">
                                                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                                            </svg>
                                                        </span>
                                                        <span x-show="smsList.length > 0 && !(scenarioId === 'wa_hijack' && steps.s4)" class="absolute -top-1.5 -right-1.5 z-10 min-w-[20px] h-5 px-1 rounded-full bg-[#DC2626] ring-2 ring-[#0F172A] text-white text-[10px] font-extrabold flex items-center justify-center" x-text="smsList.length"></span>
                                                    </span>
                                                    <span class="text-[11px] font-semibold text-slate-100">{{ $isEn ? 'Messages' : 'Pesan SMS' }}</span>
                                                </button>

                                                <!-- App 5: m-Banking BRImo (FIXED: Badge placed OUTSIDE overflow-hidden image clip!) -->
                                                <button
                                                    type="button"
                                                    @click="openApp('mbanking')"
                                                    :class="isGuideTarget('app_mbanking') ? 'panic-guide-ring bg-white/10' : 'hover:bg-white/10'"
                                                    class="panic-tap relative flex flex-col items-center gap-1.5 p-2 rounded-2xl"
                                                >
                                                    <span class="relative w-13 h-13">
                                                        <span class="block w-full h-full rounded-2xl overflow-hidden border border-sky-400/40 shadow-md bg-[#00529C]">
                                                            <img
                                                                src="{{ asset('images/icons/brimo-logo.jpg') }}"
                                                                alt="BRImo m-Banking"
                                                                width="52"
                                                                height="52"
                                                                class="w-full h-full object-cover"
                                                            >
                                                        </span>
                                                        <span x-show="mbankingBadgeCount > 0" class="absolute -top-1.5 -right-1.5 z-10 min-w-[20px] h-5 px-1 rounded-full bg-[#DC2626] ring-2 ring-[#0F172A] text-white text-[10px] font-extrabold flex items-center justify-center" x-text="mbankingBadgeCount"></span>
                                                    </span>
                                                    <span class="text-[11px] font-semibold text-slate-100">m-Banking</span>
                                                </button>

                                                <!-- App 6: Suspicious Installed APK Icon on Home Screen (Cases 1 & 3, disappears after uninstall!) -->
                                                <button
                                                    x-show="hasMalwareApp && !malwareUninstalled"
                                                    type="button"
                                                    @click="tapMalwareHomeIcon()"
                                                    class="panic-tap relative flex flex-col items-center gap-1.5 p-2 rounded-2xl hover:bg-white/10"
                                                >
                                                    <span class="relative w-13 h-13">
                                                        <span class="w-full h-full rounded-2xl bg-red-900/90 border border-red-400/60 flex items-center justify-center text-red-100 shadow-md">
                                                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                                                            </svg>
                                                        </span>
                                                    </span>
                                                    <span class="text-[10px] font-semibold text-red-200 truncate max-w-[82px]" x-text="activeScenario.malwareShort"></span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Subtle Home Status Footer -->
                                        <div class="rounded-xl bg-black/35 border border-white/10 px-3 py-2 flex items-center justify-between text-[11px] text-slate-300">
                                            <span>{{ $isEn ? 'Containment Progress' : 'Progres Penyelamatan' }}</span>
                                            <span class="font-heading font-extrabold text-[#C6F432] tabular-nums" x-text="completedCount + ' / 5'"></span>
                                        </div>
                                    </div>

                                    <!-- SCREEN 2: PENGATURAN / SETTINGS -->
                                    <div
                                        x-show="screen === 'settings'"
                                        class="panic-screen-animate panic-os-light-surface flex-1 bg-[#F8FAFC] text-[#0F172A] flex flex-col overflow-hidden"
                                    >
                                        <!-- Settings App Bar -->
                                        <div class="shrink-0 bg-[#FFFFFF] border-b border-slate-200 px-3.5 py-3 flex items-center gap-2.5 panic-os-light-card">
                                            <button
                                                type="button"
                                                @click="settingsBack()"
                                                aria-label="{{ $isEn ? 'Back' : 'Kembali' }}"
                                                class="panic-tap w-9 h-9 rounded-lg hover:bg-slate-100 flex items-center justify-center text-[#0F172A]"
                                            >
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                                            </button>
                                            <h2 class="font-heading font-extrabold text-sm text-[#0F172A]" x-text="settingsTitle"></h2>
                                        </div>

                                        <!-- Settings Root Menu -->
                                        <div x-show="settingsView === 'root'" class="flex-1 p-3.5 space-y-2.5 overflow-y-auto">
                                            <!-- Connections -->
                                            <button
                                                type="button"
                                                @click="settingsView = 'network'"
                                                :class="isGuideTarget('settings_network') ? 'panic-guide-ring-light' : ''"
                                                class="panic-tap panic-os-light-card w-full min-h-[62px] rounded-2xl bg-white border border-slate-200 px-3.5 py-3 flex items-center justify-between gap-3 text-left hover:border-[#1E63E9]"
                                            >
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="w-10 h-10 rounded-xl bg-blue-50 text-[#1E63E9] flex items-center justify-center shrink-0">
                                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="font-heading font-bold text-xs text-[#0F172A]">{{ $isEn ? 'Connections & Network' : 'Koneksi & Jaringan' }}</p>
                                                        <p class="panic-os-muted text-[11px] text-slate-600 truncate" x-text="airplaneMode ? (isEn ? 'Airplane Mode On' : 'Mode Pesawat Aktif') : (wifi ? wifiSsid : (isEn ? 'Wi-Fi Off' : 'Wi-Fi Mati'))"></p>
                                                    </div>
                                                </div>
                                                <span x-show="isNetworkIsolated" class="panic-os-lime-badge rounded-md bg-[#C6F432] text-[#0A0A0A] px-2 py-0.5 text-[10px] font-extrabold shrink-0">OK</span>
                                            </button>

                                            <!-- Accessibility -->
                                            <button
                                                type="button"
                                                @click="settingsView = 'accessibility'"
                                                :class="isGuideTarget('settings_accessibility') ? 'panic-guide-ring-light' : ''"
                                                class="panic-tap panic-os-light-card w-full min-h-[62px] rounded-2xl bg-white border border-slate-200 px-3.5 py-3 flex items-center justify-between gap-3 text-left hover:border-[#1E63E9]"
                                            >
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="5" r="2"/><path d="M5 9h14"/><path d="M12 9v12"/><path d="M9 21l3-6 3 6"/></svg>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="font-heading font-bold text-xs text-[#0F172A]">{{ $isEn ? 'Accessibility' : 'Aksesibilitas' }}</p>
                                                        <p class="panic-os-muted text-[11px] truncate" :class="hasMalwareApp && !accessibilityRevoked ? 'text-red-700 font-semibold' : 'text-slate-600'" x-text="hasMalwareApp && !accessibilityRevoked ? (isEn ? '1 active service (Full screen control)' : '1 layanan aktif (Kontrol layar penuh)') : (isEn ? 'All suspicious services off' : 'Semua layanan asing nonaktif')"></p>
                                                    </div>
                                                </div>
                                                <span x-show="hasMalwareApp && !accessibilityRevoked" class="rounded-md bg-[#DC2626] text-white px-2 py-0.5 text-[10px] font-extrabold shrink-0">{{ $isEn ? 'ALERT' : 'AKTIF' }}</span>
                                                <span x-show="!hasMalwareApp || accessibilityRevoked" class="panic-os-lime-badge rounded-md bg-[#C6F432] text-[#0A0A0A] px-2 py-0.5 text-[10px] font-extrabold shrink-0">OK</span>
                                            </button>

                                            <!-- Installed Apps -->
                                            <button
                                                type="button"
                                                @click="settingsView = 'apps'"
                                                :class="isGuideTarget('settings_apps') ? 'panic-guide-ring-light' : ''"
                                                class="panic-tap panic-os-light-card w-full min-h-[62px] rounded-2xl bg-white border border-slate-200 px-3.5 py-3 flex items-center justify-between gap-3 text-left hover:border-[#1E63E9]"
                                            >
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="w-10 h-10 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center shrink-0">
                                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="font-heading font-bold text-xs text-[#0F172A]">{{ $isEn ? 'Installed Apps' : 'Aplikasi Terpasang' }}</p>
                                                        <p class="panic-os-muted text-[11px] text-slate-600 truncate" x-text="hasMalwareApp && !malwareUninstalled ? (isEn ? '4 apps · 1 unknown source (.APK)' : '4 aplikasi · 1 sumber tak dikenal (.APK)') : (isEn ? '3 verified apps' : '3 aplikasi terverifikasi')"></p>
                                                    </div>
                                                </div>
                                                <span x-show="hasMalwareApp && !malwareUninstalled" class="rounded-md bg-[#DC2626] text-white px-2 py-0.5 text-[10px] font-extrabold shrink-0">1</span>
                                                <span x-show="!hasMalwareApp || malwareUninstalled" class="panic-os-lime-badge rounded-md bg-[#C6F432] text-[#0A0A0A] px-2 py-0.5 text-[10px] font-extrabold shrink-0">OK</span>
                                            </button>
                                        </div>

                                        <!-- Settings Subview: Connections & Network -->
                                        <div x-show="settingsView === 'network'" class="flex-1 p-3.5 space-y-2.5 overflow-y-auto">
                                            <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-3.5 flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="font-heading font-bold text-xs text-[#0F172A]">{{ $isEn ? 'Airplane Mode' : 'Mode Pesawat' }}</p>
                                                    <p class="panic-os-muted text-[11px] text-slate-600">{{ $isEn ? 'Disconnects Wi-Fi, 4G, and SMS radio' : 'Memutus Wi-Fi, 4G, dan sinyal SMS' }}</p>
                                                </div>
                                                <button
                                                    type="button"
                                                    role="switch"
                                                    :aria-checked="airplaneMode"
                                                    @click="toggleAirplaneMode()"
                                                    :class="[airplaneMode ? 'bg-[#1E63E9]' : 'bg-slate-300', isGuideTarget('tile_airplane') ? 'panic-guide-ring-light' : '']"
                                                    class="panic-tap relative inline-flex h-7 w-12 shrink-0 items-center rounded-full"
                                                >
                                                    <span class="sr-only">{{ $isEn ? 'Toggle Airplane Mode' : 'Ubah Mode Pesawat' }}</span>
                                                    <span :class="airplaneMode ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform"></span>
                                                </button>
                                            </div>

                                            <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-3.5 flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="font-heading font-bold text-xs text-[#0F172A]">Wi-Fi</p>
                                                    <p class="panic-os-muted text-[11px] text-slate-600" x-text="wifi && !airplaneMode ? wifiSsid : (isEn ? 'Off' : 'Nonaktif')"></p>
                                                </div>
                                                <button
                                                    type="button"
                                                    role="switch"
                                                    :aria-checked="wifi && !airplaneMode"
                                                    @click="toggleWifi()"
                                                    :class="[wifi && !airplaneMode ? 'bg-[#1E63E9]' : 'bg-slate-300', isGuideTarget('tile_wifi') ? 'panic-guide-ring-light' : '']"
                                                    class="panic-tap relative inline-flex h-7 w-12 shrink-0 items-center rounded-full"
                                                >
                                                    <span class="sr-only">Toggle Wi-Fi</span>
                                                    <span :class="wifi && !airplaneMode ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform"></span>
                                                </button>
                                            </div>

                                            <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-3.5 flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="font-heading font-bold text-xs text-[#0F172A]">{{ $isEn ? 'Mobile Data (4G)' : 'Data Seluler (4G)' }}</p>
                                                    <p class="panic-os-muted text-[11px] text-slate-600" x-text="mobileData && !airplaneMode ? (isEn ? 'Connected' : 'Terhubung') : (isEn ? 'Off' : 'Nonaktif')"></p>
                                                </div>
                                                <button
                                                    type="button"
                                                    role="switch"
                                                    :aria-checked="mobileData && !airplaneMode"
                                                    @click="toggleMobileData()"
                                                    :class="mobileData && !airplaneMode ? 'bg-[#1E63E9]' : 'bg-slate-300'"
                                                    class="panic-tap relative inline-flex h-7 w-12 shrink-0 items-center rounded-full"
                                                >
                                                    <span class="sr-only">{{ $isEn ? 'Toggle Mobile Data' : 'Ubah Data Seluler' }}</span>
                                                    <span :class="mobileData && !airplaneMode ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform"></span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Settings Subview: Accessibility -->
                                        <div x-show="settingsView === 'accessibility'" class="flex-1 p-3.5 space-y-3 overflow-y-auto">
                                            <div class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-[11px] text-amber-950 leading-relaxed">
                                                {{ $isEn
                                                    ? 'Services with full Accessibility permissions can read OTP codes on screen and block uninstall clicks.'
                                                    : 'Layanan dengan izin Aksesibilitas penuh dapat membaca kode OTP di layar dan menggagalkan tombol Uninstall.' }}
                                            </div>

                                            <template x-if="hasMalwareApp">
                                                <div
                                                    :class="[
                                                        !accessibilityRevoked ? 'border-red-300 bg-red-50/70' : 'border-emerald-300 bg-emerald-50/60',
                                                        isGuideTarget('toggle_accessibility') ? 'panic-guide-ring-light' : ''
                                                    ]"
                                                    class="rounded-2xl border p-3.5 space-y-2.5"
                                                >
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <p class="font-heading font-extrabold text-xs text-[#0F172A]" x-text="activeScenario.accessibilityService"></p>
                                                            <p class="text-[11px] text-slate-700 mt-0.5" x-text="(isEn ? 'Installed by: ' : 'Dipasang oleh: ') + activeScenario.malwareName"></p>
                                                            <p class="text-[10px] font-bold mt-1" :class="!accessibilityRevoked ? 'text-red-700' : 'text-emerald-800'" x-text="!accessibilityRevoked
                                                                ? (isEn ? 'ACTIVE: Full screen & tap control' : 'AKTIF: Kontrol layar & klik otomatis')
                                                                : (isEn ? 'REVOKED: Safe to uninstall app' : 'DICABUT: Aplikasi kini bisa dihapus')"></p>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            role="switch"
                                                            :aria-checked="!accessibilityRevoked"
                                                            @click="toggleMalwareAccessibility()"
                                                            :class="!accessibilityRevoked ? 'bg-[#DC2626]' : 'bg-slate-300'"
                                                            class="panic-tap relative inline-flex h-7 w-12 shrink-0 items-center rounded-full"
                                                        >
                                                            <span class="sr-only">{{ $isEn ? 'Toggle suspicious accessibility service' : 'Matikan layanan aksesibilitas mencurigakan' }}</span>
                                                            <span :class="!accessibilityRevoked ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>

                                            <template x-if="!hasMalwareApp">
                                                <div class="rounded-2xl bg-emerald-50 border border-emerald-300 p-3.5 text-xs text-emerald-900">
                                                    {{ $isEn ? 'No suspicious Accessibility services are active in this case.' : 'Tidak ada layanan Aksesibilitas mencurigakan pada kasus ini.' }}
                                                </div>
                                            </template>

                                            <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-3.5 flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="font-heading font-bold text-xs text-[#0F172A]">TalkBack ({{ $isEn ? 'Screen Reader' : 'Pembaca Layar' }})</p>
                                                    <p class="panic-os-muted text-[11px] text-slate-600">{{ $isEn ? 'Official Android system service' : 'Layanan resmi sistem Android' }}</p>
                                                </div>
                                                <span class="rounded-md bg-slate-100 text-slate-700 px-2 py-1 text-[10px] font-bold">{{ $isEn ? 'Off' : 'Mati' }}</span>
                                            </div>
                                        </div>

                                        <!-- Settings Subview: Installed Apps -->
                                        <div x-show="settingsView === 'apps'" class="flex-1 p-3.5 space-y-2 overflow-y-auto">
                                            <template x-for="app in installedApps" :key="app.id">
                                                <button
                                                    type="button"
                                                    @click="openAppDetail(app.id)"
                                                    class="panic-tap panic-os-light-card w-full min-h-[60px] rounded-2xl bg-white border px-3.5 py-2.5 flex items-center justify-between gap-3 text-left"
                                                    :class="[
                                                        app.malicious ? 'border-red-300 hover:border-red-500' : 'border-slate-200 hover:border-[#1E63E9]',
                                                        app.malicious && isGuideTarget('select_malware_app') ? 'panic-guide-ring-light' : ''
                                                    ]"
                                                >
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <p class="font-heading font-bold text-xs text-[#0F172A] truncate" x-text="app.name"></p>
                                                            <span x-show="app.malicious" class="rounded bg-red-100 text-red-800 px-1.5 py-0.5 text-[9px] font-extrabold uppercase">APK</span>
                                                        </div>
                                                        <p class="panic-os-muted text-[11px] text-slate-600 mt-0.5" x-text="app.size + ' · ' + app.source"></p>
                                                    </div>
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                                                </button>
                                            </template>
                                        </div>

                                        <!-- Settings Subview: Single App Info & Uninstall -->
                                        <div x-show="settingsView === 'app_detail' && selectedApp" class="flex-1 p-4 flex flex-col justify-between overflow-y-auto">
                                            <div class="space-y-3.5">
                                                <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-4 space-y-2">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <h3 class="font-heading font-extrabold text-sm text-[#0F172A]" x-text="selectedApp?.name"></h3>
                                                        <span
                                                            :class="selectedApp?.malicious ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800'"
                                                            class="rounded-md px-2 py-0.5 text-[10px] font-extrabold"
                                                            x-text="selectedApp?.malicious ? (isEn ? 'Unknown Source' : 'Sumber Tak Dikenal') : (isEn ? 'Verified' : 'Terverifikasi')"
                                                        ></span>
                                                    </div>
                                                    <p class="panic-os-muted text-xs text-slate-600" x-text="(isEn ? 'Package: ' : 'Paket: ') + (selectedApp?.pkg || '')"></p>
                                                    <p class="panic-os-muted text-xs text-slate-600" x-text="(isEn ? 'Size: ' : 'Ukuran: ') + (selectedApp?.size || '')"></p>
                                                </div>

                                                <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-3.5 space-y-2">
                                                    <p class="text-[11px] font-heading font-bold text-[#0F172A]">{{ $isEn ? 'Active Permissions' : 'Izin Aktif' }}</p>
                                                    <ul class="space-y-1 text-xs text-slate-700">
                                                        <template x-for="perm in (selectedApp?.permissions || [])" :key="perm">
                                                            <li class="flex items-center gap-2">
                                                                <span class="w-1.5 h-1.5 rounded-full" :class="selectedApp?.malicious ? 'bg-red-600' : 'bg-sky-600'"></span>
                                                                <span x-text="perm"></span>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="pt-4 space-y-2">
                                                <template x-if="selectedApp?.malicious">
                                                    <div class="grid grid-cols-2 gap-2.5">
                                                        <button
                                                            type="button"
                                                            @click="forceStopMalware()"
                                                            class="panic-tap min-h-[44px] rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-xs font-heading font-bold text-[#0F172A]"
                                                        >
                                                            {{ $isEn ? 'Force Stop' : 'Paksa Berhenti' }}
                                                        </button>
                                                        <button
                                                            type="button"
                                                            @click="attemptUninstallMalware()"
                                                            :class="isGuideTarget('btn_uninstall') ? 'panic-guide-ring-light' : ''"
                                                            class="panic-tap min-h-[44px] rounded-xl bg-[#DC2626] hover:bg-red-700 text-white text-xs font-heading font-extrabold"
                                                        >
                                                            {{ $isEn ? 'Uninstall' : 'Hapus Instalasi' }}
                                                        </button>
                                                    </div>
                                                </template>
                                                <template x-if="selectedApp && !selectedApp.malicious">
                                                    <p class="rounded-xl bg-slate-100 border border-slate-200 p-3 text-xs text-slate-700 text-center">
                                                        {{ $isEn ? 'This is a legitimate app and does not need to be uninstalled.' : 'Ini aplikasi resmi yang aman dan tidak perlu dihapus.' }}
                                                    </p>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SCREEN 3: WHATSAPP -->
                                    <div
                                        x-show="screen === 'whatsapp'"
                                        class="panic-screen-animate panic-os-light-surface flex-1 bg-white text-[#0F172A] flex flex-col overflow-hidden"
                                    >
                                        <!-- Authentic WhatsApp Header -->
                                        <div class="panic-os-wa-header relative shrink-0 bg-[#075E54] text-white px-3.5 py-3 flex items-center justify-between">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <button
                                                    x-show="waView !== 'chats'"
                                                    type="button"
                                                    @click="waView = 'chats'"
                                                    aria-label="{{ $isEn ? 'Back to chats' : 'Kembali ke daftar chat' }}"
                                                    class="panic-tap w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center text-white shrink-0"
                                                >
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                                                </button>
                                                <h2 class="font-heading font-extrabold text-sm text-white truncate" x-text="waView === 'linked'
                                                    ? (isEn ? 'Linked Devices' : 'Perangkat Tertaut')
                                                    : (waView === 'settings'
                                                        ? (isEn ? 'Security Settings' : 'Setelan Keamanan')
                                                        : (waView === 'chat_detail' ? (activeWaChat?.name || 'WhatsApp') : 'WhatsApp'))"></h2>
                                            </div>

                                            <!-- Overflow 3-dot Menu -->
                                            <div class="relative shrink-0">
                                                <button
                                                    type="button"
                                                    @click="waMenuOpen = !waMenuOpen"
                                                    :aria-expanded="waMenuOpen"
                                                    aria-label="{{ $isEn ? 'WhatsApp menu' : 'Menu WhatsApp' }}"
                                                    :class="isGuideTarget('wa_menu') ? 'panic-guide-ring' : ''"
                                                    class="panic-tap w-9 h-9 rounded-lg hover:bg-white/15 flex items-center justify-center text-white"
                                                >
                                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                                                    </svg>
                                                </button>

                                                <div
                                                    x-show="waMenuOpen"
                                                    x-cloak
                                                    @click.outside="waMenuOpen = false"
                                                    class="panic-os-light-card absolute right-0 top-10 z-30 w-48 rounded-xl bg-white border border-slate-200 shadow-xl py-1.5 text-[#0F172A]"
                                                >
                                                    <button
                                                        type="button"
                                                        @click="waView = 'linked'; waMenuOpen = false"
                                                        class="panic-tap w-full px-3.5 py-2.5 text-left text-xs font-semibold hover:bg-slate-100 flex items-center justify-between"
                                                    >
                                                        <span>{{ $isEn ? 'Linked Devices' : 'Perangkat Tertaut' }}</span>
                                                        <span x-show="!waLoggedOut" class="w-2 h-2 rounded-full bg-red-600"></span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="waView = 'settings'; waMenuOpen = false"
                                                        class="panic-tap w-full px-3.5 py-2.5 text-left text-xs font-semibold hover:bg-slate-100 flex items-center justify-between"
                                                    >
                                                        <span>{{ $isEn ? 'Security Settings' : 'Setelan Keamanan' }}</span>
                                                        <span x-show="scenarioId === 'wa_hijack' && !waPinEnabled" class="w-2 h-2 rounded-full bg-red-600"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- User-Friendly Quick Tab Strip inside WhatsApp (Chats / Perangkat Tertaut / Keamanan) -->
                                        <div class="shrink-0 bg-[#064E46] text-emerald-100 grid grid-cols-3 text-[11px] font-heading font-bold">
                                            <button
                                                type="button"
                                                @click="waView = 'chats'"
                                                :class="[waView === 'chats' || waView === 'chat_detail' ? 'border-b-2 border-[#C6F432] text-white' : 'text-emerald-200/80 hover:text-white', isGuideTarget('wa_tab_chat') ? 'bg-white/15 text-[#C6F432]' : '']"
                                                class="panic-tap py-2 text-center"
                                            >
                                                Chat
                                            </button>
                                            <button
                                                type="button"
                                                @click="waView = 'linked'"
                                                :class="[waView === 'linked' ? 'border-b-2 border-[#C6F432] text-white' : 'text-emerald-200/80 hover:text-white', isGuideTarget('wa_tab_linked') ? 'bg-white/15 text-[#C6F432]' : '']"
                                                class="panic-tap py-2 text-center"
                                            >
                                                {{ $isEn ? 'Linked' : 'Tertaut' }}
                                            </button>
                                            <button
                                                type="button"
                                                @click="waView = 'settings'"
                                                :class="[waView === 'settings' ? 'border-b-2 border-[#C6F432] text-white' : 'text-emerald-200/80 hover:text-white', isGuideTarget('wa_tab_security') ? 'bg-white/15 text-[#C6F432]' : '']"
                                                class="panic-tap py-2 text-center"
                                            >
                                                {{ $isEn ? 'Security' : 'Keamanan' }}
                                            </button>
                                        </div>

                                        <!-- WA Chats List -->
                                        <div x-show="waView === 'chats'" class="flex-1 flex flex-col overflow-y-auto">
                                            <!-- Hijack Warning Banner inside WA -->
                                            <div x-show="!waLoggedOut && scenarioId !== 'phishing_bank'" class="bg-red-50 border-b border-red-200 px-3.5 py-2.5 flex items-center justify-between gap-2">
                                                <p class="text-[11px] font-semibold text-red-900" x-text="scenarioId === 'wa_hijack'
                                                    ? (isEn ? 'Hijacker is asking your family for Rp 2.5M transfers!' : 'Pembajak sedang mengirim pesan pinjam uang ke grup keluarga!')
                                                    : (isEn ? 'Unknown WhatsApp Web session is spamming your contacts.' : 'Sesi WhatsApp Web asing sedang mengirim spam .APK ke kontak Anda.')"></p>
                                                <button
                                                    type="button"
                                                    @click="waView = 'linked'"
                                                    class="panic-tap shrink-0 rounded-lg bg-[#DC2626] text-white px-2.5 py-1.5 text-[10px] font-extrabold"
                                                >
                                                    {{ $isEn ? 'Inspect' : 'Cek' }}
                                                </button>
                                            </div>

                                            <div class="divide-y divide-slate-200">
                                                <template x-for="chat in waChats" :key="chat.id">
                                                    <button
                                                        type="button"
                                                        @click="activeWaChat = chat; waView = 'chat_detail'"
                                                        :class="chat.id === 'keluarga' && isGuideTarget('wa_chat_family') ? 'panic-guide-ring-light bg-blue-50/50' : 'hover:bg-slate-50'"
                                                        class="panic-tap panic-os-light-card w-full px-3.5 py-3 flex items-start justify-between gap-3 text-left"
                                                    >
                                                        <div class="min-w-0">
                                                            <p class="font-heading font-bold text-xs text-[#0F172A] truncate" x-text="chat.name"></p>
                                                            <p class="panic-os-muted text-[11px] mt-0.5 truncate" :class="chat.hijacked && !waLoggedOut ? 'text-red-700 font-semibold' : 'text-slate-600'" x-text="chat.hijacked && !waLoggedOut ? chat.spamPreview : chat.normalPreview"></p>
                                                        </div>
                                                        <span class="text-[10px] text-slate-500 shrink-0" x-text="chat.time"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- WA Chat Detail -->
                                        <div x-show="waView === 'chat_detail' && activeWaChat" class="panic-os-wa-bg flex-1 bg-[#ECE5DD] p-3.5 flex flex-col justify-between overflow-y-auto">
                                            <div class="space-y-2.5">
                                                <div class="rounded-xl bg-white p-3 text-xs text-[#0F172A] shadow-xs max-w-[92%]">
                                                    <p class="font-bold text-[11px] text-[#075E54]" x-text="activeWaChat?.name"></p>
                                                    <p class="mt-1 leading-relaxed" x-text="activeWaChat?.detailMsg"></p>
                                                </div>
                                                <div x-show="activeWaChat?.hijacked" class="ml-auto rounded-xl bg-[#DCF8C6] border border-red-300 p-3 text-xs text-[#0F172A] shadow-xs max-w-[92%]">
                                                    <p class="text-[10px] font-extrabold text-red-700 uppercase">{{ $isEn ? 'Sent automatically via Linked Device' : 'Terkirim otomatis dari Perangkat Tertaut' }}</p>
                                                    <p class="mt-1 font-semibold" x-text="scenarioId === 'wa_hijack'
                                                        ? (isEn ? 'Urgent! Please transfer Rp 2,500,000 to my friend account now, I will pay tonight.' : 'Darurat! Tolong transfer Rp 2.500.000 ke rekening teman saya dulu, malam ini diganti.')
                                                        : 'Undangan_Nikah_Digital.apk (4.6 MB)'"></p>
                                                </div>
                                                <div x-show="scenarioId === 'wa_hijack' && activeWaChat?.id === 'keluarga' && waWarningSent" class="ml-auto rounded-xl bg-emerald-100 border border-emerald-400 p-3 text-xs text-emerald-950 shadow-xs max-w-[92%]">
                                                    <p class="text-[10px] font-extrabold text-emerald-800 uppercase">{{ $isEn ? 'Clarification Sent' : 'Klarifikasi Terkirim' }}</p>
                                                    <p class="mt-1 font-semibold">{{ $isEn ? 'My WhatsApp was briefly hijacked. DO NOT transfer any money!' : 'Akun WA saya barusan dibajak. JANGAN transfer uang apa pun!' }}</p>
                                                </div>
                                            </div>

                                            <div class="pt-3 space-y-2">
                                                <!-- Case 2 Step 3 Action: Send Emergency Clarification to Family Group -->
                                                <button
                                                    x-show="scenarioId === 'wa_hijack' && activeWaChat?.id === 'keluarga' && !waWarningSent"
                                                    type="button"
                                                    @click="sendFamilyClarification()"
                                                    :class="isGuideTarget('btn_wa_clarify') ? 'panic-guide-ring-light' : ''"
                                                    class="panic-tap w-full min-h-[44px] rounded-xl bg-[#1E63E9] hover:bg-blue-700 text-white text-xs font-heading font-extrabold"
                                                >
                                                    {{ $isEn ? 'Send Emergency Clarification to Group' : 'Kirim Pesan Klarifikasi Darurat ke Grup' }}
                                                </button>
                                                <button
                                                    x-show="!waLoggedOut"
                                                    type="button"
                                                    @click="waView = 'linked'"
                                                    class="panic-tap w-full min-h-[42px] rounded-xl bg-[#075E54] text-white text-xs font-heading font-bold"
                                                >
                                                    {{ $isEn ? 'Check Linked Devices' : 'Periksa Perangkat Tertaut' }}
                                                </button>
                                            </div>
                                        </div>

                                        <!-- WA Linked Devices -->
                                        <div x-show="waView === 'linked'" class="flex-1 p-4 space-y-3.5 overflow-y-auto bg-[#F8FAFC] panic-os-light-surface">
                                            <p class="text-xs text-slate-700 leading-relaxed">
                                                {{ $isEn
                                                    ? 'Devices listed below can read and send WhatsApp messages from your account.'
                                                    : 'Perangkat di bawah ini dapat membaca dan mengirim pesan WhatsApp menggunakan akun Anda.' }}
                                            </p>

                                            <template x-if="!waLoggedOut && scenarioId !== 'phishing_bank'">
                                                <div class="rounded-2xl bg-white border border-red-300 p-3.5 space-y-3 panic-os-light-card">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div>
                                                            <p class="font-heading font-extrabold text-xs text-[#0F172A]" x-text="scenarioId === 'wa_hijack' ? 'Microsoft Edge (Windows 10)' : 'Google Chrome (Windows 11)'"></p>
                                                            <p class="text-[11px] text-red-700 font-semibold mt-0.5">{{ $isEn ? 'Active now · Unknown location' : 'Aktif sekarang · Lokasi tidak dikenal' }}</p>
                                                        </div>
                                                        <span class="rounded bg-red-100 text-red-800 px-2 py-0.5 text-[10px] font-extrabold">{{ $isEn ? 'SUSPICIOUS' : 'ASING' }}</span>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        @click="logoutLinkedDevice()"
                                                        :class="isGuideTarget('btn_wa_logout') ? 'panic-guide-ring-light' : ''"
                                                        class="panic-tap w-full min-h-[44px] rounded-xl bg-[#DC2626] hover:bg-red-700 text-white text-xs font-heading font-extrabold"
                                                    >
                                                        {{ $isEn ? 'Log Out This Device' : 'Keluar (Log Out) Perangkat Ini' }}
                                                    </button>
                                                </div>
                                            </template>

                                            <template x-if="waLoggedOut || scenarioId === 'phishing_bank'">
                                                <div class="rounded-2xl bg-emerald-50 border border-emerald-300 p-4 text-center space-y-1.5">
                                                    <p class="font-heading font-extrabold text-xs text-emerald-900">{{ $isEn ? 'No Linked Devices Active' : 'Tidak Ada Perangkat Tertaut Aktif' }}</p>
                                                    <p class="text-[11px] text-emerald-800">{{ $isEn ? 'Your WhatsApp session is clean.' : 'Sesi WhatsApp Anda saat ini bersih.' }}</p>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- WA Security Settings Subview (Interactive 2FA PIN for Case 2!) -->
                                        <div x-show="waView === 'settings'" class="flex-1 p-4 space-y-3 bg-[#F8FAFC] panic-os-light-surface overflow-y-auto">
                                            <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-4 space-y-3">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div>
                                                        <p class="font-heading font-extrabold text-xs text-[#0F172A]">{{ $isEn ? 'Two-Step Verification (6-Digit PIN)' : 'Verifikasi Dua Langkah (PIN 6 Digit)' }}</p>
                                                        <p class="panic-os-muted text-[11px] text-slate-600 mt-1 leading-relaxed">
                                                            {{ $isEn
                                                                ? 'Blocks attackers from re-registering your number even if they steal an SMS OTP.'
                                                                : 'Mencegah peretas mengambil alih nomor Anda kembali meski memiliki kode SMS OTP.' }}
                                                        </p>
                                                    </div>
                                                    <span
                                                        class="rounded px-2 py-0.5 text-[10px] font-extrabold shrink-0"
                                                        :class="waPinEnabled ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                                                        x-text="waPinEnabled ? (isEn ? 'ON' : 'AKTIF') : (isEn ? 'OFF' : 'MATI')"
                                                    ></span>
                                                </div>

                                                <button
                                                    x-show="!waPinEnabled"
                                                    type="button"
                                                    @click="enableWaTwoStepPin()"
                                                    :class="isGuideTarget('btn_wa_pin') ? 'panic-guide-ring-light' : ''"
                                                    class="panic-tap w-full min-h-[44px] rounded-xl bg-[#075E54] hover:bg-[#064E46] text-white text-xs font-heading font-extrabold"
                                                >
                                                    {{ $isEn ? 'Enable 6-Digit Security PIN Now' : 'Aktifkan PIN Keamanan 6 Digit Sekarang' }}
                                                </button>

                                                <p x-show="waPinEnabled" class="rounded-xl bg-emerald-50 border border-emerald-200 p-2.5 text-xs font-semibold text-emerald-900">
                                                    {{ $isEn ? '6-Digit PIN active! Account takeover protection enabled.' : 'PIN 6 digit aktif! Akun terlindungi dari pendaftaran ulang peretas.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SCREEN 4: TELEPON / EMERGENCY DIALER (STEP 5 ALL CASES) -->
                                    <div
                                        x-show="screen === 'phone'"
                                        class="panic-screen-animate panic-os-light-surface flex-1 bg-[#F8FAFC] text-[#0F172A] flex flex-col overflow-hidden"
                                    >
                                        <!-- Active Call Connected State -->
                                        <div x-show="callState === 'connected'" class="flex-1 bg-[#0B1220] text-white p-5 flex flex-col justify-between items-center text-center">
                                            <div class="pt-6 space-y-2">
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/50 px-3 py-1 text-[11px] font-bold text-emerald-200">
                                                    {{ $isEn ? 'Official Emergency Call Connected' : 'Tersambung ke Call Center Resmi' }}
                                                </span>
                                                <h2 class="font-heading font-extrabold text-xl text-white" x-text="activeHotlineName"></h2>
                                                <p class="text-sm font-bold text-[#C6F432] tabular-nums" x-text="dialNumber"></p>
                                            </div>

                                            <div class="w-full rounded-2xl bg-slate-900 border border-emerald-500/40 p-4 text-left space-y-1.5">
                                                <p class="text-xs font-heading font-bold text-emerald-300">{{ $isEn ? 'Official Report & Freeze Confirmed' : 'Laporan & Pemblokiran Resmi Berhasil' }}</p>
                                                <p class="text-xs text-slate-200 leading-relaxed">
                                                    {{ $isEn
                                                        ? 'Your account freeze and fraud report have been logged by the official call center.'
                                                        : 'Pemblokiran sementara dan laporan pengaduan Anda resmi tercatat di sistem call center.' }}
                                                </p>
                                            </div>

                                            <button
                                                type="button"
                                                @click="endCall()"
                                                class="panic-tap w-full min-h-[46px] rounded-2xl bg-[#DC2626] hover:bg-red-700 text-white text-xs font-heading font-extrabold"
                                            >
                                                {{ $isEn ? 'End Call & Return Home' : 'Tutup Panggilan & Kembali ke Beranda' }}
                                            </button>
                                        </div>

                                        <!-- Dialer Keypad State -->
                                        <div x-show="callState !== 'connected'" class="flex-1 p-3.5 flex flex-col justify-between overflow-y-auto">
                                            <div class="space-y-2.5">
                                                <div class="rounded-xl bg-blue-50 border border-blue-200 px-3 py-2 text-[11px] text-blue-950 leading-snug">
                                                    {{ $isEn
                                                        ? 'Emergency Hotline Dialer (via backup phone / landline). Tap an official hotline below or dial manually:'
                                                        : 'Dialer Darurat (lewat telepon rumah/keluarga). Pilih nomor resmi di bawah lalu tekan Panggil:' }}
                                                </div>

                                                <!-- Official Bank/OJK Quick-Fill Chips -->
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <button type="button" @click="dialNumber = '1500017'; dialError = ''" :class="isGuideTarget('btn_hotline') && !dialNumber ? 'panic-guide-ring-light' : ''" class="panic-tap panic-os-light-card min-h-[38px] rounded-xl bg-white border border-slate-300 hover:border-[#1E63E9] px-2.5 py-1.5 text-[11px] font-bold text-[#0F172A] text-left">
                                                        Call BRI · <span class="text-[#1E63E9] tabular-nums">1500017</span>
                                                    </button>
                                                    <button type="button" @click="dialNumber = '14000'; dialError = ''" class="panic-tap panic-os-light-card min-h-[38px] rounded-xl bg-white border border-slate-300 hover:border-[#1E63E9] px-2.5 py-1.5 text-[11px] font-bold text-[#0F172A] text-left">
                                                        Mandiri · <span class="text-[#1E63E9] tabular-nums">14000</span>
                                                    </button>
                                                    <button type="button" @click="dialNumber = '1500888'; dialError = ''" class="panic-tap panic-os-light-card min-h-[38px] rounded-xl bg-white border border-slate-300 hover:border-[#1E63E9] px-2.5 py-1.5 text-[11px] font-bold text-[#0F172A] text-left">
                                                        Halo BCA · <span class="text-[#1E63E9] tabular-nums">1500888</span>
                                                    </button>
                                                    <button type="button" @click="dialNumber = '157'; dialError = ''" class="panic-tap panic-os-light-card min-h-[38px] rounded-xl bg-white border border-slate-300 hover:border-[#1E63E9] px-2.5 py-1.5 text-[11px] font-bold text-[#0F172A] text-left">
                                                        OJK / IASC · <span class="text-[#1E63E9] tabular-nums">157</span>
                                                    </button>
                                                </div>

                                                <!-- Dial Display -->
                                                <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 px-3.5 py-2.5 flex items-center justify-between gap-2 min-h-[48px]">
                                                    <span class="font-heading font-extrabold text-xl tracking-wider tabular-nums text-[#0F172A] truncate" x-text="dialNumber || (isEn ? 'Enter hotline...' : 'Ketik nomor resmi...')"></span>
                                                    <button
                                                        type="button"
                                                        @click="dialNumber = dialNumber.slice(0, -1); dialError = ''"
                                                        aria-label="{{ $isEn ? 'Delete digit' : 'Hapus angka' }}"
                                                        class="panic-tap w-9 h-9 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-600"
                                                    >
                                                        &larr;
                                                    </button>
                                                </div>

                                                <p x-show="dialError" class="text-[11px] font-semibold text-red-700 px-1" x-text="dialError"></p>
                                            </div>

                                            <!-- 12-Key Tactile Keypad -->
                                            <div class="space-y-2 pt-2">
                                                <div class="grid grid-cols-3 gap-2">
                                                    <template x-for="key in ['1','2','3','4','5','6','7','8','9','*','0','#']" :key="key">
                                                        <button
                                                            type="button"
                                                            @click="appendDigit(key)"
                                                            class="panic-tap panic-os-light-card min-h-[44px] rounded-xl bg-white border border-slate-200 hover:bg-slate-100 font-heading font-extrabold text-base text-[#0F172A]"
                                                            x-text="key"
                                                        ></button>
                                                    </template>
                                                </div>

                                                <button
                                                    type="button"
                                                    @click="placeEmergencyCall()"
                                                    :class="isGuideTarget('btn_call') && dialNumber ? 'panic-guide-ring-light' : ''"
                                                    class="panic-tap w-full min-h-[46px] rounded-2xl bg-[#15803D] hover:bg-emerald-800 text-white font-heading font-extrabold text-xs flex items-center justify-center gap-2"
                                                >
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/>
                                                    </svg>
                                                    <span>{{ $isEn ? 'Call & Report / Freeze' : 'Panggil & Bekukan Rekening' }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SCREEN 5: PESAN SMS (INTERCEPTED OTP & PHISHING SENDER BLOCKER) -->
                                    <div
                                        x-show="screen === 'sms'"
                                        class="panic-screen-animate panic-os-light-surface flex-1 bg-[#F8FAFC] text-[#0F172A] flex flex-col overflow-hidden"
                                    >
                                        <div class="shrink-0 bg-white border-b border-slate-200 px-3.5 py-3 flex items-center gap-2.5 panic-os-light-card">
                                            <button type="button" @click="screen = 'home'" aria-label="{{ $isEn ? 'Back' : 'Kembali' }}" class="panic-tap w-9 h-9 rounded-lg hover:bg-slate-100 flex items-center justify-center text-[#0F172A]">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                                            </button>
                                            <h2 class="font-heading font-extrabold text-sm text-[#0F172A]">{{ $isEn ? 'SMS Messages' : 'Pesan SMS' }}</h2>
                                        </div>
                                        <div class="flex-1 p-3.5 space-y-2.5 overflow-y-auto">
                                            <!-- Case 2 Step 4 Action: Block Phishing SMS Sender -->
                                            <div x-show="scenarioId === 'wa_hijack'" class="rounded-2xl border p-3.5 space-y-2.5" :class="smsSenderBlocked ? 'bg-emerald-50 border-emerald-300' : 'bg-red-50 border-red-300'">
                                                <div class="flex items-center justify-between text-[11px]">
                                                    <span class="font-extrabold text-red-800">+62 877-0019-4421 (WA-RESET-PALSU)</span>
                                                    <span class="rounded px-1.5 py-0.5 text-[9px] font-extrabold" :class="smsSenderBlocked ? 'bg-emerald-200 text-emerald-900' : 'bg-red-200 text-red-900'" x-text="smsSenderBlocked ? (isEn ? 'BLOCKED' : 'DIBLOKIR') : 'PHISHING'"></span>
                                                </div>
                                                <p class="text-xs text-slate-900 leading-relaxed">
                                                    {{ $isEn
                                                        ? 'Your WhatsApp recovery link: http://verifikasi-wa-akun.xyz/otp, enter your 6-digit code now!'
                                                        : 'Tautan pemulihan WhatsApp Anda: http://verifikasi-wa-akun.xyz/otp, masukkan kode 6 digit Anda sekarang!' }}
                                                </p>
                                                <button
                                                    x-show="!smsSenderBlocked"
                                                    type="button"
                                                    @click="blockPhishingSmsSender()"
                                                    :class="isGuideTarget('btn_block_sms') ? 'panic-guide-ring-light' : ''"
                                                    class="panic-tap w-full min-h-[42px] rounded-xl bg-[#DC2626] hover:bg-red-700 text-white text-xs font-heading font-extrabold"
                                                >
                                                    {{ $isEn ? 'Block & Report Phishing Sender' : 'Blokir & Laporkan Pengirim Phishing' }}
                                                </button>
                                            </div>

                                            <div x-show="scenarioId !== 'wa_hijack'" class="rounded-xl bg-red-50 border border-red-200 p-3 text-[11px] text-red-900 leading-relaxed">
                                                {{ $isEn
                                                    ? 'Malware or attackers exploit these OTP codes while your network or account remains exposed.'
                                                    : 'Peretas memanfaatkan kode OTP di bawah ini selama jaringan atau akses m-Banking belum diputus.' }}
                                            </div>

                                            <template x-for="(msg, idx) in smsList" :key="idx">
                                                <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-3 space-y-1">
                                                    <div class="flex items-center justify-between text-[10px]">
                                                        <span class="font-extrabold text-[#1E63E9]" x-text="msg.sender"></span>
                                                        <span class="text-slate-500 tabular-nums" x-text="msg.time"></span>
                                                    </div>
                                                    <p class="text-xs text-[#0F172A] leading-relaxed" x-text="msg.text"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- SCREEN 6: m-BANKING (BRImo LIVE DRAIN + EMERGENCY CARD FREEZE FOR CASE 3!) -->
                                    <div
                                        x-show="screen === 'mbanking'"
                                        class="panic-screen-animate panic-os-light-surface flex-1 bg-[#F8FAFC] text-[#0F172A] flex flex-col overflow-hidden"
                                    >
                                        <div class="shrink-0 bg-[#00529C] text-white px-3.5 py-3 flex items-center gap-2.5">
                                            <button type="button" @click="screen = 'home'" aria-label="{{ $isEn ? 'Back' : 'Kembali' }}" class="panic-tap w-9 h-9 rounded-lg hover:bg-white/10 flex items-center justify-center text-white">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                                            </button>
                                            <h2 class="font-heading font-extrabold text-sm text-white">m-Banking (BRImo)</h2>
                                        </div>
                                        <div class="flex-1 p-3.5 space-y-3 overflow-y-auto">
                                            <div class="rounded-2xl bg-[#00529C] text-white p-4 space-y-1.5">
                                                <p class="text-[11px] text-sky-200">{{ $isEn ? 'Remaining Balance' : 'Sisa Saldo Rekening' }}</p>
                                                <p class="font-heading font-extrabold text-2xl tabular-nums" x-text="formatRupiah(balance)"></p>
                                                <p class="text-[11px]" :class="mbankingLocked || steps.s5 ? 'text-[#C6F432]' : 'text-red-200'" x-text="mbankingLocked || steps.s5
                                                    ? (isEn ? 'Status: Temporarily Frozen Safe' : 'Status: Rekening Dibekukan Aman')
                                                    : (isEn ? 'Status: Exposed to Unauthorized Transfers' : 'Status: Rentan terhadap Transaksi Keluar')"></p>
                                            </div>

                                            <!-- Emergency In-App Card/Account Freeze Button (Key Step 2 for Case 3, helpful in all!) -->
                                            <div class="panic-os-light-card rounded-2xl bg-white border border-slate-200 p-3.5 space-y-2">
                                                <p class="text-xs font-heading font-bold text-[#0F172A]">{{ $isEn ? 'Emergency Card & Access Freeze' : 'Keamanan Darurat Rekening' }}</p>
                                                <button
                                                    x-show="!mbankingLocked"
                                                    type="button"
                                                    @click="lockMbankingApp()"
                                                    :class="isGuideTarget('btn_mbanking_lock') ? 'panic-guide-ring-light' : ''"
                                                    class="panic-tap w-full min-h-[44px] rounded-xl bg-[#DC2626] hover:bg-red-700 text-white text-xs font-heading font-extrabold"
                                                >
                                                    {{ $isEn ? 'Freeze Card & Block m-Banking Transfers' : 'Blokir Sementara Kartu & Transaksi Keluar' }}
                                                </button>
                                                <p x-show="mbankingLocked" class="rounded-xl bg-emerald-50 border border-emerald-300 p-2.5 text-xs font-semibold text-emerald-900">
                                                    {{ $isEn ? 'Outgoing transfers locked! No more money can be drained.' : 'Transaksi keluar berhasil dikunci! Saldo tidak bisa disedot lagi.' }}
                                                </p>
                                            </div>

                                            <div class="space-y-2">
                                                <p class="text-[11px] font-heading font-bold text-slate-700">{{ $isEn ? 'Unauthorized Transfers' : 'Mutasi Keluar Tak Dikenal' }}</p>
                                                <template x-if="transfers.length === 0">
                                                    <p class="panic-os-light-card rounded-xl bg-white border border-slate-200 p-3 text-xs text-slate-600">
                                                        {{ $isEn ? 'No balance lost yet.' : 'Belum ada saldo yang terpotong.' }}
                                                    </p>
                                                </template>
                                                <template x-for="(tx, idx) in transfers" :key="idx">
                                                    <div class="panic-os-light-card rounded-xl bg-white border border-red-200 p-3 flex items-center justify-between gap-2">
                                                        <div class="min-w-0">
                                                            <p class="text-xs font-bold text-[#0F172A] truncate" x-text="tx.label"></p>
                                                            <p class="text-[10px] text-slate-500 tabular-nums" x-text="tx.time"></p>
                                                        </div>
                                                        <span class="text-xs font-extrabold text-red-700 tabular-nums shrink-0" x-text="'-' + formatRupiah(tx.amount)"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SCREEN 7: RECENTS / APP SWITCHER -->
                                    <div
                                        x-show="screen === 'recents'"
                                        class="panic-screen-animate flex-1 bg-[#0B1220] text-white p-4 space-y-2.5 overflow-y-auto"
                                    >
                                        <p class="text-xs font-heading font-bold text-slate-300 mb-2">{{ $isEn ? 'Open Apps' : 'Aplikasi Terbuka' }}</p>
                                        <button type="button" @click="openApp('settings')" class="panic-tap w-full min-h-[48px] rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 px-3.5 py-2.5 text-left text-xs font-bold text-white flex items-center justify-between">
                                            <span>{{ $isEn ? 'Settings (Connections, Accessibility, Apps)' : 'Pengaturan (Koneksi, Aksesibilitas, Aplikasi)' }}</span>
                                            <span class="text-sky-400">&rarr;</span>
                                        </button>
                                        <button type="button" @click="openApp('whatsapp')" class="panic-tap w-full min-h-[48px] rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 px-3.5 py-2.5 text-left text-xs font-bold text-white flex items-center justify-between">
                                            <span>WhatsApp</span>
                                            <span class="text-emerald-400">&rarr;</span>
                                        </button>
                                        <button type="button" @click="openApp('phone')" class="panic-tap w-full min-h-[48px] rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 px-3.5 py-2.5 text-left text-xs font-bold text-white flex items-center justify-between">
                                            <span>{{ $isEn ? 'Phone (Emergency Bank Hotlines)' : 'Telepon (Call Center Resmi Bank/OJK)' }}</span>
                                            <span class="text-sky-400">&rarr;</span>
                                        </button>
                                        <button type="button" @click="openApp('mbanking')" class="panic-tap w-full min-h-[48px] rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 px-3.5 py-2.5 text-left text-xs font-bold text-white flex items-center justify-between">
                                            <span>m-Banking (BRImo)</span>
                                            <span class="text-sky-400">&rarr;</span>
                                        </button>
                                        <button type="button" @click="openApp('sms')" class="panic-tap w-full min-h-[48px] rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 px-3.5 py-2.5 text-left text-xs font-bold text-white flex items-center justify-between">
                                            <span>{{ $isEn ? 'SMS Messages' : 'Pesan SMS' }}</span>
                                            <span class="text-sky-400">&rarr;</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- ANDROID BOTTOM NAVIGATION BAR (Back, Home, Recents) -->
                                <nav
                                    aria-label="{{ $isEn ? 'Virtual Android navigation' : 'Navigasi Android virtual' }}"
                                    class="shrink-0 bg-[#090D16] border-t border-slate-800 grid grid-cols-3 h-12"
                                >
                                    <button
                                        type="button"
                                        @click="androidBack()"
                                        aria-label="{{ $isEn ? 'Android Back' : 'Tombol Kembali Android' }}"
                                        class="panic-tap flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/5"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="17 4 6 12 17 20 17 4"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="androidHome()"
                                        aria-label="{{ $isEn ? 'Android Home' : 'Tombol Beranda Android' }}"
                                        :class="isGuideTarget('nav_home') ? 'text-[#C6F432]' : 'text-slate-300 hover:text-white'"
                                        class="panic-tap flex items-center justify-center hover:bg-white/5"
                                    >
                                        <span class="w-3.5 h-3.5 rounded-full border-2 border-current"></span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="androidRecents()"
                                        aria-label="{{ $isEn ? 'Android Recent Apps' : 'Daftar Aplikasi Terbuka' }}"
                                        class="panic-tap flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/5"
                                    >
                                        <span class="w-3.5 h-3.5 rounded-[3px] border-2 border-current"></span>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Live Forensic Telemetry, 5-Step Tracker, Log & Final Report -->
                <div class="lg:col-span-7 space-y-5">

                    <!-- FINAL FORENSIC REPORT CARD (Shown when Won or Lost) -->
                    <div
                        x-show="phase === 'won' || phase === 'lost'"
                        x-cloak
                        class="panic-screen-animate rounded-2xl border p-5 sm:p-6 space-y-5"
                        :class="phase === 'won'
                            ? '{{ $isDark ? 'bg-emerald-950/50 border-emerald-500/60' : 'bg-white border-emerald-400 shadow-sm' }}'
                            : '{{ $isDark ? 'bg-red-950/40 border-red-500/60' : 'bg-white border-red-300 shadow-sm' }}'"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <span
                                    :class="phase === 'won' ? 'bg-[#C6F432] text-[#0A0A0A]' : 'bg-[#DC2626] text-white'"
                                    class="inline-flex items-center rounded-lg px-2.5 py-1 text-[11px] font-heading font-extrabold uppercase tracking-wide"
                                    x-text="phase === 'won'
                                        ? (isEn ? 'DEVICE SAVED' : 'PERANGKAT BERHASIL DISELAMATKAN')
                                        : (isEn ? 'CONTAINMENT FAILED' : 'PENYELAMATAN TERLAMBAT')"
                                ></span>
                                <h2 class="mt-2 font-heading font-extrabold text-xl sm:text-2xl {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="phase === 'won'
                                    ? (isEn ? 'All 5 emergency containment steps completed!' : 'Seluruh 5 langkah darurat berhasil dijalankan!')
                                    : (balance <= 0
                                        ? (isEn ? 'Funds reached zero before containment.' : 'Dana habis terkuras sebelum ancaman dihentikan.')
                                        : (isEn ? 'Time expired before all steps were secured.' : 'Waktu habis sebelum seluruh ancaman dibersihkan.'))"></h2>
                            </div>
                            <div class="rounded-2xl px-4 py-2.5 text-center border {{ $isDark ? 'bg-slate-900 border-slate-700' : 'bg-slate-50 border-slate-200' }}">
                                <p class="text-[10px] font-bold uppercase tracking-wider {{ $isDark ? 'text-slate-400' : 'text-slate-500' }}">
                                    {{ $isEn ? 'Forensic Score' : 'Skor Forensik' }}
                                </p>
                                <p class="font-heading font-extrabold text-2xl tabular-nums {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="finalScore + ' / 100'"></p>
                            </div>
                        </div>

                        <!-- Result Metrics Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="rounded-xl border p-3.5 {{ $isDark ? 'bg-slate-900/90 border-slate-700' : 'bg-slate-50 border-slate-200' }}">
                                <p class="text-[11px] font-semibold {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}">{{ $isEn ? 'Saved Funds' : 'Dana Terselamatkan' }}</p>
                                <p class="mt-1 font-heading font-extrabold text-base tabular-nums {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="formatRupiah(balance)"></p>
                            </div>
                            <div class="rounded-xl border p-3.5 {{ $isDark ? 'bg-slate-900/90 border-slate-700' : 'bg-slate-50 border-slate-200' }}">
                                <p class="text-[11px] font-semibold {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}">{{ $isEn ? 'Response Time' : 'Waktu Respons' }}</p>
                                <p class="mt-1 font-heading font-extrabold text-base tabular-nums {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="elapsedSeconds + (isEn ? ' sec' : ' detik')"></p>
                            </div>
                            <div class="rounded-xl border p-3.5 {{ $isDark ? 'bg-slate-900/90 border-slate-700' : 'bg-slate-50 border-slate-200' }}">
                                <p class="text-[11px] font-semibold {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}">{{ $isEn ? 'Order / Trap Mistakes' : 'Kesalahan Urutan / Jebakan' }}</p>
                                <p class="mt-1 font-heading font-extrabold text-base tabular-nums {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="mistakes"></p>
                            </div>
                        </div>

                        <!-- Post-Incident Real-World Checklist -->
                        <div class="rounded-xl border p-4 space-y-2 {{ $isDark ? 'bg-slate-900/80 border-slate-700' : 'bg-slate-50 border-slate-200' }}">
                            <p class="text-xs font-heading font-extrabold {{ $isDark ? 'text-white' : 'text-ink' }}">
                                {{ $isEn ? 'Real-World Next Actions After an Incident:' : 'Langkah Lanjutan di Dunia Nyata Setelah Insiden:' }}
                            </p>
                            <ul class="space-y-1.5 text-xs {{ $isDark ? 'text-slate-300' : 'text-slate-700' }}">
                                <li>1. {{ $isEn ? 'Change your Gmail, m-Banking PIN, and e-wallet passwords from a separate clean device.' : 'Ganti kata sandi Gmail, PIN m-Banking, dan dompet digital menggunakan perangkat lain yang bersih.' }}</li>
                                <li>2. {{ $isEn ? 'Enable 6-digit Two-Step Verification PIN inside WhatsApp Settings.' : 'Aktifkan PIN Verifikasi Dua Langkah (6 digit) di pengaturan WhatsApp.' }}</li>
                                <li>3. {{ $isEn ? 'If unknown apps reappear, back up photos and perform a full Factory Reset.' : 'Jika aplikasi asing muncul kembali, cadangkan foto lalu lakukan Factory Reset (Reset Pabrik).' }}</li>
                                <li>4. {{ $isEn ? 'Report financial fraud immediately to OJK 157 or Indonesia Anti-Scam Centre (iasc.ojk.go.id).' : 'Laporkan indikasi penipuan rekening ke OJK 157 atau Indonesia Anti-Scam Centre (iasc.ojk.go.id).' }}</li>
                            </ul>
                        </div>

                        <!-- Save Status + Replay Actions -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <p class="text-xs font-semibold {{ $isDark ? 'text-slate-300' : 'text-slate-600' }}" x-text="saveStatusText"></p>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <button
                                    type="button"
                                    @click="resetAndStart()"
                                    class="panic-tap min-h-[44px] px-5 py-2.5 rounded-xl bg-[#1E63E9] hover:bg-blue-700 text-white text-xs font-heading font-extrabold"
                                >
                                    {{ $isEn ? 'Replay Case' : 'Ulangi Kasus Ini' }}
                                </button>
                                <button
                                    type="button"
                                    @click="nextScenario()"
                                    class="panic-tap min-h-[44px] px-4 py-2.5 rounded-xl bg-[#C6F432] hover:bg-[#b5e025] text-[#0A0A0A] text-xs font-heading font-extrabold"
                                >
                                    {{ $isEn ? 'Try Next Case' : 'Coba Kasus Lainnya' }}
                                </button>
                                <a
                                    href="{{ route('leaderboard.index') }}"
                                    class="panic-tap inline-flex items-center min-h-[44px] px-4 py-2.5 rounded-xl border text-xs font-heading font-bold {{ $isDark ? 'border-slate-700 bg-slate-900 text-slate-200 hover:border-sky' : 'border-slate-300 bg-white text-ink hover:border-sky' }}"
                                >
                                    {{ $isEn ? 'Leaderboard' : 'Papan Peringkat' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- LIVE TELEMETRY STRIP (Countdown, Balance, Threat State) -->
                    <div class="rounded-2xl border p-4 sm:p-5 space-y-4 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }}">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <!-- Timer -->
                            <div class="rounded-xl border p-3.5 {{ $isDark ? 'bg-slate-950/70 border-slate-800' : 'bg-slate-50 border-slate-200' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wide {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}">{{ $isEn ? 'Time Left' : 'Sisa Waktu' }}</span>
                                    <span x-show="phase === 'playing' && timeLeft <= 15" class="text-[10px] font-extrabold text-[#DC2626]">{{ $isEn ? 'URGENT' : 'KRITIS' }}</span>
                                </div>
                                <p
                                    class="mt-1 font-heading font-extrabold text-2xl tabular-nums"
                                    :class="phase === 'playing' && timeLeft <= 15 ? 'text-[#DC2626]' : '{{ $isDark ? 'text-white' : 'text-ink' }}'"
                                    x-text="timeLeft + 's'"
                                ></p>
                                <div class="mt-2 h-1.5 w-full rounded-full overflow-hidden {{ $isDark ? 'bg-slate-800' : 'bg-slate-200' }}">
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="timeLeft <= 15 ? 'bg-[#DC2626]' : 'bg-[#1E63E9]'"
                                        :style="'width: ' + Math.max(0, Math.round((timeLeft / maxDuration) * 100)) + '%'"
                                    ></div>
                                </div>
                            </div>

                            <!-- Live Saved Balance -->
                            <div class="rounded-xl border p-3.5 {{ $isDark ? 'bg-slate-950/70 border-slate-800' : 'bg-slate-50 border-slate-200' }}">
                                <span class="text-[11px] font-bold uppercase tracking-wide {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}">{{ $isEn ? 'Protected Funds' : 'Dana Aman' }}</span>
                                <p class="mt-1 font-heading font-extrabold text-xl sm:text-2xl tabular-nums {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="formatRupiah(balance)"></p>
                                <p class="mt-1 text-[11px]" :class="stolenTotal > 0 ? 'text-[#DC2626] font-semibold' : '{{ $isDark ? 'text-slate-400' : 'text-slate-600' }}'" x-text="stolenTotal > 0
                                    ? ((isEn ? 'Lost: -' : 'Terkuras: -') + formatRupiah(stolenTotal))
                                    : (isEn ? 'Zero loss so far' : 'Belum ada kerugian')"></p>
                            </div>

                            <!-- Threat Status -->
                            <div class="rounded-xl border p-3.5 {{ $isDark ? 'bg-slate-950/70 border-slate-800' : 'bg-slate-50 border-slate-200' }}">
                                <span class="text-[11px] font-bold uppercase tracking-wide {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}">{{ $isEn ? 'Device Status' : 'Status Perangkat' }}</span>
                                <p
                                    class="mt-1.5 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-heading font-extrabold"
                                    :class="phase === 'won'
                                        ? 'bg-[#C6F432] text-[#0A0A0A]'
                                        : (isDrainStopped
                                            ? 'bg-[#1E63E9] text-white'
                                            : 'bg-[#DC2626] text-white')"
                                    x-text="threatStatusLabel"
                                ></p>
                                <p class="mt-1.5 text-[11px] {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}" x-text="(isEn ? 'Mistakes: ' : 'Kesalahan: ') + mistakes"></p>
                            </div>
                        </div>
                    </div>

                    <!-- 5-STEP CONTAINMENT TRACKER + INTERACTIVE STEP GUIDANCE -->
                    <div class="rounded-2xl border p-4 sm:p-5 space-y-4 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="font-heading font-extrabold text-base {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="activeScenario.protocolTitle"></h2>
                                <p class="text-xs {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}">
                                    {{ $isEn ? 'Follow the active step inside the phone screen on the left.' : 'Jalankan langkah aktif langsung di layar HP sebelah kiri.' }}
                                </p>
                            </div>

                            <span class="rounded-lg px-2.5 py-1 text-xs font-heading font-extrabold tabular-nums {{ $isDark ? 'bg-slate-800 text-slate-100' : 'bg-slate-100 text-ink' }}" x-text="completedCount + ' / 5'"></span>
                        </div>

                        <ol class="space-y-2">
                            <template x-for="item in checklistItems" :key="item.key">
                                <li
                                    class="rounded-xl border px-3.5 py-2.5 flex items-start gap-3"
                                    :class="steps[item.key]
                                        ? '{{ $isDark ? 'bg-emerald-950/35 border-emerald-700/60' : 'bg-emerald-50/70 border-emerald-300' }}'
                                        : (currentGuideStep && currentGuideStep.key === item.key && guidedMode
                                            ? '{{ $isDark ? 'bg-blue-950/40 border-[#1E63E9]' : 'bg-blue-50/70 border-[#1E63E9]' }}'
                                            : '{{ $isDark ? 'bg-slate-950/60 border-slate-800' : 'bg-slate-50 border-slate-200' }}')"
                                >
                                    <span
                                        class="mt-0.5 w-5 h-5 rounded-md flex items-center justify-center text-[11px] font-extrabold shrink-0"
                                        :class="steps[item.key]
                                            ? 'bg-[#047857] text-white'
                                            : (currentGuideStep && currentGuideStep.key === item.key && guidedMode
                                                ? 'bg-[#1E63E9] text-white'
                                                : '{{ $isDark ? 'bg-slate-800 text-slate-300' : 'bg-slate-200 text-slate-700' }}')"
                                    >
                                        <template x-if="steps[item.key]">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                        </template>
                                        <template x-if="!steps[item.key]">
                                            <span x-text="item.num"></span>
                                        </template>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <p
                                                class="text-xs font-heading font-bold"
                                                :class="steps[item.key]
                                                    ? '{{ $isDark ? 'text-emerald-200 line-through' : 'text-emerald-900 line-through' }}'
                                                    : '{{ $isDark ? 'text-slate-100' : 'text-ink' }}'"
                                                x-text="item.title"
                                            ></p>
                                            <span
                                                class="text-[10px] font-bold shrink-0"
                                                :class="steps[item.key] ? '{{ $isDark ? 'text-emerald-300' : 'text-[#047857]' }}' : '{{ $isDark ? 'text-slate-400' : 'text-slate-600' }}'"
                                                x-text="steps[item.key] ? (isEn ? 'Done' : 'Selesai') : (currentGuideStep && currentGuideStep.key === item.key && guidedMode ? (isEn ? 'Now' : 'Sekarang') : (isEn ? 'Pending' : 'Belum'))"
                                            ></span>
                                        </div>
                                        <p
                                            x-show="guidedMode && !steps[item.key]"
                                            class="mt-1 text-[11px] leading-relaxed {{ $isDark ? 'text-sky-300' : 'text-[#1E63E9]' }}"
                                            x-text="item.where"
                                        ></p>
                                    </div>
                                </li>
                            </template>
                        </ol>
                    </div>

                    <!-- LIVE FORENSIC ACTIVITY LOG -->
                    <div class="rounded-2xl border p-4 sm:p-5 space-y-3 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }}">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="font-heading font-extrabold text-sm {{ $isDark ? 'text-white' : 'text-ink' }}">
                                {{ $isEn ? 'Live Forensic Action Log' : 'Catatan Forensik Langsung' }}
                            </h2>
                            <button
                                x-show="phase === 'playing'"
                                type="button"
                                @click="resetToIntro()"
                                class="panic-tap text-xs font-bold text-[#DC2626] hover:underline"
                            >
                                {{ $isEn ? 'Restart Drill' : 'Ulangi dari Awal' }}
                            </button>
                        </div>

                        <div
                            aria-live="polite"
                            class="max-h-[210px] overflow-y-auto space-y-2 pr-1"
                        >
                            <template x-if="logs.length === 0">
                                <p class="text-xs py-4 text-center {{ $isDark ? 'text-slate-400' : 'text-slate-600' }}">
                                    {{ $isEn ? 'Pick a case above and press Start on the phone screen to begin.' : 'Pilih salah satu dari 3 kasus di atas, lalu tekan Mulai di layar HP.' }}
                                </p>
                            </template>

                            <template x-for="(entry, idx) in logs" :key="idx">
                                <div class="rounded-xl border px-3 py-2 flex items-start gap-2.5 text-xs {{ $isDark ? 'bg-slate-950/70 border-slate-800' : 'bg-slate-50 border-slate-200' }}">
                                    <span class="font-bold tabular-nums text-[11px] shrink-0 {{ $isDark ? 'text-slate-400' : 'text-slate-500' }}" x-text="entry.time"></span>
                                    <span
                                        class="rounded px-1.5 py-0.5 text-[10px] font-extrabold uppercase shrink-0"
                                        :class="entry.level === 'ok'
                                            ? 'bg-[#047857] text-white'
                                            : (entry.level === 'danger'
                                                ? 'bg-[#DC2626] text-white'
                                                : (entry.level === 'warn'
                                                    ? 'bg-amber-400 text-[#0A0A0A]'
                                                    : 'bg-slate-700 text-white'))"
                                        x-text="entry.tag"
                                    ></span>
                                    <p class="leading-snug {{ $isDark ? 'text-slate-200' : 'text-slate-800' }}" x-text="entry.message"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <script>
        function panicSimulator(config) {
            return {
                isEn: Boolean(config.isEn),
                isAuth: Boolean(config.isAuth),
                recordUrl: config.recordUrl,
                csrfToken: config.csrfToken,

                // User Settings
                reduceMotion: false,
                guidedMode: true, // Default ON for beginner-friendly guidance!

                // Scenario Selection (3 Real Emergency Cases)
                scenarioId: 'apk_breach', // 'apk_breach' | 'wa_hijack' | 'phishing_bank'

                // Core Game State
                mode: 'standard', // 'standard' (60s) | 'extreme' (45s)
                phase: 'intro',   // 'intro' | 'playing' | 'won' | 'lost'
                maxDuration: 60,
                timeLeft: 60,
                elapsedSeconds: 0,
                initialBalance: 4850000,
                balance: 4850000,
                stolenTotal: 0,
                mistakes: 0,
                finalScore: 0,
                saveStatusText: '',

                // Hardware & Android OS State
                shadeOpen: false,
                isDraggingShade: false,
                dragMode: null, // 'open' | 'close'
                dragStartY: 0,
                dragDeltaY: 0,
                justDragged: false,
                airplaneMode: false,
                wifi: true,
                mobileData: true,
                flashlight: false,
                brightness: 100,
                battery: 74,
                clockLabel: '09:41',
                shaking: false,
                drainTick: false,

                // Specific Case Sub-States
                accessibilityRevoked: false,
                malwareUninstalled: false,
                waLoggedOut: false,
                waPinEnabled: false,
                waWarningSent: false,
                smsSenderBlocked: false,
                mbankingLocked: false,

                // Screen Routing
                screen: 'home', // home | settings | whatsapp | phone | sms | mbanking | recents
                settingsView: 'root', // root | network | accessibility | apps | app_detail
                selectedAppId: null,
                waView: 'chats', // chats | chat_detail | linked | settings
                waMenuOpen: false,
                activeWaChat: null,
                callState: 'dialer', // dialer | connected
                dialNumber: '',
                dialError: '',
                activeHotlineName: '',

                // Toast & Alert Modal
                activeToast: null,
                toastTimer: null,
                alertModal: null,
                trapTriggered: false,

                // 5 Generic Step Slots (Mapped per scenario)
                steps: {
                    s1: false,
                    s2: false,
                    s3: false,
                    s4: false,
                    s5: false,
                },

                // Live Data Lists
                smsList: [],
                transfers: [],
                logs: [],

                // Timers
                tickInterval: null,
                drainInterval: null,

                init() {
                    const savedMotion = window.localStorage.getItem('digisafe_panic_reduced_motion');
                    if (savedMotion === '1' || savedMotion === 'true') {
                        this.reduceMotion = true;
                    } else if (savedMotion === '0' || savedMotion === 'false') {
                        this.reduceMotion = false;
                    } else if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        this.reduceMotion = true;
                    }
                    this.resetToIntro();
                },

                get scenarios() {
                    return [
                        {
                            id: 'apk_breach',
                            badge: this.isEn ? 'Case 1 · Malware .APK' : 'Kasus 1 · File .APK',
                            title: this.isEn ? 'Infected Courier .APK Installed' : 'Terlanjur Pasang .APK Kurir',
                            shortDesc: this.isEn
                                ? 'Resi_JNE_Viewer.apk hijacks Accessibility & steals SMS OTP codes.'
                                : 'Resi_JNE_Viewer.apk membajak Aksesibilitas & mencuri SMS OTP m-Banking.',
                            headline: this.isEn ? 'You just installed Resi_JNE_Viewer.apk' : 'Anda baru saja memasang Resi_JNE_Viewer.apk',
                            story: this.isEn
                                ? 'Seconds after installation, bank OTP messages pour in, Rp 4,850,000 begins draining automatically, and your WhatsApp spams family groups.'
                                : 'Beberapa detik setelah terpasang, SMS OTP bank masuk bertubi-tubi, saldo Rp 4.850.000 mulai terpotong otomatis, dan WhatsApp menyebar file .APK ke grup keluarga.',
                            protocolTitle: this.isEn ? 'Case 1 Rescue Protocol (5 Steps)' : 'Protokol Penyelamatan Kasus 1 (5 Langkah)',
                            initialBalance: 4850000,
                            widgetLabel: this.isEn ? 'BRImo · Main Account' : 'BRImo · Tabungan Utama',
                            widgetSafeText: this.isEn ? 'Network cut or account frozen. Finish cleaning device.' : 'Penyedotan saldo berhenti. Lanjutkan pembersihan perangkat.',
                            widgetDangerText: this.isEn ? 'Unauthorized transfers active via background OTP.' : 'Transfer tak dikenal aktif melalui pencurian SMS OTP.',
                            malwareName: 'Resi_JNE_Viewer',
                            malwareShort: 'Resi_JNE',
                            malwarePkg: 'com.kurir.resi.viewer.apk',
                            malwareSize: '4.6 MB',
                            accessibilityService: 'Update Layanan Sistem',
                        },
                        {
                            id: 'wa_hijack',
                            badge: this.isEn ? 'Case 2 · Account Takeover' : 'Kasus 2 · Bajak WhatsApp',
                            title: this.isEn ? 'WhatsApp Hijacked & Scamming Family' : 'WhatsApp Dibajak Minta Transfer',
                            shortDesc: this.isEn
                                ? 'Attacker linked WhatsApp Web and is urgently asking relatives for money.'
                                : 'Penipu menautkan WhatsApp Web lalu mengirim pesan pinjam uang ke grup keluarga.',
                            headline: this.isEn ? 'Your WhatsApp is being controlled remotely!' : 'Akun WhatsApp Anda sedang dikendalikan penipu!',
                            story: this.isEn
                                ? 'An unknown Edge (Windows 10) session is messaging your family group asking for urgent Rp 2.5M transfers while sending fake OTP reset SMS.'
                                : 'Sesi asing Edge (Windows 10) sedang mengirim pesan darurat pinjam uang ke grup keluarga Purwakarta dan mengirim SMS link OTP palsu.',
                            protocolTitle: this.isEn ? 'Case 2 Rescue Protocol (5 Steps)' : 'Protokol Penyelamatan Kasus 2 (5 Langkah)',
                            initialBalance: 6000000,
                            widgetLabel: this.isEn ? 'Family Funds at Risk' : 'Dana Kerabat Terancam',
                            widgetSafeText: this.isEn ? 'Hijacked WA session terminated! Finish securing account.' : 'Sesi pembajak diputus! Lanjutkan pengamanan akun WA.',
                            widgetDangerText: this.isEn ? 'Relatives are about to transfer money to the scammer!' : 'Kerabat hampir tertipu dan mentransfer uang ke penipu!',
                            malwareName: '',
                            malwareShort: '',
                            malwarePkg: '',
                            malwareSize: '',
                            accessibilityService: '',
                        },
                        {
                            id: 'phishing_bank',
                            badge: this.isEn ? 'Case 3 · Bank Phishing' : 'Kasus 3 · Phishing Bank',
                            title: this.isEn ? 'Typed PIN on Fake Bank Link + Public Wi-Fi' : 'Isi Link Tarif Bank di Wi-Fi Publik',
                            shortDesc: this.isEn
                                ? 'Connected to Free_WiFi_Cafe_Gratis, entered PIN on phishing page & remote app installed.'
                                : 'Terhubung Wi-Fi kafe gratis, salah isi PIN tarif bank palsu & terpasang aplikasi remote.',
                            headline: this.isEn ? 'm-Banking PIN leaked on Public Wi-Fi!' : 'PIN m-Banking bocor di Wi-Fi Publik!',
                            story: this.isEn
                                ? 'While on Free_WiFi_Cafe_Gratis, you tapped a fake Rp 150k tariff link and installed RemoteSupport_CS. Rp 7,500,000 is being drained fast!'
                                : 'Saat memakai Free_WiFi_Cafe_Gratis, Anda mengisi link tarif bank palsu dan memasang RemoteSupport_CS. Saldo Rp 7.500.000 mulai disedot!',
                            protocolTitle: this.isEn ? 'Case 3 Rescue Protocol (5 Steps)' : 'Protokol Penyelamatan Kasus 3 (5 Langkah)',
                            initialBalance: 7500000,
                            widgetLabel: this.isEn ? 'BRImo · Savings Exposed' : 'BRImo · Saldo Terancam',
                            widgetSafeText: this.isEn ? 'Account frozen & public Wi-Fi severed. Clean remote app.' : 'Rekening dibekukan & Wi-Fi diputus. Hapus aplikasi remote.',
                            widgetDangerText: this.isEn ? 'Attacker logged in via leaked PIN over public Wi-Fi.' : 'Peretas masuk lewat PIN bocor dan Wi-Fi publik.',
                            malwareName: 'RemoteSupport_CS',
                            malwareShort: 'RemoteCS',
                            malwarePkg: 'com.csbank.remote.helper.apk',
                            malwareSize: '6.2 MB',
                            accessibilityService: 'Layanan Bantu CS Palsu',
                        },
                    ];
                },

                get activeScenario() {
                    return this.scenarios.find((s) => s.id === this.scenarioId) || this.scenarios[0];
                },

                get hasMalwareApp() {
                    return this.scenarioId === 'apk_breach' || this.scenarioId === 'phishing_bank';
                },

                get wifiSsid() {
                    return this.scenarioId === 'phishing_bank' ? 'Free_WiFi_Cafe_Gratis' : 'Rumah_5G';
                },

                get isNetworkIsolated() {
                    if (this.scenarioId === 'phishing_bank') {
                        return this.airplaneMode || !this.wifi;
                    }
                    return this.airplaneMode;
                },

                get isDrainStopped() {
                    if (this.scenarioId === 'apk_breach') {
                        return this.steps.s1 || this.mbankingLocked || this.steps.s5;
                    }
                    if (this.scenarioId === 'wa_hijack') {
                        return this.waLoggedOut || this.waWarningSent;
                    }
                    return this.mbankingLocked || this.steps.s5 || this.airplaneMode;
                },

                get settingsBadgeCount() {
                    if (!this.hasMalwareApp) return 0;
                    let c = 0;
                    if (!this.accessibilityRevoked) c++;
                    if (!this.malwareUninstalled) c++;
                    return c;
                },

                get waBadgeCount() {
                    if (this.scenarioId === 'phishing_bank') return 0;
                    if (this.scenarioId === 'wa_hijack') {
                        let c = 0;
                        if (!this.waLoggedOut) c++;
                        if (!this.waPinEnabled) c++;
                        if (!this.waWarningSent) c++;
                        return c;
                    }
                    return this.waLoggedOut ? 0 : 2;
                },

                get mbankingBadgeCount() {
                    if (this.scenarioId === 'phishing_bank' && !this.mbankingLocked) {
                        return Math.max(1, this.transfers.length);
                    }
                    return this.transfers.length;
                },

                get shadeTransformStyle() {
                    if (this.isDraggingShade) {
                        if (this.dragMode === 'open') {
                            const pct = Math.min(100, Math.max(0, (this.dragDeltaY / 260) * 100));
                            return `transform: translateY(${pct - 100}%); opacity: ${Math.max(0.15, pct / 100)}; pointer-events: auto;`;
                        }
                        if (this.dragMode === 'close') {
                            const offset = Math.min(0, this.dragDeltaY);
                            return `transform: translateY(${offset}px); opacity: ${Math.max(0.2, 1 + offset / 280)}; pointer-events: auto;`;
                        }
                    }
                    if (this.shadeOpen) {
                        return 'transform: translateY(0%); opacity: 1; pointer-events: auto;';
                    }
                    return 'transform: translateY(-104%); opacity: 0; pointer-events: none;';
                },

                toggleMotion() {
                    this.reduceMotion = !this.reduceMotion;
                    try {
                        window.localStorage.setItem('digisafe_panic_reduced_motion', this.reduceMotion ? '1' : '0');
                    } catch (_) {}
                },

                selectMode(newMode) {
                    this.mode = newMode;
                    this.maxDuration = newMode === 'extreme' ? 45 : 60;
                    this.guidedMode = newMode === 'standard';
                    this.resetToIntro();
                },

                selectScenario(id) {
                    this.scenarioId = id;
                    this.resetToIntro();
                },

                nextScenario() {
                    const order = ['apk_breach', 'wa_hijack', 'phishing_bank'];
                    const idx = order.indexOf(this.scenarioId);
                    this.scenarioId = order[(idx + 1) % order.length];
                    this.resetToIntro();
                },

                get completedCount() {
                    return Object.values(this.steps).filter(Boolean).length;
                },

                get threatStatusLabel() {
                    if (this.phase === 'won') {
                        return this.isEn ? 'Device Secured' : 'Perangkat Bersih';
                    }
                    if (this.phase === 'lost') {
                        return this.isEn ? 'Compromised' : 'Terdampak Penuh';
                    }
                    if (this.isDrainStopped) {
                        return this.isEn ? 'Drain Halted' : 'Kerugian Terhenti';
                    }
                    return this.isEn ? 'Active Breach' : 'Ancaman Aktif';
                },

                get settingsTitle() {
                    const map = this.isEn
                        ? { root: 'Settings', network: 'Connections & Network', accessibility: 'Accessibility', apps: 'Installed Apps', app_detail: 'App Info' }
                        : { root: 'Pengaturan', network: 'Koneksi & Jaringan', accessibility: 'Aksesibilitas', apps: 'Aplikasi Terpasang', app_detail: 'Info Aplikasi' };
                    return map[this.settingsView] || map.root;
                },

                get installedApps() {
                    const apps = [];
                    if (this.hasMalwareApp && !this.malwareUninstalled) {
                        apps.push({
                            id: 'malware',
                            name: this.activeScenario.malwareName,
                            pkg: this.activeScenario.malwarePkg,
                            size: this.activeScenario.malwareSize,
                            source: this.isEn ? 'Unknown Source (.APK)' : 'Sumber Tak Dikenal (.APK)',
                            malicious: true,
                            permissions: this.isEn
                                ? ['Accessibility (Full Screen Control)', 'Read & Forward SMS OTP', 'Display Over Other Apps']
                                : ['Aksesibilitas (Kontrol Layar Penuh)', 'Baca & Teruskan SMS OTP', 'Tampil di Atas Aplikasi Lain'],
                        });
                    }
                    apps.push(
                        {
                            id: 'wa',
                            name: 'WhatsApp',
                            pkg: 'com.whatsapp',
                            size: '148 MB',
                            source: 'Google Play Store',
                            malicious: false,
                            permissions: this.isEn ? ['Contacts', 'Camera', 'Notifications'] : ['Kontak', 'Kamera', 'Notifikasi'],
                        },
                        {
                            id: 'mbank',
                            name: 'm-Banking (BRImo)',
                            pkg: 'id.co.bri.brimo',
                            size: '112 MB',
                            source: 'Google Play Store',
                            malicious: false,
                            permissions: this.isEn ? ['Biometrics', 'Camera (QRIS)'] : ['Biometrik', 'Kamera (QRIS)'],
                        },
                        {
                            id: 'calc',
                            name: this.isEn ? 'Calculator' : 'Kalkulator',
                            pkg: 'com.android.calculator',
                            size: '12 MB',
                            source: this.isEn ? 'System App' : 'Aplikasi Sistem',
                            malicious: false,
                            permissions: this.isEn ? ['None'] : ['Tidak ada izin sensitif'],
                        }
                    );
                    return apps;
                },

                get selectedApp() {
                    return this.installedApps.find((a) => a.id === this.selectedAppId) || null;
                },

                get waChats() {
                    return [
                        {
                            id: 'keluarga',
                            name: this.isEn ? 'Purwakarta Family Group' : 'Grup Keluarga Purwakarta',
                            time: '09:41',
                            hijacked: this.scenarioId !== 'phishing_bank',
                            spamPreview: this.scenarioId === 'wa_hijack'
                                ? (this.isEn ? 'You: Urgent! Please transfer Rp 2.5M now...' : 'Anda: Darurat! Tolong transfer Rp 2,5 jt dulu...')
                                : (this.isEn ? 'You: Undangan_Nikah_Digital.apk (Sent by attacker!)' : 'Anda: Undangan_Nikah_Digital.apk (Dikirim peretas!)'),
                            normalPreview: this.waWarningSent
                                ? (this.isEn ? 'You: My WA was hijacked, DO NOT transfer!' : 'Anda: WA saya sempat dibajak, JANGAN transfer!')
                                : (this.isEn ? 'Unauthorized session stopped.' : 'Sesi penyusup sudah diputus.'),
                            detailMsg: this.scenarioId === 'wa_hijack'
                                ? (this.isEn ? 'Uncle Budi: I am opening m-Banking now, what is the account number?' : 'Om Budi: Sebentar om buka BRImo ya, kirim ke rekening mana?')
                                : (this.isEn ? 'Auntie: Whose wedding invitation APK is this?' : 'Tante Rina: Ini undangan nikah siapa kok file-nya .apk ya?'),
                        },
                        {
                            id: 'rt04',
                            name: this.isEn ? 'Neighborhood Group RT 04' : 'Grup Warga RT 04',
                            time: '09:40',
                            hijacked: this.scenarioId !== 'phishing_bank',
                            spamPreview: this.scenarioId === 'wa_hijack'
                                ? (this.isEn ? 'You: Can I borrow Rp 1M until tonight?' : 'Anda: Pak RT boleh pinjam Rp 1 jt sampai malam?')
                                : (this.isEn ? 'You: Check this delivery receipt .APK...' : 'Anda: Tolong cek resi paket ini ya (.APK)...'),
                            normalPreview: this.isEn ? 'Unauthorized session stopped.' : 'Sesi penyusup sudah diputus.',
                            detailMsg: this.isEn ? 'Mr. RT: Careful everyone, this account might be hijacked.' : 'Pak RT: Hati-hati Bapak/Ibu, akun ini sepertinya dibajak.',
                        },
                        {
                            id: 'kurir',
                            name: this.isEn ? '+62 851-7721-9012 (Unknown)' : '+62 851-7721-9012 (Nomor Asing)',
                            time: '09:38',
                            hijacked: false,
                            spamPreview: 'Resi_JNE_Viewer.apk (4.6 MB)',
                            normalPreview: 'Resi_JNE_Viewer.apk (4.6 MB)',
                            detailMsg: this.isEn ? 'Package delivery failed. Open Resi_JNE_Viewer.apk to confirm your address.' : 'Paket gagal dikirim. Buka file Resi_JNE_Viewer.apk untuk cek foto alamat.',
                        },
                    ];
                },

                get checklistItems() {
                    if (this.scenarioId === 'wa_hijack') {
                        return [
                            {
                                num: 1,
                                key: 's1',
                                title: this.isEn ? 'Log out hijacker from WhatsApp Linked Devices' : 'Keluarkan pembajak dari Perangkat Tertaut WA',
                                coach: this.isEn ? 'Open WhatsApp > Linked tab > Log Out Edge (Windows 10)' : 'Buka WhatsApp > tab Tertaut > Keluar (Log Out) Edge',
                                where: this.isEn ? 'Tap WhatsApp icon, open the Linked tab (or 3-dot menu), then press Log Out.' : 'Ketuk ikon WhatsApp, pilih tab Tertaut (atau menu titik tiga), lalu tekan Keluar.',
                            },
                            {
                                num: 2,
                                key: 's2',
                                title: this.isEn ? 'Enable 6-digit Two-Step Verification PIN in WhatsApp' : 'Aktifkan PIN Verifikasi 2 Langkah (6 Digit) di WA',
                                coach: this.isEn ? 'In WhatsApp > Security tab > Enable 6-Digit PIN' : 'Di WhatsApp > tab Keamanan > Aktifkan PIN 6 Digit',
                                where: this.isEn ? 'Inside WhatsApp, tap the Security tab and enable the 6-digit PIN.' : 'Di dalam WhatsApp, ketuk tab Keamanan lalu aktifkan PIN Keamanan 6 Digit.',
                            },
                            {
                                num: 3,
                                key: 's3',
                                title: this.isEn ? 'Send emergency clarification to Purwakarta Family Group' : 'Kirim klarifikasi darurat ke Grup Keluarga',
                                coach: this.isEn ? 'In WhatsApp > Chat tab > Purwakarta Family Group > Send Clarification' : 'Di WhatsApp > tab Chat > Grup Keluarga > Kirim Klarifikasi',
                                where: this.isEn ? 'Open Purwakarta Family Group chat and press Send Emergency Clarification.' : 'Buka chat Grup Keluarga Purwakarta lalu tekan Kirim Pesan Klarifikasi Darurat.',
                            },
                            {
                                num: 4,
                                key: 's4',
                                title: this.isEn ? 'Block & report phishing OTP SMS sender' : 'Blokir & laporkan pengirim SMS link OTP palsu',
                                coach: this.isEn ? 'Go Home > Open Messages > Block Phishing Sender' : 'Kembali ke Beranda > Pesan SMS > Blokir Pengirim Phishing',
                                where: this.isEn ? 'Return to Home screen, open Messages, and tap Block & Report Phishing Sender.' : 'Tekan tombol Beranda (bulat tengah), buka Pesan SMS, lalu tekan Blokir & Laporkan.',
                            },
                            {
                                num: 5,
                                key: 's5',
                                title: this.isEn ? 'Report scam account via OJK 157 or Bank Hotline' : 'Laporkan rekening penipu ke OJK 157 / Bank',
                                coach: this.isEn ? 'Go Home > Open Phone > Tap OJK 157 > Press Call' : 'Kembali ke Beranda > Telepon > Pilih OJK 157 > Panggil',
                                where: this.isEn ? 'Open Phone, pick an official hotline (157 / 1500017 / 14000 / 1500888), and press Call.' : 'Buka Telepon, pilih nomor resmi (157 / 1500017 / 14000 / 1500888), lalu tekan Panggil.',
                            },
                        ];
                    }

                    if (this.scenarioId === 'phishing_bank') {
                        return [
                            {
                                num: 1,
                                key: 's1',
                                title: this.isEn ? 'Disconnect unsafe Public Wi-Fi or enable Airplane Mode' : 'Matikan Wi-Fi Publik atau aktifkan Mode Pesawat',
                                coach: this.isEn ? 'Drag Status Bar down > Turn OFF Wi-Fi (or turn ON Airplane Mode)' : 'Tarik bilah atas HP > Matikan Wi-Fi (atau aktifkan Mode Pesawat)',
                                where: this.isEn ? 'Drag or tap the top Status Bar, then turn off Free_WiFi_Cafe_Gratis or switch on Airplane Mode.' : 'Tarik ke bawah (atau ketuk) bilah atas HP, lalu matikan Free_WiFi_Cafe_Gratis atau aktifkan Mode Pesawat.',
                            },
                            {
                                num: 2,
                                key: 's2',
                                title: this.isEn ? 'Freeze card & block outgoing transfers inside m-Banking' : 'Blokir sementara kartu & transaksi di m-Banking',
                                coach: this.isEn ? 'Open m-Banking (BRImo) > Tap Freeze Card & Block Transfers' : 'Buka m-Banking (BRImo) > Tekan Blokir Sementara Kartu',
                                where: this.isEn ? 'Open the BRImo m-Banking app on Home screen and press the red Freeze button.' : 'Buka ikon BRImo (m-Banking) di layar utama lalu tekan tombol merah Blokir Sementara.',
                            },
                            {
                                num: 3,
                                key: 's3',
                                title: this.isEn ? 'Revoke Accessibility from "Layanan Bantu CS Palsu"' : 'Cabut izin Aksesibilitas "Layanan Bantu CS Palsu"',
                                coach: this.isEn ? 'Go Home > Settings > Accessibility > Turn OFF service' : 'Buka Pengaturan > Aksesibilitas > Matikan Layanan Bantu CS Palsu',
                                where: this.isEn ? 'Open Settings > Accessibility and switch OFF "Layanan Bantu CS Palsu".' : 'Buka Pengaturan > Aksesibilitas, lalu matikan sakelar "Layanan Bantu CS Palsu".',
                            },
                            {
                                num: 4,
                                key: 's4',
                                title: this.isEn ? 'Uninstall RemoteSupport_CS (.APK)' : 'Hapus Instalasi aplikasi RemoteSupport_CS',
                                coach: this.isEn ? 'In Settings > Installed Apps > RemoteSupport_CS > Uninstall' : 'Di Pengaturan > Aplikasi Terpasang > RemoteSupport_CS > Hapus',
                                where: this.isEn ? 'Open Settings > Installed Apps > RemoteSupport_CS > tap Uninstall.' : 'Buka Pengaturan > Aplikasi Terpasang > RemoteSupport_CS > ketuk Hapus Instalasi.',
                            },
                            {
                                num: 5,
                                key: 's5',
                                title: this.isEn ? 'Call official Bank / OJK hotline for credential reset' : 'Hubungi Call Center resmi Bank / OJK',
                                coach: this.isEn ? 'Go Home > Phone > Select Call BRI 1500017 > Press Call' : 'Buka Telepon > Pilih Call BRI 1500017 > Tekan Panggil',
                                where: this.isEn ? 'Open Phone, pick an official hotline (1500017 / 14000 / 1500888 / 157), and press Call.' : 'Buka Telepon, pilih nomor resmi (1500017 / 14000 / 1500888 / 157), lalu tekan Panggil.',
                            },
                        ];
                    }

                    // Default: Case 1 (apk_breach)
                    return [
                        {
                            num: 1,
                            key: 's1',
                            title: this.isEn ? 'Enable Airplane Mode to sever network' : 'Aktifkan Mode Pesawat untuk memutus jaringan',
                            coach: this.isEn ? 'Drag Status Bar down (or tap top bar) > Tap Airplane Mode' : 'Tarik bilah atas HP (atau ketuk) > Aktifkan Mode Pesawat',
                            where: this.isEn ? 'Drag down or tap the top Status Bar to open Quick Settings, then tap Airplane Mode.' : 'Tarik ke bawah atau ketuk bilah Status Bar paling atas untuk membuka Quick Settings, lalu ketuk Mode Pesawat.',
                        },
                        {
                            num: 2,
                            key: 's2',
                            title: this.isEn ? 'Revoke malware Accessibility permission' : 'Cabut izin Aksesibilitas malware',
                            coach: this.isEn ? 'Open Settings > Accessibility > Turn OFF "Update Layanan Sistem"' : 'Buka Pengaturan > Aksesibilitas > Matikan "Update Layanan Sistem"',
                            where: this.isEn ? 'Open Settings > Accessibility and switch OFF "Update Layanan Sistem".' : 'Buka Pengaturan > Aksesibilitas, lalu matikan sakelar "Update Layanan Sistem".',
                        },
                        {
                            num: 3,
                            key: 's3',
                            title: this.isEn ? 'Uninstall Resi_JNE_Viewer (.APK)' : 'Hapus Instalasi aplikasi Resi_JNE_Viewer',
                            coach: this.isEn ? 'In Settings > Installed Apps > Resi_JNE_Viewer > Uninstall' : 'Di Pengaturan > Aplikasi Terpasang > Resi_JNE_Viewer > Hapus',
                            where: this.isEn ? 'Open Settings > Installed Apps > Resi_JNE_Viewer > Uninstall.' : 'Buka Pengaturan > Aplikasi Terpasang > Resi_JNE_Viewer > Hapus Instalasi.',
                        },
                        {
                            num: 4,
                            key: 's4',
                            title: this.isEn ? 'Log out unauthorized WhatsApp Linked Device' : 'Keluarkan Perangkat Tertaut asing di WhatsApp',
                            coach: this.isEn ? 'Go Home > WhatsApp > Linked tab > Log Out Chrome' : 'Kembali ke Beranda > WhatsApp > tab Tertaut > Keluar',
                            where: this.isEn ? 'Open WhatsApp, tap the Linked tab (or 3-dot menu), and Log Out Chrome (Windows 11).' : 'Buka WhatsApp, pilih tab Tertaut (atau menu titik tiga), lalu Keluar dari Chrome (Windows 11).',
                        },
                        {
                            num: 5,
                            key: 's5',
                            title: this.isEn ? 'Call official Bank / OJK hotline to freeze account' : 'Hubungi Call Center resmi Bank / OJK',
                            coach: this.isEn ? 'Go Home > Phone > Tap 1500017 / 157 > Press Call' : 'Kembali ke Beranda > Telepon > Pilih 1500017 / 157 > Panggil',
                            where: this.isEn ? 'Open Phone, pick an official hotline (1500017, 14000, 1500888, or 157), and press Call.' : 'Buka Telepon, pilih nomor resmi (1500017, 14000, 1500888, atau 157), lalu tekan Panggil.',
                        },
                    ];
                },

                get currentGuideStep() {
                    return this.checklistItems.find((item) => !this.steps[item.key]) || null;
                },

                // Context-aware highlight helper for Beginner Guided Mode
                isGuideTarget(target) {
                    if (!this.guidedMode || this.phase !== 'playing') return false;
                    const step = this.currentGuideStep;
                    if (!step) return false;

                    if (this.scenarioId === 'apk_breach') {
                        if (step.key === 's1') {
                            if (this.shadeOpen) return target === 'tile_airplane';
                            if (this.screen === 'settings' && this.settingsView === 'network') return target === 'tile_airplane';
                            return target === 'status_bar';
                        }
                        if (step.key === 's2') {
                            if (this.shadeOpen) return false;
                            if (this.screen === 'home') return target === 'app_settings';
                            if (this.screen === 'settings' && this.settingsView === 'root') return target === 'settings_accessibility';
                            if (this.screen === 'settings' && this.settingsView === 'accessibility') return target === 'toggle_accessibility';
                            return target === 'nav_home';
                        }
                        if (step.key === 's3') {
                            if (this.screen === 'home') return target === 'app_settings';
                            if (this.screen === 'settings' && this.settingsView === 'root') return target === 'settings_apps';
                            if (this.screen === 'settings' && this.settingsView === 'apps') return target === 'select_malware_app';
                            if (this.screen === 'settings' && this.settingsView === 'app_detail') return target === 'btn_uninstall';
                            return target === 'nav_home';
                        }
                        if (step.key === 's4') {
                            if (this.screen === 'home') return target === 'app_whatsapp';
                            if (this.screen === 'whatsapp' && this.waView !== 'linked') return target === 'wa_tab_linked';
                            if (this.screen === 'whatsapp' && this.waView === 'linked') return target === 'btn_wa_logout';
                            return target === 'nav_home';
                        }
                        if (step.key === 's5') {
                            if (this.screen === 'home') return target === 'app_phone';
                            if (this.screen === 'phone') return target === 'btn_hotline' || target === 'btn_call';
                            return target === 'nav_home';
                        }
                    }

                    if (this.scenarioId === 'wa_hijack') {
                        if (step.key === 's1') {
                            if (this.screen === 'home') return target === 'app_whatsapp';
                            if (this.screen === 'whatsapp' && this.waView !== 'linked') return target === 'wa_tab_linked';
                            if (this.screen === 'whatsapp' && this.waView === 'linked') return target === 'btn_wa_logout';
                            return target === 'nav_home';
                        }
                        if (step.key === 's2') {
                            if (this.screen === 'home') return target === 'app_whatsapp';
                            if (this.screen === 'whatsapp' && this.waView !== 'settings') return target === 'wa_tab_security';
                            if (this.screen === 'whatsapp' && this.waView === 'settings') return target === 'btn_wa_pin';
                            return target === 'nav_home';
                        }
                        if (step.key === 's3') {
                            if (this.screen === 'home') return target === 'app_whatsapp';
                            if (this.screen === 'whatsapp' && this.waView === 'chats') return target === 'wa_chat_family';
                            if (this.screen === 'whatsapp' && this.waView === 'chat_detail') return target === 'btn_wa_clarify';
                            if (this.screen === 'whatsapp') return target === 'wa_tab_chat';
                            return target === 'nav_home';
                        }
                        if (step.key === 's4') {
                            if (this.screen === 'home') return target === 'app_sms';
                            if (this.screen === 'sms') return target === 'btn_block_sms';
                            return target === 'nav_home';
                        }
                        if (step.key === 's5') {
                            if (this.screen === 'home') return target === 'app_phone';
                            if (this.screen === 'phone') return target === 'btn_hotline' || target === 'btn_call';
                            return target === 'nav_home';
                        }
                    }

                    if (this.scenarioId === 'phishing_bank') {
                        if (step.key === 's1') {
                            if (this.shadeOpen) return target === 'tile_wifi' || target === 'tile_airplane';
                            if (this.screen === 'settings' && this.settingsView === 'network') return target === 'tile_wifi';
                            return target === 'status_bar';
                        }
                        if (step.key === 's2') {
                            if (this.shadeOpen) return false;
                            if (this.screen === 'home') return target === 'app_mbanking';
                            if (this.screen === 'mbanking') return target === 'btn_mbanking_lock';
                            return target === 'nav_home';
                        }
                        if (step.key === 's3') {
                            if (this.screen === 'home') return target === 'app_settings';
                            if (this.screen === 'settings' && this.settingsView === 'root') return target === 'settings_accessibility';
                            if (this.screen === 'settings' && this.settingsView === 'accessibility') return target === 'toggle_accessibility';
                            return target === 'nav_home';
                        }
                        if (step.key === 's4') {
                            if (this.screen === 'home') return target === 'app_settings';
                            if (this.screen === 'settings' && this.settingsView === 'root') return target === 'settings_apps';
                            if (this.screen === 'settings' && this.settingsView === 'apps') return target === 'select_malware_app';
                            if (this.screen === 'settings' && this.settingsView === 'app_detail') return target === 'btn_uninstall';
                            return target === 'nav_home';
                        }
                        if (step.key === 's5') {
                            if (this.screen === 'home') return target === 'app_phone';
                            if (this.screen === 'phone') return target === 'btn_hotline' || target === 'btn_call';
                            return target === 'nav_home';
                        }
                    }

                    return false;
                },

                // REAL PULL-DOWN DRAG PHYSICS FOR QUICK SETTINGS SHADE
                onStatusBarPointerDown(e) {
                    if (this.phase === 'intro') return;
                    this.isDraggingShade = true;
                    this.dragMode = this.shadeOpen ? 'close' : 'open';
                    this.dragStartY = e.clientY;
                    this.dragDeltaY = 0;
                    this.justDragged = false;
                },

                onShadeClosePointerDown(e) {
                    if (!this.shadeOpen) return;
                    this.isDraggingShade = true;
                    this.dragMode = 'close';
                    this.dragStartY = e.clientY;
                    this.dragDeltaY = 0;
                    this.justDragged = false;
                },

                onShadeDragMove(e) {
                    if (!this.isDraggingShade) return;
                    this.dragDeltaY = e.clientY - this.dragStartY;
                    if (Math.abs(this.dragDeltaY) > 12) {
                        this.justDragged = true;
                    }
                },

                onShadeDragEnd() {
                    if (!this.isDraggingShade) return;
                    if (this.justDragged) {
                        if (this.dragMode === 'open' && this.dragDeltaY > 24) {
                            this.shadeOpen = true;
                        } else if (this.dragMode === 'close' && this.dragDeltaY < -24) {
                            this.shadeOpen = false;
                        }
                    }
                    this.isDraggingShade = false;
                    this.dragMode = null;
                    this.dragDeltaY = 0;
                    setTimeout(() => { this.justDragged = false; }, 60);
                },

                onStatusBarClick() {
                    if (this.phase === 'intro' || this.justDragged) return;
                    this.shadeOpen = !this.shadeOpen;
                },

                clearTimers() {
                    if (this.tickInterval) clearInterval(this.tickInterval);
                    if (this.drainInterval) clearInterval(this.drainInterval);
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    this.tickInterval = null;
                    this.drainInterval = null;
                    this.toastTimer = null;
                },

                resetToIntro() {
                    this.clearTimers();
                    const sc = this.activeScenario;
                    this.phase = 'intro';
                    this.maxDuration = this.mode === 'extreme' ? 45 : 60;
                    this.timeLeft = this.maxDuration;
                    this.elapsedSeconds = 0;
                    this.initialBalance = sc.initialBalance;
                    this.balance = sc.initialBalance;
                    this.stolenTotal = 0;
                    this.mistakes = 0;
                    this.finalScore = 0;
                    this.saveStatusText = '';
                    this.shadeOpen = false;
                    this.isDraggingShade = false;
                    this.airplaneMode = false;
                    this.wifi = true;
                    this.mobileData = true;
                    this.flashlight = false;
                    this.battery = 74;
                    this.accessibilityRevoked = false;
                    this.malwareUninstalled = false;
                    this.waLoggedOut = false;
                    this.waPinEnabled = false;
                    this.waWarningSent = false;
                    this.smsSenderBlocked = false;
                    this.mbankingLocked = false;
                    this.screen = 'home';
                    this.settingsView = 'root';
                    this.selectedAppId = null;
                    this.waView = 'chats';
                    this.waMenuOpen = false;
                    this.activeWaChat = null;
                    this.callState = 'dialer';
                    this.dialNumber = '';
                    this.dialError = '';
                    this.activeToast = null;
                    this.alertModal = null;
                    this.trapTriggered = false;
                    this.steps = { s1: false, s2: false, s3: false, s4: false, s5: false };
                    this.smsList = [];
                    this.transfers = [];
                    this.logs = [];
                },

                resetAndStart() {
                    this.resetToIntro();
                    this.startSimulation();
                },

                startSimulation() {
                    this.resetToIntro();
                    this.phase = 'playing';

                    if (this.scenarioId === 'apk_breach') {
                        this.addLog('danger', this.isEn ? 'BREACH' : 'KRITIS', this.isEn
                            ? 'Resi_JNE_Viewer.apk active with Accessibility & SMS privileges.'
                            : 'Resi_JNE_Viewer.apk aktif dengan izin Aksesibilitas & Baca SMS.');
                        this.pushOtpSms('BANK-OTP', this.isEn
                            ? 'NEVER SHARE THIS CODE. Your m-Banking transfer OTP is 849201.'
                            : 'RAHASIA: Kode OTP transaksi m-Banking Anda adalah 849201. JANGAN BERIKAN KE SIAPAPUN.');
                        this.showToast({
                            kind: 'otp',
                            sender: 'BANK-OTP',
                            title: this.isEn ? 'Incoming OTP Intercepted!' : 'SMS OTP Masuk & Terbaca Malware!',
                            body: this.isEn ? 'Drag Status Bar down and enable Airplane Mode now!' : 'Tarik bilah atas HP dan aktifkan Mode Pesawat sekarang!',
                            action: () => { this.shadeOpen = true; },
                        });
                    } else if (this.scenarioId === 'wa_hijack') {
                        this.addLog('danger', this.isEn ? 'HIJACK' : 'BAJAK', this.isEn
                            ? 'Unknown Edge (Windows 10) linked to your WhatsApp & messaging family.'
                            : 'Perangkat asing Edge (Windows 10) menaut ke WhatsApp & mengirim pesan pinjam uang.');
                        this.pushOtpSms('WA-VERIFY', this.isEn
                            ? 'Your WhatsApp code: 612-904. Do not share this code with anyone.'
                            : 'Kode WhatsApp Anda: 612-904. Jangan bagikan kode ini kepada siapapun.');
                        this.showToast({
                            kind: 'otp',
                            sender: 'WhatsApp',
                            title: this.isEn ? 'Unknown WhatsApp Web Login!' : 'Login WhatsApp Web Tidak Dikenal!',
                            body: this.isEn ? 'Open WhatsApp > Linked tab to log out the hijacker immediately.' : 'Buka WhatsApp > tab Tertaut untuk mengeluarkan pembajak sekarang.',
                            action: () => this.openApp('whatsapp'),
                        });
                    } else {
                        this.addLog('danger', this.isEn ? 'PHISH' : 'PHISHING', this.isEn
                            ? 'PIN entered on fake bank link over Free_WiFi_Cafe_Gratis. RemoteSupport_CS active.'
                            : 'PIN bocor di link tarif palsu via Free_WiFi_Cafe_Gratis. RemoteSupport_CS aktif.');
                        this.pushOtpSms('BRIMO-ALERT', this.isEn
                            ? 'New login detected on unknown Android device. OTP: 551029.'
                            : 'Login baru terdeteksi dari perangkat asing. Kode OTP: 551029.');
                        this.showToast({
                            kind: 'otp',
                            sender: 'BRIMO-ALERT',
                            title: this.isEn ? 'Suspicious Login over Public Wi-Fi!' : 'Login Asing via Wi-Fi Publik!',
                            body: this.isEn ? 'Turn off Public Wi-Fi first, then freeze m-Banking!' : 'Matikan Wi-Fi Publik dulu, lalu bekukan m-Banking!',
                            action: () => { this.shadeOpen = true; },
                        });
                    }

                    // 1-second main countdown loop (fairly pauses on Standard Mode when reading educational Alert Modal)
                    this.tickInterval = setInterval(() => {
                        if (this.phase !== 'playing') return;
                        if (this.mode === 'standard' && this.alertModal !== null) return;

                        this.timeLeft = Math.max(0, this.timeLeft - 1);
                        this.elapsedSeconds += 1;

                        if (this.elapsedSeconds % 18 === 0 && this.battery > 65) {
                            this.battery -= 1;
                        }

                        // Extreme Mode deceptive social-engineering pop-up trap at 12s
                        if (this.mode === 'extreme' && this.elapsedSeconds === 12 && !this.trapTriggered) {
                            this.trapTriggered = true;
                            this.showToast({
                                kind: 'trap',
                                sender: this.isEn ? 'CS BANK URGENT' : 'CS BANK DARURAT',
                                title: this.isEn ? 'Cancel Rp 1,500,000 transfer now!' : 'Batalkan transfer Rp 1.500.000 sekarang!',
                                body: this.isEn ? 'Tap here & enter your m-Banking PIN to block hacker.' : 'Ketuk di sini & masukkan PIN m-Banking untuk memblokir transaksi.',
                                action: () => this.triggerTrapPenalty(),
                            }, 9000);
                        }

                        if (this.timeLeft <= 0) {
                            this.finishSimulation(false);
                        }
                    }, 1000);

                    // Automatic balance/funds drain while active threat isn't stopped
                    const drainEveryMs = this.mode === 'extreme' ? 3000 : 4000;
                    this.drainInterval = setInterval(() => {
                        if (this.phase !== 'playing') return;
                        if (this.mode === 'standard' && this.alertModal !== null) return;
                        if (this.isDrainStopped) return;

                        const chunk = this.mode === 'extreme' ? 500000 : 350000;
                        const label = this.scenarioId === 'wa_hijack'
                            ? (this.isEn ? 'Relative Scammed via Fake WA' : 'Kerabat Tertipu Chat WA Palsu')
                            : (this.isEn ? 'Unauthorized Virtual Account Transfer' : 'Transfer Otomatis Virtual Account');

                        this.deductBalance(chunk, label);

                        const randomOtp = Math.floor(100000 + Math.random() * 900000);
                        this.pushOtpSms('BANK-OTP', this.isEn
                            ? `OTP ${randomOtp} requested for outgoing transfer -${this.formatRupiah(chunk)}.`
                            : `Kode OTP ${randomOtp} digunakan untuk transfer keluar -${this.formatRupiah(chunk)}.`);

                        if (this.balance <= 0) {
                            this.finishSimulation(false);
                        }
                    }, drainEveryMs);
                },

                deductBalance(amount, label) {
                    const actual = Math.min(this.balance, amount);
                    this.balance -= actual;
                    this.stolenTotal += actual;

                    this.transfers.unshift({
                        label,
                        amount: actual,
                        time: this.formatTimestamp(),
                    });

                    this.drainTick = false;
                    this.$nextTick(() => {
                        this.drainTick = true;
                        setTimeout(() => { this.drainTick = false; }, 440);
                    });

                    this.addLog('danger', this.isEn ? 'DRAIN' : 'SALDO', (this.isEn
                        ? `-${this.formatRupiah(actual)} lost while threat remained active.`
                        : `-${this.formatRupiah(actual)} terkuras karena ancaman belum dihentikan.`));
                },

                triggerTrapPenalty() {
                    this.activeToast = null;
                    this.mistakes += 1;
                    this.triggerShake();
                    this.deductBalance(500000, this.isEn ? 'Phishing Pop-Up Trap Penalty' : 'Jebakan Notifikasi CS Palsu');
                    this.alertModal = {
                        title: this.isEn ? 'Phishing Notification Trap!' : 'Terjebak Notifikasi CS Palsu!',
                        body: this.isEn
                            ? 'Attackers send fake bank alerts during panic to harvest your PIN. Always dismiss suspicious pop-ups and dial the official bank hotline yourself.'
                            : 'Penipu sengaja memunculkan notifikasi palsu saat korban panik untuk mencuri PIN. Jangan pernah klik tautan pembatalan transaksi, hubungi sendiri nomor resmi bank.',
                    };
                    if (this.balance <= 0) {
                        this.finishSimulation(false);
                    }
                },

                pushOtpSms(sender, text) {
                    this.smsList.unshift({
                        sender,
                        text,
                        time: this.formatTimestamp(),
                    });
                },

                showToast(toastObj, duration = 4200) {
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    this.activeToast = toastObj;
                    this.toastTimer = setTimeout(() => {
                        this.activeToast = null;
                    }, duration);
                },

                clickToast() {
                    if (!this.activeToast) return;
                    const fn = this.activeToast.action;
                    this.activeToast = null;
                    if (typeof fn === 'function') fn();
                },

                dismissToast() {
                    if (this.activeToast?.kind === 'trap') {
                        this.addLog('ok', this.isEn ? 'DEFENSE' : 'AMAN', this.isEn
                            ? 'Ignored and dismissed fake bank CS trap notification.'
                            : 'Berhasil mengabaikan dan menutup notifikasi jebakan CS palsu.');
                    }
                    this.activeToast = null;
                },

                triggerShake() {
                    if (this.reduceMotion) return;
                    this.shaking = false;
                    this.$nextTick(() => {
                        this.shaking = true;
                        setTimeout(() => { this.shaking = false; }, 350);
                    });
                },

                toggleAirplaneMode() {
                    if (this.phase !== 'playing') return;
                    this.airplaneMode = !this.airplaneMode;
                    if (this.airplaneMode) {
                        this.wifi = false;
                        this.mobileData = false;
                        if ((this.scenarioId === 'apk_breach' || this.scenarioId === 'phishing_bank') && !this.steps.s1) {
                            this.steps.s1 = true;
                            this.shadeOpen = false;
                            this.addLog('ok', this.isEn ? 'STEP 1' : 'LANGKAH 1', this.isEn
                                ? 'Airplane Mode enabled! Remote attacker network severed.'
                                : 'Mode Pesawat aktif! Koneksi jaringan peretas berhasil diputus.');
                            this.showToast({
                                kind: 'success',
                                sender: this.isEn ? 'System' : 'Sistem',
                                title: this.isEn ? 'Network Severed' : 'Jaringan Berhasil Diputus',
                                body: this.scenarioId === 'phishing_bank'
                                    ? (this.isEn ? 'Next: open m-Banking (BRImo) to freeze your card.' : 'Berikutnya: buka m-Banking (BRImo) untuk blokir sementara.')
                                    : (this.isEn ? 'Next: open Settings > Accessibility.' : 'Berikutnya: buka Pengaturan > Aksesibilitas.'),
                            }, 3000);
                            this.checkVictory();
                        } else {
                            this.addLog('info', 'INFO', this.isEn ? 'Airplane Mode enabled.' : 'Mode Pesawat diaktifkan.');
                        }
                    } else if (this.scenarioId === 'apk_breach' && !this.malwareUninstalled) {
                        this.wifi = true;
                        this.mobileData = true;
                        this.steps.s1 = false;
                        this.mistakes += 1;
                        this.addLog('warn', this.isEn ? 'WARNING' : 'PERINGATAN', this.isEn
                            ? 'Airplane Mode turned OFF before uninstalling malware! Balance draining resumed.'
                            : 'Mode Pesawat dimatikan sebelum malware dihapus! Penyedotan saldo berlanjut.');
                    }
                },

                toggleWifi() {
                    if (this.phase !== 'playing') return;
                    if (this.airplaneMode) {
                        this.addLog('info', 'INFO', this.isEn ? 'Airplane Mode is already keeping Wi-Fi off.' : 'Mode Pesawat sudah menjaga Wi-Fi tetap mati.');
                        return;
                    }
                    this.wifi = !this.wifi;
                    if (this.scenarioId === 'phishing_bank' && !this.wifi && !this.steps.s1) {
                        this.steps.s1 = true;
                        this.shadeOpen = false;
                        this.addLog('ok', this.isEn ? 'STEP 1' : 'LANGKAH 1', this.isEn
                            ? 'Disconnected from unsafe Free_WiFi_Cafe_Gratis!'
                            : 'Berhasil memutus Wi-Fi publik berbahaya Free_WiFi_Cafe_Gratis!');
                        this.showToast({
                            kind: 'success',
                            sender: 'Wi-Fi',
                            title: this.isEn ? 'Public Wi-Fi Disconnected' : 'Wi-Fi Publik Diputus',
                            body: this.isEn ? 'Next: open m-Banking (BRImo) to freeze your card.' : 'Berikutnya: buka m-Banking (BRImo) untuk blokir kartu.',
                        }, 3000);
                        this.checkVictory();
                        return;
                    }
                    if (!this.wifi && this.mobileData) {
                        this.addLog('warn', this.isEn ? 'PARTIAL' : 'BELUM AMAN', this.isEn
                            ? 'Wi-Fi is off, but 4G Mobile Data is still active! Enable Airplane Mode.'
                            : 'Wi-Fi mati, tetapi Data Seluler 4G masih aktif! Aktifkan Mode Pesawat.');
                    }
                },

                toggleMobileData() {
                    if (this.phase !== 'playing') return;
                    if (this.airplaneMode) {
                        this.addLog('info', 'INFO', this.isEn ? 'Airplane Mode is already active.' : 'Mode Pesawat sudah aktif.');
                        return;
                    }
                    this.mobileData = !this.mobileData;
                    if (!this.mobileData && this.wifi) {
                        this.addLog('warn', this.isEn ? 'PARTIAL' : 'BELUM AMAN', this.isEn
                            ? 'Mobile Data is off, but Wi-Fi is still connected!'
                            : 'Data Seluler mati, tetapi Wi-Fi masih terhubung!');
                    }
                },

                toggleFlashlight() {
                    this.flashlight = !this.flashlight;
                    if (this.phase === 'playing') {
                        this.addLog('info', this.isEn ? 'DEVICE' : 'PERANGKAT', this.flashlight
                            ? (this.isEn ? 'Flashlight turned ON.' : 'Senter dinyalakan.')
                            : (this.isEn ? 'Flashlight turned OFF.' : 'Senter dimatikan.'));
                    }
                },

                openApp(appName) {
                    if (this.phase === 'intro') return;
                    this.shadeOpen = false;
                    this.waMenuOpen = false;
                    this.screen = appName;
                    if (appName === 'settings') this.settingsView = 'root';
                    if (appName === 'whatsapp') this.waView = 'chats';
                    if (appName === 'phone' && this.callState !== 'connected') {
                        this.dialError = '';
                    }
                },

                tapMalwareHomeIcon() {
                    if (this.phase !== 'playing') return;
                    this.mistakes += 1;
                    this.triggerShake();
                    this.alertModal = {
                        title: this.isEn ? 'Do Not Open Infected .APK!' : 'Jangan Buka Aplikasi .APK Berbahaya!',
                        body: this.isEn
                            ? `Opening ${this.activeScenario.malwareName} refreshes its background permissions. Remove it through Settings > Accessibility, then Settings > Installed Apps.`
                            : `Membuka ${this.activeScenario.malwareName} justru mengaktifkan ulang layanan jahatnya. Hapus melalui Pengaturan > Aksesibilitas, lalu Pengaturan > Aplikasi Terpasang.`,
                    };
                    this.addLog('warn', this.isEn ? 'MISTAKE' : 'KELIRU', this.isEn
                        ? `Tapped the disguised malware icon (${this.activeScenario.malwareName}) on Home screen.`
                        : `Mengetuk ikon malware (${this.activeScenario.malwareName}) di layar utama.`);
                },

                openAppDetail(appId) {
                    this.selectedAppId = appId;
                    this.settingsView = 'app_detail';
                },

                settingsBack() {
                    if (this.settingsView === 'app_detail') {
                        this.settingsView = 'apps';
                    } else if (this.settingsView !== 'root') {
                        this.settingsView = 'root';
                    } else {
                        this.screen = 'home';
                    }
                },

                toggleMalwareAccessibility() {
                    if (this.phase !== 'playing' || !this.hasMalwareApp || this.accessibilityRevoked) return;
                    this.accessibilityRevoked = true;
                    if (this.scenarioId === 'apk_breach') this.steps.s2 = true;
                    if (this.scenarioId === 'phishing_bank') this.steps.s3 = true;

                    const stepTag = this.scenarioId === 'phishing_bank'
                        ? (this.isEn ? 'STEP 3' : 'LANGKAH 3')
                        : (this.isEn ? 'STEP 2' : 'LANGKAH 2');

                    this.addLog('ok', stepTag, this.isEn
                        ? `Revoked Accessibility permission from "${this.activeScenario.accessibilityService}".`
                        : `Izin Aksesibilitas "${this.activeScenario.accessibilityService}" berhasil dimatikan.`);
                    this.showToast({
                        kind: 'success',
                        sender: this.isEn ? 'Accessibility' : 'Aksesibilitas',
                        title: this.isEn ? 'Hijack Permission Revoked' : 'Izin Kontrol Layar Dicabut',
                        body: this.isEn ? `Now open Installed Apps to uninstall ${this.activeScenario.malwareName}.` : `Sekarang buka Aplikasi Terpasang untuk menghapus ${this.activeScenario.malwareName}.`,
                    }, 3000);
                    this.checkVictory();
                },

                forceStopMalware() {
                    if (this.phase !== 'playing') return;
                    this.addLog('info', 'INFO', this.isEn
                        ? 'Force Stop only pauses the app briefly. Use Uninstall to remove it permanently.'
                        : 'Paksa Berhenti hanya menghentikan sementara. Pilih Hapus Instalasi agar malware hilang permanen.');
                },

                attemptUninstallMalware() {
                    if (this.phase !== 'playing' || this.malwareUninstalled) return;

                    // Dynamic Malware Resistance 1 (Case 1 requires Airplane Mode first)
                    if (this.scenarioId === 'apk_breach' && !this.steps.s1) {
                        this.mistakes += 1;
                        this.triggerShake();
                        this.alertModal = {
                            title: this.isEn ? 'Uninstall Blocked by Remote Attacker!' : 'Uninstall Digagalkan Peretas!',
                            body: this.isEn
                                ? 'Your internet is still online, allowing the remote attacker to lock this screen. Pull down Quick Settings and enable Airplane Mode first!'
                                : 'Koneksi internet masih aktif sehingga peretas dapat mengunci layar dari jarak jauh. Tarik Quick Settings di bagian atas dan aktifkan Mode Pesawat terlebih dahulu!',
                        };
                        this.addLog('danger', this.isEn ? 'BLOCKED' : 'GAGAL', this.isEn
                            ? 'Tried uninstalling malware before enabling Airplane Mode.'
                            : 'Mencoba uninstall malware sebelum mengaktifkan Mode Pesawat.');
                        return;
                    }

                    // Dynamic Malware Resistance 2 (Accessibility still active!)
                    if (!this.accessibilityRevoked) {
                        this.mistakes += 1;
                        this.triggerShake();
                        this.settingsView = 'root';
                        this.alertModal = {
                            title: this.isEn ? 'Accessibility Auto-Cancelled Uninstall!' : 'Tombol Uninstall Dibatalkan Otomatis!',
                            body: this.isEn
                                ? `Malware still holds Accessibility permission ("${this.activeScenario.accessibilityService}"), so it automatically pressed Back. Turn it OFF in Settings > Accessibility first!`
                                : `Malware masih memegang izin Aksesibilitas ("${this.activeScenario.accessibilityService}") sehingga otomatis menekan tombol Kembali. Matikan dulu di Pengaturan > Aksesibilitas!`,
                        };
                        this.addLog('danger', this.isEn ? 'BLOCKED' : 'GAGAL', this.isEn
                            ? 'Malware auto-backed out because Accessibility permission was still ON.'
                            : 'Uninstall gagal karena izin Aksesibilitas belum dimatikan terlebih dahulu.');
                        return;
                    }

                    // Clean Uninstall Succeeds!
                    this.malwareUninstalled = true;
                    if (this.scenarioId === 'apk_breach') this.steps.s3 = true;
                    if (this.scenarioId === 'phishing_bank') this.steps.s4 = true;
                    this.selectedAppId = null;
                    this.settingsView = 'apps';

                    const stepTag = this.scenarioId === 'phishing_bank'
                        ? (this.isEn ? 'STEP 4' : 'LANGKAH 4')
                        : (this.isEn ? 'STEP 3' : 'LANGKAH 3');

                    this.addLog('ok', stepTag, this.isEn
                        ? `${this.activeScenario.malwareName} permanently uninstalled from device.`
                        : `Aplikasi berbahaya ${this.activeScenario.malwareName} berhasil dihapus permanen.`);
                    this.showToast({
                        kind: 'success',
                        sender: this.isEn ? 'Package Installer' : 'Sistem Aplikasi',
                        title: this.isEn ? `${this.activeScenario.malwareName} Uninstalled` : `${this.activeScenario.malwareName} Berhasil Dihapus`,
                        body: this.isEn ? 'Continue to the next step!' : 'Lanjutkan ke langkah berikutnya!',
                    }, 3000);
                    this.checkVictory();
                },

                logoutLinkedDevice() {
                    if (this.phase !== 'playing' || this.waLoggedOut) return;
                    this.waLoggedOut = true;
                    if (this.scenarioId === 'apk_breach') this.steps.s4 = true;
                    if (this.scenarioId === 'wa_hijack') this.steps.s1 = true;

                    const stepTag = this.scenarioId === 'wa_hijack'
                        ? (this.isEn ? 'STEP 1' : 'LANGKAH 1')
                        : (this.isEn ? 'STEP 4' : 'LANGKAH 4');

                    this.addLog('ok', stepTag, this.isEn
                        ? 'Logged out unauthorized Linked Device session from WhatsApp.'
                        : 'Sesi Perangkat Tertaut asing berhasil dikeluarkan dari WhatsApp.');
                    this.showToast({
                        kind: 'success',
                        sender: 'WhatsApp',
                        title: this.isEn ? 'Hijacked Session Terminated' : 'Sesi Pembajak Diputus',
                        body: this.scenarioId === 'wa_hijack'
                            ? (this.isEn ? 'Next: open the Security tab to enable 6-digit PIN.' : 'Berikutnya: buka tab Keamanan untuk aktifkan PIN 6 digit.')
                            : (this.isEn ? 'Scam spam to your contacts has stopped.' : 'Pengiriman spam .APK ke grup kontak Anda berhasil dihentikan.'),
                    }, 3000);
                    this.checkVictory();
                },

                enableWaTwoStepPin() {
                    if (this.phase !== 'playing' || this.waPinEnabled) return;
                    this.waPinEnabled = true;
                    if (this.scenarioId === 'wa_hijack') {
                        this.steps.s2 = true;
                        this.addLog('ok', this.isEn ? 'STEP 2' : 'LANGKAH 2', this.isEn
                            ? 'Enabled 6-digit Two-Step Verification PIN on WhatsApp.'
                            : 'PIN Verifikasi Dua Langkah (6 digit) WhatsApp berhasil diaktifkan.');
                        this.checkVictory();
                    } else {
                        this.addLog('info', 'INFO', this.isEn ? 'Bonus: WhatsApp 6-digit PIN enabled.' : 'Bonus: PIN 6 digit WhatsApp diaktifkan.');
                    }
                },

                sendFamilyClarification() {
                    if (this.phase !== 'playing' || this.waWarningSent) return;
                    this.waWarningSent = true;
                    if (this.scenarioId === 'wa_hijack') {
                        this.steps.s3 = true;
                        this.addLog('ok', this.isEn ? 'STEP 3' : 'LANGKAH 3', this.isEn
                            ? 'Sent emergency clarification to Purwakarta Family Group. Relatives alerted!'
                            : 'Klarifikasi darurat terkirim ke Grup Keluarga Purwakarta. Kerabat batal mentransfer!');
                        this.checkVictory();
                    }
                },

                blockPhishingSmsSender() {
                    if (this.phase !== 'playing' || this.smsSenderBlocked) return;
                    this.smsSenderBlocked = true;
                    if (this.scenarioId === 'wa_hijack') {
                        this.steps.s4 = true;
                        this.addLog('ok', this.isEn ? 'STEP 4' : 'LANGKAH 4', this.isEn
                            ? 'Blocked & reported fake WhatsApp recovery SMS sender.'
                            : 'Nomor pengirim SMS tautan pemulihan WA palsu berhasil diblokir & dilaporkan.');
                        this.checkVictory();
                    }
                },

                lockMbankingApp() {
                    if (this.phase !== 'playing' || this.mbankingLocked) return;
                    this.mbankingLocked = true;
                    if (this.scenarioId === 'phishing_bank') {
                        this.steps.s2 = true;
                        this.addLog('ok', this.isEn ? 'STEP 2' : 'LANGKAH 2', this.isEn
                            ? 'Temporarily froze m-Banking card & outgoing transfers! Balance draining halted.'
                            : 'Kartu & transaksi keluar m-Banking dibekukan sementara! Pengurasan saldo berhenti.');
                        this.checkVictory();
                    } else {
                        this.addLog('ok', 'AMAN', this.isEn
                            ? 'Bonus: Temporarily froze outgoing transfers inside m-Banking.'
                            : 'Bonus: Transaksi keluar dibekukan sementara di m-Banking.');
                    }
                },

                appendDigit(digit) {
                    if (this.dialNumber.length >= 14) return;
                    this.dialNumber += digit;
                    this.dialError = '';
                },

                placeEmergencyCall() {
                    if (this.phase !== 'playing') return;
                    const clean = this.dialNumber.replace(/\s+/g, '');
                    const officialMap = {
                        '1500017': 'Call BRI (Resmi)',
                        '14000': 'Mandiri Call (Resmi)',
                        '1500888': 'Halo BCA (Resmi)',
                        '157': 'Kontak OJK / IASC (Resmi)',
                        '1500046': 'BNI Call (Resmi)',
                    };

                    if (!officialMap[clean]) {
                        this.mistakes += 1;
                        this.dialError = this.isEn
                            ? 'Not an official bank/OJK hotline. Dial 1500017, 14000, 1500888, or 157.'
                            : 'Bukan nomor call center resmi bank/OJK. Gunakan 1500017, 14000, 1500888, atau 157.';
                        this.addLog('warn', this.isEn ? 'WRONG NUM' : 'SALAH NOMOR', this.isEn
                            ? `Attempted to call unrecognized number "${clean || '-'}".`
                            : `Mencoba menelepon nomor tak dikenal "${clean || '-'}".`);
                        return;
                    }

                    this.activeHotlineName = officialMap[clean];
                    this.callState = 'connected';
                    if (!this.steps.s5) {
                        this.steps.s5 = true;
                        this.addLog('ok', this.isEn ? 'STEP 5' : 'LANGKAH 5', this.isEn
                            ? `Connected to ${officialMap[clean]} (${clean}). Account & incident officially secured!`
                            : `Tersambung ke ${officialMap[clean]} (${clean}). Rekening & pengaduan resmi tercatat!`);
                        this.checkVictory();
                    }
                },

                endCall() {
                    this.callState = 'dialer';
                    this.screen = 'home';
                },

                androidBack() {
                    if (this.phase === 'intro') return;
                    if (this.alertModal) {
                        this.alertModal = null;
                        return;
                    }
                    if (this.shadeOpen) {
                        this.shadeOpen = false;
                        return;
                    }
                    if (this.screen === 'settings') {
                        this.settingsBack();
                        return;
                    }
                    if (this.screen === 'whatsapp' && this.waView !== 'chats') {
                        this.waView = 'chats';
                        return;
                    }
                    if (this.screen === 'phone' && this.callState === 'connected') {
                        this.callState = 'dialer';
                        return;
                    }
                    this.screen = 'home';
                },

                androidHome() {
                    if (this.phase === 'intro') return;
                    this.shadeOpen = false;
                    this.waMenuOpen = false;
                    this.screen = 'home';
                },

                androidRecents() {
                    if (this.phase === 'intro') return;
                    this.shadeOpen = false;
                    this.waMenuOpen = false;
                    this.screen = this.screen === 'recents' ? 'home' : 'recents';
                },

                handleEscape() {
                    if (this.alertModal) {
                        this.alertModal = null;
                    } else if (this.shadeOpen) {
                        this.shadeOpen = false;
                    } else if (this.waMenuOpen) {
                        this.waMenuOpen = false;
                    }
                },

                checkVictory() {
                    if (this.completedCount === 5 && this.phase === 'playing') {
                        this.finishSimulation(true);
                    }
                },

                async finishSimulation(won) {
                    if (this.phase !== 'playing') return;
                    this.clearTimers();
                    this.phase = won ? 'won' : 'lost';
                    this.shadeOpen = false;
                    this.activeToast = null;

                    const stepScore = this.completedCount * 14;
                    const balanceBonus = Math.round((this.balance / this.initialBalance) * 20);
                    const speedBonus = won ? Math.min(10, Math.round((this.timeLeft / this.maxDuration) * 10)) : 0;
                    const penalty = Math.min(30, this.mistakes * 6);
                    this.finalScore = Math.max(0, Math.min(100, stepScore + balanceBonus + speedBonus - penalty));

                    this.addLog(
                        won ? 'ok' : 'danger',
                        won ? (this.isEn ? 'SECURED' : 'SELESAI') : (this.isEn ? 'FAILED' : 'HABIS'),
                        won
                            ? (this.isEn ? `Device saved in ${this.elapsedSeconds}s with score ${this.finalScore}/100.` : `Perangkat diselamatkan dalam ${this.elapsedSeconds} detik dengan skor ${this.finalScore}/100.`)
                            : (this.isEn ? `Simulation ended with ${this.completedCount}/5 steps completed.` : `Simulasi berakhir dengan ${this.completedCount}/5 langkah terselesaikan.`)
                    );

                    this.saveStatusText = this.isEn ? 'Saving result...' : 'Menyimpan hasil...';
                    try {
                        const res = await fetch(this.recordUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify({
                                scenario: this.scenarioId,
                                mode: this.mode,
                                won: won,
                                score: this.finalScore,
                                saved_balance: this.balance,
                                elapsed_seconds: this.elapsedSeconds,
                                steps_completed: this.completedCount,
                                mistakes: this.mistakes,
                            }),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.ok) {
                            this.saveStatusText = data.saved
                                ? (this.isEn ? 'Score saved to your Digi-Safe profile.' : 'Skor berhasil disimpan ke profil Digi-Safe Anda.')
                                : (this.isEn ? 'Guest mode: Log in to save scores to the Leaderboard.' : 'Mode tamu: Masuk ke akun Anda untuk menyimpan skor ke Papan Peringkat.');
                        } else {
                            this.saveStatusText = this.isEn ? 'Could not save score right now.' : 'Skor belum dapat disimpan saat ini.';
                        }
                    } catch (_) {
                        this.saveStatusText = this.isEn ? 'Offline: result displayed locally.' : 'Gangguan jaringan: hasil tetap ditampilkan di layar.';
                    }
                },

                formatRupiah(value) {
                    const num = Math.max(0, Math.round(Number(value) || 0));
                    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },

                formatTimestamp() {
                    const mins = String(Math.floor(this.elapsedSeconds / 60)).padStart(2, '0');
                    const secs = String(this.elapsedSeconds % 60).padStart(2, '0');
                    return `${mins}:${secs}`;
                },

                addLog(level, tag, message) {
                    this.logs.unshift({
                        time: this.formatTimestamp(),
                        level,
                        tag,
                        message,
                    });
                },
            };
        }
    </script>
@endsection
