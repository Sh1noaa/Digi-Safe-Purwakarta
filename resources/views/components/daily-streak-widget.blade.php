@props(['streak'])
@php
    $isEn = app()->getLocale() === 'en';
    $isDark = ($currentTheme ?? 'light') === 'dark';
    $initial = $streak ?? \App\Services\DailyStreakService::getStatusForUser(Auth::user());
@endphp

<style>
    .streak-flame-dormant {
        filter: grayscale(100%) contrast(0.92) brightness(0.9);
        opacity: 0.58;
        transform: scale(0.92);
        transition: filter 950ms cubic-bezier(0.22, 1, 0.36, 1),
                    transform 850ms cubic-bezier(0.22, 1.35, 0.36, 1),
                    opacity 750ms ease;
    }
    .streak-flame-lit {
        filter: grayscale(0%) saturate(1.22) drop-shadow(0 8px 20px rgba(249, 115, 22, 0.5));
        opacity: 1;
        transform-origin: 50% 82%;
        animation: streakFlameBurn 1.8s ease-in-out infinite;
    }
    .streak-flame-freezing {
        filter: saturate(1.15) drop-shadow(0 6px 18px rgba(14, 165, 233, 0.52));
        opacity: 1;
        transform-origin: 50% 55%;
        animation: streakFreezeShiver 2.4s ease-in-out infinite;
    }
    .streak-flame-igniting {
        transform-origin: 50% 82%;
        animation: streakFlameIgniteMorph 1250ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }
    @keyframes streakFlameBurn {
        0%, 100% {
            transform: scale(1, 1) rotate(-1.5deg) translateY(0px);
            filter: saturate(1.18) brightness(1) drop-shadow(0 6px 16px rgba(249, 115, 22, 0.45));
        }
        25% {
            transform: scale(0.97, 1.06) rotate(1.8deg) translateY(-1.5px);
            filter: saturate(1.35) brightness(1.07) drop-shadow(0 10px 24px rgba(239, 68, 68, 0.62));
        }
        50% {
            transform: scale(1.04, 0.96) rotate(-1deg) translateY(0.5px);
            filter: saturate(1.25) brightness(1.03) drop-shadow(0 7px 18px rgba(249, 115, 22, 0.5));
        }
        75% {
            transform: scale(0.98, 1.07) rotate(2.2deg) translateY(-2px);
            filter: saturate(1.4) brightness(1.1) drop-shadow(0 11px 26px rgba(234, 88, 12, 0.68));
        }
    }
    @keyframes streakFreezeShiver {
        0%, 58%, 100% {
            transform: translateX(0px) rotate(0deg) scale(1);
            filter: saturate(1.1) brightness(1) drop-shadow(0 6px 16px rgba(14, 165, 233, 0.45));
        }
        62% {
            transform: translateX(-2px) rotate(-2.5deg) scale(0.98);
            filter: saturate(1.28) brightness(1.08) drop-shadow(0 6px 22px rgba(56, 189, 248, 0.72));
        }
        66% {
            transform: translateX(2px) rotate(2.5deg) scale(0.98);
        }
        70% {
            transform: translateX(-1.8px) rotate(-2deg) scale(0.99);
        }
        74% {
            transform: translateX(1.8px) rotate(2deg) scale(0.99);
        }
        78% {
            transform: translateX(-1px) rotate(-1deg) scale(1);
        }
        82% {
            transform: translateX(0px) rotate(0deg) scale(1);
        }
    }
    @keyframes streakFlameIgniteMorph {
        0% {
            filter: grayscale(100%) brightness(0.85) drop-shadow(0 0 0 rgba(239, 68, 68, 0));
            opacity: 0.55;
            transform: scale(0.82) rotate(-6deg);
        }
        38% {
            filter: grayscale(35%) saturate(1.5) hue-rotate(-10deg) drop-shadow(0 0 26px rgba(239, 68, 68, 0.78));
            opacity: 1;
            transform: scale(1.22) rotate(4deg);
        }
        72% {
            filter: grayscale(0%) saturate(1.32) hue-rotate(-4deg) drop-shadow(0 12px 28px rgba(249, 115, 22, 0.65));
            transform: scale(0.96) rotate(-2deg);
        }
        100% {
            filter: grayscale(0%) saturate(1.18) drop-shadow(0 8px 20px rgba(249, 115, 22, 0.45));
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }
    }
    @keyframes streakRingWave {
        0% {
            transform: scale(0.75);
            opacity: 0.9;
        }
        100% {
            transform: scale(1.75);
            opacity: 0;
        }
    }
    @keyframes streakEmberFloat {
        0% {
            transform: translateY(8px) scale(0.45);
            opacity: 0;
        }
        25% {
            opacity: 0.95;
        }
        100% {
            transform: translateY(-26px) scale(1.05);
            opacity: 0;
        }
    }
    @keyframes streakFrostDrift {
        0% {
            transform: translateY(-8px) rotate(0deg) scale(0.6);
            opacity: 0;
        }
        30% {
            opacity: 0.9;
        }
        100% {
            transform: translateY(20px) rotate(90deg) scale(1);
            opacity: 0;
        }
    }
    .streak-ignite-wave {
        animation: streakRingWave 1050ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .streak-ember-1 { animation: streakEmberFloat 1.7s ease-out infinite; }
    .streak-ember-2 { animation: streakEmberFloat 2.1s 0.45s ease-out infinite; }
    .streak-ember-3 { animation: streakEmberFloat 1.9s 0.9s ease-out infinite; }
    .streak-frost-1 { animation: streakFrostDrift 2.4s ease-in-out infinite; }
    .streak-frost-2 { animation: streakFrostDrift 2.8s 0.7s ease-in-out infinite; }
    .streak-frost-3 { animation: streakFrostDrift 2.2s 1.3s ease-in-out infinite; }
    @media (prefers-reduced-motion: reduce) {
        .streak-flame-lit, .streak-flame-freezing, .streak-flame-igniting,
        .streak-ember-1, .streak-ember-2, .streak-ember-3,
        .streak-frost-1, .streak-frost-2, .streak-frost-3 {
            animation: none !important;
        }
    }
</style>

<section
    id="tantangan-harian"
    x-data="{
        streak: @js($initial),
        selected: '',
        submitting: false,
        justIgnited: false,
        feedback: null,

        triggerIgniteAnimation() {
            this.justIgnited = false;
            this.$nextTick(() => {
                this.justIgnited = true;
                setTimeout(() => { this.justIgnited = false; }, 1450);
            });
        },

        async submitAnswer() {
            if (!this.selected || this.submitting || this.streak.completed_today) return;
            this.submitting = true;
            this.feedback = null;
            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                const res = await fetch('{{ route('streak.answer') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ option: this.selected })
                });
                const data = await res.json().catch(() => ({}));
                if (data.streak) {
                    this.streak = data.streak;
                }
                if (res.ok && data.ok) {
                    if (data.correct) {
                        this.triggerIgniteAnimation();
                    }
                    this.feedback = {
                        correct: !!data.correct,
                        recovered: !!data.recovered,
                        correct_option: data.correct_option || '',
                        explanation: data.explanation || '',
                        message: data.message || ''
                    };
                    if (!data.correct) {
                        this.selected = '';
                    }
                } else {
                    this.feedback = {
                        correct: false,
                        message: data.message || @js($isEn ? 'Unable to submit answer.' : 'Gagal mengirim jawaban.')
                    };
                }
            } catch (_) {
                this.feedback = {
                    correct: false,
                    message: @js($isEn ? 'Network error. Please try again.' : 'Gangguan jaringan. Silakan coba lagi.')
                };
            } finally {
                this.submitting = false;
            }
        }
    }"
    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8"
