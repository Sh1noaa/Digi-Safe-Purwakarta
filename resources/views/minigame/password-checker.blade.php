@extends('layouts.app')

@section('title', 'Password Strength Checker | Digi-Safe Purwakarta')

@section('content')

    @php
        $isEn = app()->getLocale() === 'en';
        $isDark = ($currentTheme ?? 'light') === 'dark';
    @endphp

    <section x-data="passwordCheckerLab(@js($isEn))"
        class="min-h-screen {{ $isDark ? 'bg-[#0B1120]' : 'bg-[#F8FAFC]' }} pt-24 sm:pt-28 pb-24 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- bar kembali -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('minigame.index') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-4 py-2 text-xs font-heading font-bold text-ink hover:border-sky hover:text-sky shadow-2xs transition-colors">
                    <span>&larr;</span>
                    <span>{{ $isEn ? 'Back to Minigame Hub' : 'Kembali ke Hub Minigame' }}</span>
                </a>

                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-3.5 py-1.5 text-[11px] font-bold text-emerald-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ $isEn ? 'Processed 100% locally in your browser' : 'Diproses 100% di browser, tidak disimpan ke server' }}</span>
                </span>
            </div>

            <!-- kartu utama input & meter kekuatan bergaya thumbnail minigame dengan tema teal-langit & awan pop-out -->
            <div class="group rounded-[28px] bg-white border border-gray-200/80 shadow-neutral overflow-visible">
                <div class="relative h-44 sm:h-48 rounded-t-[28px] overflow-visible"
                    style="background: linear-gradient(135deg, #0F766E 0%, #0284C7 100%);">
                    <!-- pola aura perisai di balik awan -->
                    <svg class="absolute inset-0 w-full h-full rounded-t-[28px] overflow-hidden pointer-events-none"
                        viewBox="0 0 600 190" preserveAspectRatio="none">
                        <circle cx="485" cy="48" r="78" stroke="rgba(198,244,50,0.38)" stroke-width="1.5"
                            stroke-dasharray="6 6" fill="none" />
                        <circle cx="485" cy="48" r="44" fill="rgba(255,255,255,0.12)" />
                        <circle cx="90" cy="42" r="2.5" fill="rgba(255,255,255,0.65)" />
                        <circle cx="155" cy="24" r="2" fill="#C6F432" />
                        <circle cx="240" cy="56" r="2" fill="rgba(255,255,255,0.45)" />
                    </svg>

                    <!-- awan putih mengalir di bagian bawah thumbnail -->
                    <svg class="absolute inset-x-0 -bottom-1 w-full h-28 sm:h-32 pointer-events-none" viewBox="0 0 600 125"
                        preserveAspectRatio="none">
                        <path
                            d="M0,45 Q45,25 85,55 Q135,38 175,68 Q230,48 275,78 Q335,55 385,84 Q445,62 505,86 Q555,70 600,88 L600,125 L0,125 Z"
                            fill="#FFFFFF" />
                        <path d="M55,75 Q95,62 125,84 M225,88 Q265,74 295,94 M415,92 Q455,78 485,98" stroke="#0EA5E9"
                            stroke-width="2" stroke-linecap="round" fill="none" opacity="0.45" />
                    </svg>

                    <!-- teks judul di kiri thumbnail (di atas awan) -->
                    <div class="relative z-10 h-full px-6 sm:px-8 pt-6 flex flex-col justify-start max-w-xl">
                        <h1
                            class="font-heading font-extrabold text-2xl sm:text-3xl text-white tracking-tight drop-shadow-xs">
                            Password Strength Checker
                        </h1>
                        <p class="mt-1.5 text-xs sm:text-sm text-white/90 leading-relaxed max-w-md">
                            {{ $isEn
        ? 'Type your own password to inspect its real strength, spot weak patterns, and generate stronger memorable variations.'
        : 'Ketik kata sandi pilihanmu sendiri untuk memeriksa kekuatannya secara langsung dan mendapatkan variasi sandi yang jauh lebih kuat.' }}
                        </p>
                    </div>

                    <!-- ilustrasi gembok & kunci siber pop-out menembus batas kanan-atas kartu -->
                    <div
                        class="hidden sm:block absolute -top-10 right-6 w-32 h-32 pointer-events-none group-hover:rotate-6 group-hover:scale-105 transition-transform duration-300 z-20">
                        <svg viewBox="0 0 130 125" class="w-full h-full drop-shadow-lg">
                            <g transform="rotate(10 65 62)">
                                <path d="M40 50 V33 C40 16, 90 16, 90 33 V50" fill="none" stroke="#F8FAFC" stroke-width="11"
                                    stroke-linecap="round" />
                                <path d="M40 50 V33 C40 16, 90 16, 90 33 V50" fill="none" stroke="#0F766E" stroke-width="2"
                                    stroke-linecap="round" />
                                <rect x="27" y="47" width="76" height="58" rx="16" fill="#38BDF8" stroke="#0F172A"
                                    stroke-width="2.5" />
                                <rect x="35" y="55" width="60" height="42" rx="10" fill="#0F766E" />
                                <circle cx="65" cy="72" r="9" fill="#C6F432" stroke="#0F172A" stroke-width="2" />
                                <path d="M62 78 L59 90 H71 L68 78 Z" fill="#C6F432" stroke="#0F172A" stroke-width="1.6"
                                    stroke-linejoin="round" />
                                <circle cx="105" cy="30" r="7" fill="#C6F432" stroke="#0F172A" stroke-width="2" />
                                <path d="M105 26 V34 M101 30 H109" stroke="#0F172A" stroke-width="1.8"
                                    stroke-linecap="round" />
                            </g>
                        </svg>
                    </div>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <!-- input password -->
                    <div>
                        <label for="pw-checker-input" class="block text-xs font-heading font-bold text-ink mb-2">
                            {{ $isEn ? 'Enter a password to test & strengthen' : 'Masukkan kata sandi yang ingin diuji & diperkuat' }}
                        </label>
                        <div class="relative">
                            <input id="pw-checker-input" :type="show ? 'text' : 'password'" x-model="password"
                                placeholder="{{ $isEn ? 'Type your password here...' : 'Ketik kata sandi kamu di sini...' }}"
                                autocomplete="off"
                                class="w-full rounded-2xl bg-[#F8FAFC] border border-gray-200 focus:border-sky focus:bg-white pl-4 pr-32 py-3.5 text-sm sm:text-base font-mono text-ink placeholder:font-sans placeholder:text-gray-400 focus:outline-none transition-colors">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                                <button type="button" x-show="password.length > 0" style="display: none;"
                                    @click="password = ''" aria-label="{{ $isEn ? 'Clear password' : 'Hapus kata sandi' }}"
                                    class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-muted hover:text-ink flex items-center justify-center text-xs font-bold transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round">
                                        <path d="M18 6L6 18M6 6l12 12" />
                                    </svg>
                                </button>
                                <button type="button" @click="show = !show"
                                    class="rounded-full bg-white border border-gray-200 px-3 py-1.5 text-[11px] font-bold text-ink hover:border-sky cursor-pointer">
                                    <span x-text="show ? (isEn ? 'Hide' : 'Sembunyi') : (isEn ? 'Show' : 'Lihat')"></span>
                                </button>
                            </div>
                        </div>

                        <p class="mt-2 text-[11px] text-muted">
                            {{ $isEn
        ? 'Start typing above to see real-time strength checks and upgraded password recommendations.'
        : 'Mulai ketik kata sandi di kolom atas untuk melihat hasil analisis keamanan dan rekomendasi sandi secara langsung.' }}
                        </p>
                    </div>

                    <!-- progress bar & estimasi waktu bobol -->
                    <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/75 p-5 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xs font-bold text-muted uppercase tracking-wider">
                                    {{ $isEn ? 'Strength Status:' : 'Tingkat Kekuatan:' }}
                                </span>
                                <span :class="analysis.badgeClass"
                                    class="rounded-full px-3 py-1 text-xs font-heading font-extrabold"
                                    x-text="analysis.label"></span>
                            </div>
                            <span class="text-xs font-semibold text-ink">
                                {{ $isEn ? 'Est. crack time:' : 'Estimasi retas:' }}
                                <strong class="text-sky" x-text="analysis.crackTime"></strong>
                            </span>
                        </div>

                        <div class="w-full h-3 rounded-full bg-gray-200 overflow-hidden">
                            <div :class="analysis.barClass" :style="'width: ' + analysis.score + '%'"
                                class="h-full rounded-full transition-all duration-300"></div>
                        </div>

                        <!-- checklist kriteria -->
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-1 text-[11px]">
                            <template x-for="rule in analysis.checks" :key="rule.key">
                                <div :class="rule.ok ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-white border-gray-200 text-muted'"
                                    class="rounded-xl border px-3 py-2 flex items-center gap-2 font-semibold">
                                    <span x-text="rule.ok ? '✓' : '○'"
                                        :class="rule.ok ? 'text-emerald-600 font-bold' : ''"></span>
                                    <span x-text="rule.label" class="truncate"></span>
                                </div>
                            </template>
                        </div>

                        <template x-if="analysis.warning">
                            <p class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2"
                                x-text="analysis.warning"></p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- panel saran upgrade password cerdas berbasis kata yang diketik user -->
            <div x-show="password.trim().length > 0" x-transition
                class="rounded-[28px] bg-white border border-gray-200/80 shadow-neutral p-6 sm:p-8 space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span
                            class="inline-block rounded-full bg-[#DBEAFE] text-sky px-3 py-0.5 text-[10px] font-extrabold uppercase tracking-wider mb-1.5">
                            {{ $isEn ? 'SMART UPGRADE SUGGESTIONS' : 'SARAN PENINGKATAN SANDI' }}
                        </span>
                        <h2 class="font-heading font-extrabold text-lg sm:text-xl text-ink">
                            {{ $isEn ? 'Stronger versions based on what you typed' : 'Rekomendasi kata sandi lebih aman dari kata yang kamu tulis' }}
                        </h2>
                        <p class="text-xs text-muted mt-0.5">
                            {{ $isEn
        ? 'Still easy to remember, transformed with mixed case, smart numbers, and strong symbols.'
        : 'Tetap mudah kamu ingat karena berasal dari kata dasarmu, namun sudah diperkuat dengan huruf kapital, substitusi angka, dan simbol.' }}
                        </p>
                    </div>

                    <button type="button" @click="seed++"
                        class="inline-flex items-center gap-2 rounded-full bg-ink hover:bg-sky text-white text-xs font-heading font-bold px-4 py-2.5 transition-colors cursor-pointer">
                        <span>↻</span>
                        <span>{{ $isEn ? 'Shuffle Variations' : 'Acak Variasi Baru' }}</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <template x-for="(item, idx) in suggestions" :key="idx">
                        <div
                            class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 hover:border-sky/50 p-4 flex flex-col justify-between gap-3 transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky"
                                    x-text="item.tag"></span>
                                <span
                                    class="rounded-full bg-emerald-100 text-emerald-800 px-2.5 py-0.5 text-[10px] font-bold"
                                    x-text="item.strength"></span>
                            </div>

                            <div class="font-mono font-bold text-base sm:text-lg text-ink break-all bg-white border border-gray-200/70 rounded-xl px-3.5 py-2.5 select-all"
                                x-text="item.value"></div>

                            <div class="flex items-center justify-between gap-2">
                                <button type="button" @click="password = item.value"
                                    class="text-[11px] font-bold text-sky hover:underline cursor-pointer">
                                    {{ $isEn ? 'Test in checker' : 'Uji saran ini' }}
                                </button>
                                <button type="button" @click="copySuggestion(item.value, idx)"
                                    :class="copiedIdx === idx ? 'bg-lime text-ink' : 'bg-ink text-white hover:bg-sky'"
                                    class="rounded-full px-3.5 py-1.5 text-[11px] font-heading font-bold transition-colors cursor-pointer"
                                    x-text="copiedIdx === idx ? (isEn ? 'Copied ✓' : 'Tersalin ✓') : (isEn ? 'Copy' : 'Salin')"></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </section>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('passwordCheckerLab', (isEn) => ({
                isEn,
                password: '',
                show: true,
                seed: 0,
                copiedIdx: null,

                // logika password strength checker digisafe mantap
                get analysis() {
                    const p = this.password || '';
                    const hasLen = p.length >= 12;
                    const hasUpper = /[A-Z]/.test(p);
                    const hasLower = /[a-z]/.test(p);
                    const hasNum = /[0-9]/.test(p);
                    const hasSym = /[^A-Za-z0-9]/.test(p);

                    let score = 0;
                    if (p.length >= 6) score += 15;
                    if (p.length >= 9) score += 15;
                    if (hasLen) score += 15;
                    if (hasUpper) score += 15;
                    if (hasLower) score += 10;
                    if (hasNum) score += 15;
                    if (hasSym) score += 15;

                    const lowerP = p.toLowerCase();
                    let warning = '';
                    if (p.length > 0 && /(123|234|345|456|qwerty|password|admin|sayang)/.test(lowerP)) {
                        score = Math.max(10, score - 25);
                        warning = isEn
                            ? 'Contains predictable numbers or common dictionary words!'
                            : 'Mengandung urutan angka/kata umum yang mudah ditebak oleh brute-force!';
                    } else if (p.length > 0 && p.length < 8) {
                        warning = isEn
                            ? 'Too short! Aim for at least 12 characters.'
                            : 'Terlalu pendek! Minimal gunakan 12 karakter agar aman.';
                    }

                    score = Math.min(100, score);

                    let label, badgeClass, barClass, crackTime;
                    if (p.length === 0) {
                        label = isEn ? 'Waiting input' : 'Belum diisi';
                        badgeClass = 'bg-gray-100 text-muted';
                        barClass = 'bg-gray-300';
                        crackTime = '-';
                    } else if (score < 35) {
                        label = isEn ? 'Very Weak' : 'Sangat Lemah';
                        badgeClass = 'bg-red-100 text-red-700';
                        barClass = 'bg-red-500';
                        crackTime = isEn ? 'Under 1 second' : 'Kurang dari 1 detik';
                    } else if (score < 60) {
                        label = isEn ? 'Weak' : 'Lemah';
                        badgeClass = 'bg-amber-100 text-amber-800';
                        barClass = 'bg-amber-500';
                        crackTime = isEn ? 'A few minutes' : 'Beberapa menit';
                    } else if (score < 85) {
                        label = isEn ? 'Moderate' : 'Cukup Kuat';
                        badgeClass = 'bg-sky/15 text-sky';
                        barClass = 'bg-sky';
                        crackTime = isEn ? 'Several months' : 'Beberapa bulan';
                    } else {
                        label = isEn ? 'Cyber Fortress' : 'Benteng Siber';
                        badgeClass = 'bg-emerald-100 text-emerald-800';
                        barClass = 'bg-emerald-500';
                        crackTime = isEn ? 'Centuries+' : 'Berabad-abad+';
                    }

                    return {
                        score,
                        label,
                        badgeClass,
                        barClass,
                        crackTime,
                        warning,
                        checks: [
                            { key: 'len', ok: hasLen, label: isEn ? '12+ Chars' : '12+ Karakter' },
                            { key: 'up', ok: hasUpper, label: isEn ? 'Uppercase' : 'Huruf Besar' },
                            { key: 'low', ok: hasLower, label: isEn ? 'Lowercase' : 'Huruf Kecil' },
                            { key: 'num', ok: hasNum, label: isEn ? 'Numbers' : 'Angka' },
                            { key: 'sym', ok: hasSym, label: isEn ? 'Symbols' : 'Simbol (~#!)' },
                        ],
                    };
                },

                get suggestions() {
                    const raw = (this.password || '').trim();
                    if (!raw) return [];

                    const alphaOnly = raw.replace(/[^a-zA-Z]/g, '') || 'DigiSafe';
                    const digitsOnly = raw.replace(/[^0-9]/g, '');
                    const symbols = ['~#', '!@', '#$', '*~', '&!', '%#'];
                    const s1 = symbols[this.seed % symbols.length];
                    const s2 = symbols[(this.seed + 2) % symbols.length];
                    const numTail = (digitsOnly && digitsOnly !== '123' && digitsOnly !== '1234')
                        ? digitsOnly.slice(-2)
                        : String(47 + ((this.seed * 19) % 51));

                    const leetSmart = (word, aggressive = false) => {
                        const map = { a: '4', e: '3', i: '1', o: '0', s: '5' };
                        return word.split('').map((ch, idx) => {
                            const lc = ch.toLowerCase();
                            if (idx === 0) return lc.toUpperCase();
                            if (map[lc] && (aggressive || idx % 2 === 1 || lc === 'a' || lc === 'e')) {
                                return map[lc];
                            }
                            if (idx === word.length - 1 && aggressive) return ch.toUpperCase();
                            return lc;
                        }).join('');
                    };

                    const v1Base = leetSmart(alphaOnly, false);
                    const v2Base = leetSmart(alphaOnly, true);
                    const prefixSym = ['!', '#', '~', '@'][this.seed % 4];
                    const tagSuffix = isEn ? ['Saf3', 'Sh1eld', 'K3y', 'V4ult'][this.seed % 4] : ['Am4n', 'Ku4t', 'Pwk', 'Pr1v'][this.seed % 4];

                    const v1 = `${v1Base}${s1}${digitsOnly && digitsOnly.length <= 2 ? digitsOnly : ''}`.length >= 10
                        ? `${v1Base}${s1}${numTail}`
                        : `${v1Base}${s1}${numTail}!`;

                    const v2 = `${prefixSym}${v2Base}_${tagSuffix}#${numTail}`;
                    const v3 = `${v1Base}-${numTail}${s2}${tagSuffix}`;
                    const v4 = `${s2[0]}${v2Base}${numTail}${s1}X`;

                    return [
                        {
                            tag: isEn ? 'Direct Smart Upgrade' : 'Upgrade Langsung (Mirip Asli)',
                            strength: 'Strong • 90%',
                            value: v1.length >= 12 ? v1 : `${v1}${s2[1]}`,
                        },
                        {
                            tag: isEn ? 'Extended Vault Phrase' : 'Frasa Panjang Anti-Retas',
                            strength: 'Ultra • 100%',
                            value: v2,
                        },
                        {
                            tag: isEn ? 'Hybrid Passphrase' : 'Kombinasi Mudah Diingat',
                            strength: 'Ultra • 100%',
                            value: v3,
                        },
                        {
                            tag: isEn ? 'Compact Symbol Guard' : 'Kombinasi Simbol Padat',
                            strength: 'Strong • 95%',
                            value: v4.length >= 12 ? v4 : `${v4}!9`,
                        },
                    ];
                },

                async copySuggestion(val, idx) {
                    try {
                        await navigator.clipboard.writeText(val);
                    } catch (_) {
                        // Fallback silent copy
                    }
                    this.copiedIdx = idx;
                    setTimeout(() => {
                        if (this.copiedIdx === idx) this.copiedIdx = null;
                    }, 1800);
                },
            }));
        });
    </script>

@endsection