>
    <div class="max-w-6xl mx-auto">
        <div
            class="rounded-[28px] border p-5 sm:p-8 shadow-neutral transition-colors {{ $isDark ? 'bg-[#0F172A] border-slate-800' : 'bg-white border-gray-200/80' }}"
        >
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">

                <!-- KOLOM KIRI: STATUS API / FREEZE STREAK + ANIMASI TRANSISI ABU KE MERAH/ORANYE -->
                <div class="lg:col-span-5 space-y-5">
                    <div class="flex items-start gap-4">
                        <div
                            class="relative w-20 h-20 rounded-2xl flex items-center justify-center shrink-0 border transition-all duration-700 overflow-visible"
                            :class="streak.is_frozen
                                ? 'bg-sky-50 border-sky-200'
                                : ((streak.completed_today || streak.current_streak > 0 || justIgnited)
                                    ? 'bg-gradient-to-br from-amber-50 via-orange-50 to-red-50 border-orange-300 shadow-[0_10px_28px_rgba(249,115,22,0.18)]'
                                    : '{{ $isDark ? 'bg-slate-800 border-slate-700' : 'bg-slate-100 border-slate-200' }}')"
                        >
                            <!-- Gelombang Cahaya Saat Transisi Menyala -->
                            <template x-if="justIgnited">
                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    <span class="streak-ignite-wave absolute inset-0 rounded-2xl border-2 border-orange-500/70 bg-gradient-to-tr from-red-500/25 to-amber-400/25"></span>
                                </div>
                            </template>

                            <!-- Partikel Bara Api Berkelanjutan Saat Menyala -->
                            <template x-if="!streak.is_frozen && (streak.completed_today || streak.current_streak > 0 || justIgnited)">
                                <div class="pointer-events-none absolute inset-0 overflow-visible">
                                    <span class="streak-ember-1 absolute top-2 left-3 w-1.5 h-1.5 rounded-full bg-red-500/90"></span>
                                    <span class="streak-ember-2 absolute top-1 right-3.5 w-2 h-2 rounded-full bg-orange-400/90"></span>
                                    <span class="streak-ember-3 absolute top-3 right-6 w-1.5 h-1.5 rounded-full bg-amber-300/95"></span>
                                </div>
                            </template>

                            <!-- Partikel Kristal Es Berkelanjutan Saat Freeze (Kedinginan) -->
                            <template x-if="streak.is_frozen">
                                <div class="pointer-events-none absolute inset-0 overflow-visible">
                                    <span class="streak-frost-1 absolute top-1 left-3 w-2 h-2 rotate-45 rounded-[2px] bg-sky-300/90 shadow-xs"></span>
                                    <span class="streak-frost-2 absolute top-0 right-3.5 w-1.5 h-1.5 rotate-45 rounded-[2px] bg-cyan-200/95"></span>
                                    <span class="streak-frost-3 absolute top-2 left-8 w-1.5 h-1.5 rounded-full bg-sky-400/80"></span>
                                </div>
                            </template>

                            <img
                                :src="streak.is_frozen ? '{{ asset('images/streak-freeze.png') }}' : '{{ asset('images/streak-fire.png') }}'"
                                :alt="streak.is_frozen ? 'Streak Freeze' : 'Streak Fire'"
                                class="w-16 h-16 object-contain select-none relative z-10"
                                :class="justIgnited
                                    ? 'streak-flame-igniting'
                                    : (streak.is_frozen
                                        ? 'streak-flame-freezing'
                                        : ((streak.completed_today || streak.current_streak > 0)
                                            ? 'streak-flame-lit'
                                            : 'streak-flame-dormant'))"
                            >
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-heading font-extrabold uppercase tracking-wider transition-colors duration-500"
                                    :class="streak.is_frozen
                                        ? 'bg-sky-100 text-sky-800'
                                        : (streak.completed_today ? 'bg-gradient-to-r from-orange-500 to-red-600 text-white' : 'bg-slate-100 text-slate-700')"
                                    x-text="streak.is_frozen
                                        ? @js($isEn ? 'Frozen (24h+ Missed)' : 'Streak Membeku (24 Jam+)')
                                        : (streak.completed_today ? @js($isEn ? 'Flame Ignited Today' : 'Api Hari Ini Menyala') : @js($isEn ? 'Dormant Gray Flame' : 'Api Masih Abu (Belum Menyala)'))"
                                ></span>
                            </div>

                            <div class="mt-1.5 flex items-baseline gap-2">
                                <span
                                    class="font-heading font-extrabold text-3xl sm:text-4xl tracking-tight transition-transform duration-500 {{ $isDark ? 'text-white' : 'text-ink' }}"
                                    :class="justIgnited ? 'scale-110 text-orange-600' : ''"
                                    x-text="streak.current_streak"
                                ></span>
                                <span class="font-heading font-bold text-sm text-muted">
                                    {{ $isEn ? 'Day Streak' : 'Hari Beruntun' }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-muted leading-relaxed">
                                {{ $isEn ? 'Longest:' : 'Rekor terbaik:' }}
                                <strong class="{{ $isDark ? 'text-white' : 'text-ink' }}" x-text="streak.longest_streak"></strong>
                                {{ $isEn ? 'days' : 'hari' }}
                                &middot;
                                {{ $isEn ? 'Solved:' : 'Selesai:' }}
                                <strong class="{{ $isDark ? 'text-white' : 'text-ink' }}" x-text="streak.total_completed"></strong>
                            </p>
                        </div>
                    </div>

                    <!-- Indikator Freeze & Kesempatan 2x Pemulihan -->
                    <div
                        class="rounded-2xl border p-4 space-y-2"
                        :class="streak.is_frozen
                            ? 'bg-sky-50/90 border-sky-300 text-sky-950'
                            : '{{ $isDark ? 'bg-slate-800/70 border-slate-700 text-slate-200' : 'bg-[#F8FAFC] border-gray-200/80 text-ink' }}'"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-heading font-bold text-xs flex items-center gap-2">
                                <img src="{{ asset('images/streak-freeze.png') }}" alt="Freeze" class="w-5 h-5 object-contain">
                                <span>{{ $isEn ? 'Freeze Recovery Chances' : 'Kesempatan Pulihkan Freeze' }}</span>
                            </span>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-[11px] font-heading font-extrabold"
                                :class="streak.freeze_recovery_left > 0 ? 'bg-sky-600 text-white' : 'bg-red-600 text-white'"
                                x-text="streak.freeze_recovery_left + ' / ' + streak.max_freeze_recoveries + 'x'"
                            ></span>
                        </div>
                        <p class="text-[11px] leading-relaxed opacity-85">
                            <template x-if="streak.is_frozen">
                                <span>{{ $isEn
                                    ? 'You missed over 24 hours! Answer today\'s advanced A-E question correctly to thaw and restore your streak.'
                                    : 'Sudah lebih dari 24 jam tanpa menyalakan streak! Jawab benar tantangan lanjutan A-E di samping untuk mencairkan & memulihkan streak Anda.' }}</span>
                            </template>
                            <template x-if="!streak.is_frozen">
                                <span>{{ $isEn
                                    ? 'If you miss 24 hours, your flame freezes instead of breaking immediately. You get 2 recovery chances per streak cycle.'
                                    : 'Jika 24 jam tidak menyalakan streak, api akan membeku (freeze) dan Anda diberi 2x kesempatan memulihkannya sebelum padam.' }}</span>
                            </template>
                        </p>
                    </div>

                    <!-- Strip 7 Hari (Weekly Streak Tracker) -->
                    <div>
                        <span class="block text-[11px] font-heading font-bold uppercase tracking-wider text-muted mb-2.5">
                            {{ $isEn ? 'Weekly Streak Progress' : 'Progres Weekly Streak' }}
                        </span>
                        <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                            <template x-for="day in streak.week_days" :key="day.dow">
                                <div
                                    class="rounded-xl border py-2 px-1 flex flex-col items-center justify-center gap-1 text-center transition-all duration-500"
                                    :class="day.is_today
                                        ? '{{ $isDark ? 'border-orange-400 bg-slate-800' : 'border-orange-400 bg-orange-50/50 ring-1 ring-orange-400/30' }}'
                                        : '{{ $isDark ? 'border-slate-800 bg-slate-900/70' : 'border-gray-200/80 bg-[#F8FAFC]' }}'"
                                >
                                    <span class="text-[10px] font-heading font-bold" :class="day.is_today ? 'text-orange-600' : 'text-muted'" x-text="day.label"></span>
                                    <div class="w-7 h-7 flex items-center justify-center">
                                        <template x-if="day.state === 'done' || day.state === 'recovered'">
                                            <img
                                                src="{{ asset('images/streak-fire.png') }}"
                                                alt="Done"
                                                class="w-6 h-6 object-contain"
                                                :class="day.is_today && justIgnited ? 'streak-flame-igniting' : 'streak-flame-lit'"
                                            >
                                        </template>
                                        <template x-if="day.state === 'frozen'">
                                            <img src="{{ asset('images/streak-freeze.png') }}" alt="Frozen" class="w-6 h-6 object-contain streak-flame-freezing">
                                        </template>
                                        <template x-if="day.state !== 'done' && day.state !== 'recovered' && day.state !== 'frozen'">
                                            <img
                                                src="{{ asset('images/streak-fire.png') }}"
                                                alt="Dormant"
                                                class="w-5 h-5 object-contain streak-flame-dormant"
                                            >
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: PERTANYAAN TINGKAT LANJUT (OPSI A, B, C, D, E) -->
                <div class="lg:col-span-7 rounded-2xl border p-5 sm:p-6 space-y-4 {{ $isDark ? 'bg-slate-900/90 border-slate-800' : 'bg-[#F8FAFC] border-gray-200/80' }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-ink text-lime px-3 py-1 text-[10px] font-heading font-extrabold uppercase tracking-wider" x-text="streak.question.difficulty"></span>
                            <span class="rounded-full bg-sky/10 text-sky px-3 py-1 text-[10px] font-heading font-bold" x-text="streak.question.category"></span>
                        </div>
                        <span class="text-[11px] font-bold text-emerald-600" x-text="streak.is_frozen ? '+60 pts (Recovery)' : '+40 pts'"></span>
                    </div>

                    <h3 class="font-heading font-extrabold text-sm sm:text-base leading-relaxed {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="streak.question.prompt"></h3>

                    @guest
                        <div class="rounded-xl border p-4 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $isDark ? 'bg-slate-800 border-slate-700 text-slate-200' : 'bg-white border-gray-200 text-ink' }}">
                            <span>{{ $isEn ? 'Sign in to answer advanced daily challenges and ignite your gray flame into a burning streak.' : 'Masuk ke akun Anda untuk menjawab tantangan lanjutan dan menyalakan api abu-abu menjadi api streak menyala.' }}</span>
                            <a href="{{ route('login') }}" class="shrink-0 rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-5 py-2.5 text-center transition-colors">
                                {{ $isEn ? 'Sign In Now' : 'Masuk Sekarang' }}
                            </a>
                        </div>
                    @else
                        <!-- Pilihan Opsi A, B, C, D, E -->
                        <div class="space-y-2">
                            <template x-for="(optText, optKey) in streak.question.options" :key="optKey">
                                <button
                                    type="button"
                                    @click="if (!streak.completed_today) selected = optKey"
                                    :disabled="streak.completed_today || submitting"
                                    :class="selected === optKey
                                        ? 'border-sky bg-sky/10 ring-1 ring-sky/30 {{ $isDark ? 'text-white' : 'text-ink' }}'
                                        : '{{ $isDark ? 'bg-slate-800/80 border-slate-700 text-slate-200 hover:border-slate-500' : 'bg-white border-gray-200 text-ink hover:border-sky/50' }}'"
                                    class="w-full text-left rounded-xl border px-3.5 py-2.5 flex items-start gap-3 transition-all cursor-pointer disabled:opacity-65 disabled:cursor-not-allowed"
                                >
                                    <span
                                        class="w-6 h-6 rounded-lg font-heading font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                                        :class="selected === optKey ? 'bg-sky text-white' : '{{ $isDark ? 'bg-slate-700 text-slate-200' : 'bg-slate-100 text-ink' }}'"
                                        x-text="optKey"
                                    ></span>
                                    <span class="text-xs leading-relaxed" x-text="optText"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Umpan Balik & Analisis Teknis -->
                        <template x-if="feedback">
                            <div
                                class="rounded-xl border p-4 space-y-1.5 text-xs"
                                :class="feedback.correct ? 'bg-emerald-50 border-emerald-300 text-emerald-950' : 'bg-amber-50 border-amber-300 text-amber-950'"
                            >
                                <p class="font-heading font-extrabold" x-text="feedback.message"></p>
                                <p x-show="feedback.explanation" class="leading-relaxed opacity-90" x-text="feedback.explanation"></p>
                            </div>
                        </template>

                        <div class="pt-1 flex flex-wrap items-center justify-between gap-3">
                            <span class="text-[11px] text-muted" x-show="streak.completed_today">
                                {{ $isEn ? 'Today\'s flame is lit! Next advanced challenge unlocks tomorrow at 00:00 WIB.' : 'Api hari ini sudah menyala! Soal lanjutan baru terbuka besok pukul 00.00 WIB.' }}
                            </span>
                            <span class="text-[11px] text-muted" x-show="!streak.completed_today">
                                {{ $isEn ? 'Select 1 option (A-E) to ignite your gray flame.' : 'Pilih 1 opsi (A-E) untuk menyalakan api streak Anda.' }}
                            </span>

                            <button
                                type="button"
                                x-show="!streak.completed_today"
                                @click="submitAnswer()"
                                :disabled="!selected || submitting"
                                class="rounded-full font-heading font-extrabold text-xs px-6 py-2.5 min-h-[42px] inline-flex items-center gap-2 transition-colors cursor-pointer disabled:opacity-55 disabled:cursor-not-allowed"
                                :class="streak.is_frozen ? 'bg-sky hover:bg-ink text-white' : 'bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white shadow-xs'"
                            >
                                <span x-show="submitting" style="display: none;" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span x-text="streak.is_frozen ? @js($isEn ? 'Thaw & Recover Streak' : 'Cairkan & Pulihkan Streak') : @js($isEn ? 'Submit & Ignite Flame' : 'Jawab & Nyalakan Api')"></span>
                            </button>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</section>
