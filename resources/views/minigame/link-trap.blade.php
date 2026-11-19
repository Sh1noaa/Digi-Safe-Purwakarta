@extends('layouts.app')

@section('title', 'Link or Trap? - Minigame Digi-Safe Purwakarta')

@section('content')

    <section
        x-data="linkTrapGame()"
        class="min-h-screen bg-[#F7F8FC] pt-24 sm:pt-28 pb-20 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-4xl mx-auto">

            <!-- navigasi balik -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <a
                    href="{{ route('minigame.index') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-4 py-2 text-xs font-semibold text-ink hover:border-ink transition-colors"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    <span>{{ __('ui.game.back_to_hub') }}</span>
                </a>

                <div class="flex items-center gap-2">
                    @if (Auth::check() && Auth::user()->hasAdminAccess())
                        <a
                            href="{{ route('admin.minigame.index', ['game' => 'link-trap']) }}"
                            class="rounded-full bg-ink text-lime text-xs font-bold px-3.5 py-1.5 hover:bg-sky hover:text-white transition-colors"
                        >
                            {{ __('ui.game.manage_levels_admin') }}
                        </a>
                    @endif
                    <span class="rounded-full bg-lime px-3.5 py-1.5 text-xs font-bold text-ink">
                        Link or Trap? • {{ $levels->count() }} Level
                    </span>
                </div>
            </div>

            <!-- layar awal & pilih level -->
            <div x-show="state === 'menu'" class="rounded-[28px] bg-white border border-gray-100 p-7 sm:p-10 shadow-neutral text-center">
                <div class="w-14 h-14 rounded-2xl bg-lime/50 text-ink flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                    </svg>
                </div>

                <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-ink">
                    Link or Trap?
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-muted max-w-lg mx-auto leading-relaxed">
                    {{ __('ui.game.linktrap_intro') }}
                </p>

                <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-left">
                    @foreach ($levels as $lvl)
                        <button
                            type="button"
                            @click="startGame(@js($lvl->slug))"
                            class="rounded-2xl border border-gray-200 hover:border-sky bg-[#F8FAFC]/60 hover:bg-white p-5 transition-all hover:-translate-y-1 shadow-2xs cursor-pointer group flex flex-col justify-between"
                        >
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold uppercase {{ $lvl->badge_classes }}">
                                        {{ $lvl->badge_label }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-ink">
                                        <svg class="w-3.5 h-3.5 text-sky shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                                        <span>{{ $lvl->waktu_detik }}{{ __('ui.game.sec_short') }}</span>
                                    </span>
                                </div>
                                <h3 class="mt-3 font-heading font-extrabold text-base text-ink group-hover:text-sky transition-colors">
                                    {{ $lvl->nama_level }}
                                </h3>
                                <p class="mt-1 text-xs text-muted leading-relaxed">
                                    {{ $lvl->deskripsi }}
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-200/70 flex items-center justify-between text-[11px] font-bold">
                                <span class="text-emerald-600">{{ __('ui.game.base_combo', ['pts' => $lvl->poin_benar]) }}</span>
                                <span class="text-sky group-hover:translate-x-1 transition-transform">{{ __('ui.game.start') }} &rarr;</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- layar permainan aktif -->
            <div x-show="state === 'playing'" style="display: none;" class="space-y-5">

                <!-- bar status combo, timer, & skor sticky di HP -->
                <div class="sticky top-[68px] sm:top-[76px] z-30 space-y-2 bg-[#F7F8FC]/95 py-1.5 -mx-1 px-1 rounded-2xl">
                    <div class="rounded-2xl bg-white border border-gray-100 p-3.5 sm:p-4 flex flex-wrap items-center justify-between gap-3 sm:gap-4 shadow-sm">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <span class="rounded-full bg-[#DBEAFE] text-sky px-3 py-1 text-xs font-extrabold uppercase" x-text="activeLevelConfig.badge_label"></span>
                            <span class="text-xs font-semibold text-muted" x-text="'URL ' + (currentIndex + 1) + ' / ' + items.length"></span>
                            <span
                                class="rounded-full px-3 py-0.5 text-xs font-extrabold transition-all"
                                :class="multiplier > 1 ? 'bg-sky text-white' : 'bg-gray-100 text-muted'"
                                x-text="'Combo x' + multiplier"
                            ></span>
                        </div>

                        <div class="flex items-center gap-3.5 sm:gap-4">
                            <span
                                class="font-heading font-extrabold text-sm tabular-nums"
                                :class="timeLeft <= 3 ? 'text-red-600' : 'text-ink'"
                                x-text="timeLeft + ' {{ __('ui.game.sec_word') }}'"
                            ></span>
                            <span class="rounded-full bg-lime px-3.5 py-1 text-xs font-extrabold text-ink tabular-nums" x-text="'{{ __('ui.game.score_label') }}: ' + score"></span>
                        </div>
                    </div>

                    <!-- bar timer -->
                    <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                        <div
                            class="h-full transition-all duration-300 rounded-full"
                            :class="timeLeft <= 3 ? 'bg-red-500' : 'bg-sky'"
                            :style="'width: ' + ((timeLeft / maxTime) * 100) + '%'"
                        ></div>
                    </div>
                </div>

                <!-- card inspeksi url -->
                <div class="rounded-[24px] bg-white border border-gray-100 p-6 sm:p-8 shadow-neutral">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted block">{{ __('ui.game.url_context') }}</span>
                    <p class="mt-1 font-heading font-bold text-base text-ink" x-text="currentItem().context"></p>

                    <!-- kotak address bar browser -->
                    <div class="mt-5 rounded-2xl bg-[#F7F8FC] border border-gray-200 p-4 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-muted shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <circle cx="12" cy="12" r="9" />
                                <line x1="3" y1="12" x2="21" y2="12" />
                            </svg>
                        </span>
                        <code class="text-xs sm:text-sm font-mono font-bold text-ink break-all" x-text="currentItem().url"></code>
                    </div>

                    <!-- Mode Soal Pilihan Ganda dari Admin -->
                    <template x-if="currentItem().mode === 'mcq' && !answered">
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="letter in ['A', 'B', 'C', 'D']" :key="letter">
                                <button
                                    type="button"
                                    @click="choose(letter)"
                                    class="rounded-2xl bg-[#F8FAFC] hover:bg-sky hover:text-white border border-gray-200 p-3.5 text-left text-xs font-semibold text-ink flex items-start gap-2.5 transition-all cursor-pointer"
                                >
                                    <span class="w-6 h-6 rounded-lg bg-ink text-lime font-heading font-extrabold text-[11px] flex items-center justify-center shrink-0" x-text="letter"></span>
                                    <span class="leading-snug" x-text="currentItem().options[letter]"></span>
                                </button>
                            </template>
                        </div>
                    </template>

                    <!-- Mode Tombol Pilih Aman vs Trap -->
                    <template x-if="currentItem().mode !== 'mcq' && !answered">
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <button
                                type="button"
                                @click="choose('aman')"
                                class="rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-heading font-bold text-sm py-4 px-6 flex items-center justify-center gap-2 shadow-sm hover:-translate-y-0.5 transition-all cursor-pointer"
                            >
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                </svg>
                                <span>{{ __('ui.game.safe_btn') }}</span>
                            </button>

                            <button
                                type="button"
                                @click="choose('trap')"
                                class="rounded-2xl bg-red-600 hover:bg-red-700 text-white font-heading font-bold text-sm py-4 px-6 flex items-center justify-center gap-2 shadow-sm hover:-translate-y-0.5 transition-all cursor-pointer"
                            >
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                    <line x1="12" y1="9" x2="12" y2="13" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                </svg>
                                <span>{{ __('ui.game.trap_btn') }}</span>
                            </button>
                        </div>
                    </template>

                    <!-- penjelasan setelah jawab -->
                    <div
                        x-show="answered"
                        style="display: none;"
                        class="mt-5 rounded-2xl p-4 border"
                        :class="isCorrect ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-red-50 border-red-200 text-red-900'"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-heading font-bold text-xs uppercase tracking-wider" x-text="feedbackHeader"></span>
                            <span class="text-xs font-extrabold" x-text="lastPointsText"></span>
                        </div>
                        <p class="mt-1.5 text-xs leading-relaxed" x-text="currentItem().reason"></p>

                        <div class="mt-4 flex justify-end">
                            <button
                                type="button"
                                @click="nextUrl()"
                                class="inline-flex items-center gap-2 rounded-full bg-ink text-white text-xs font-bold px-5 py-2.5 cursor-pointer"
                            >
                                <span x-text="currentIndex + 1 < items.length ? '{{ __('ui.game.next_url') }}' : '{{ __('ui.game.view_final_score') }}'"></span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- layar selesai -->
            <div x-show="state === 'finished'" style="display: none;" class="rounded-[28px] bg-white border border-gray-100 p-8 sm:p-10 shadow-neutral text-center">
                <div class="w-16 h-16 rounded-full bg-lime text-ink flex items-center justify-center mx-auto mb-4 font-heading font-extrabold text-xl" x-text="'x' + maxCombo"></div>
                <h2 class="font-heading font-extrabold text-2xl text-ink">{{ __('ui.game.url_finished') }}</h2>
                <p class="mt-1 text-xs text-muted" x-text="saveStatus"></p>

                <div class="mt-6 grid grid-cols-3 gap-3 max-w-md mx-auto">
                    <div class="rounded-2xl bg-[#F7F8FC] p-4">
                        <span class="text-[10px] font-semibold text-muted block">{{ __('ui.game.total_score') }}</span>
                        <span class="font-heading font-extrabold text-xl text-ink mt-1 block" x-text="score"></span>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-4">
                        <span class="text-[10px] font-semibold text-emerald-700 block">{{ __('ui.game.correct') }}</span>
                        <span class="font-heading font-extrabold text-xl text-emerald-700 mt-1 block" x-text="correctCount"></span>
                    </div>
                    <div class="rounded-2xl bg-red-50 p-4">
                        <span class="text-[10px] font-semibold text-red-600 block">{{ __('ui.game.wrong') }}</span>
                        <span class="font-heading font-extrabold text-xl text-red-600 mt-1 block" x-text="wrongCount"></span>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <button
                        type="button"
                        @click="state = 'menu'"
                        class="rounded-full bg-lime text-ink font-bold text-xs px-6 py-3 cursor-pointer"
                    >
                        {{ __('ui.game.choose_other_level') }}
                    </button>
                    <a
                        href="{{ route('minigame.index') }}"
                        class="rounded-full bg-ink text-white font-bold text-xs px-6 py-3"
                    >
                        {{ __('ui.game.back_to_dashboard') }}
                    </a>
                </div>
            </div>

        </div>
    </section>

    <script>
        function linkTrapGame() {
            const isEn = @js(app()->getLocale() === 'en');
            const levelsMap = @js($levels->keyBy('slug'));
            const dbQuestions = @js($dbSoals);

            const urlListId = [
                {
                    context: 'Halaman Login Akun Google',
                    url: 'https://accounts.goog1e.com/signin/v2',
                    type: 'trap',
                    reason: "Domain 'goog1e.com' adalah palsu karena menggunakan angka 1 untuk menggantikan huruf l (typosquatting)."
                },
                {
                    context: 'Portal Resmi Pemerintah Kabupaten Purwakarta',
                    url: 'https://purwakartakab.go.id/berita',
                    type: 'aman',
                    reason: "Domain 'purwakartakab.go.id' adalah domain resmi pemerintah daerah di Indonesia (.go.id)."
                },
                {
                    context: 'Tautan Klaim Subsidi Listrik',
                    url: 'http://pln.co.id.klaim-token-gratis.xyz/login',
                    type: 'trap',
                    reason: "Jebakan Subdomain! Domain utamanya sebenarnya adalah 'klaim-token-gratis.xyz', bukan pln.co.id."
                },
                {
                    context: 'Portal Literasi Digital Nasional',
                    url: 'https://literasidigital.id/',
                    type: 'aman',
                    reason: "Domain 'literasidigital.id' adalah portal resmi literasi digital nasional yang aman diakses."
                },
                {
                    context: 'Verifikasi Mobile Banking BCA',
                    url: 'https://klikbca-update-nasabah.com/auth',
                    type: 'trap',
                    reason: "Trap! Domain resmi BCA adalah klikbca.com atau bca.co.id, bukan domain tambahan dengan tanda hubung."
                },
                {
                    context: 'Komunitas Pemeriksa Fakta MAFINDO',
                    url: 'https://mafindo.or.id/',
                    type: 'aman',
                    reason: "Domain 'mafindo.or.id' adalah domain organisasi resmi Masyarakat Anti Fitnah Indonesia."
                },
                {
                    context: 'Halaman Login WhatsApp Web',
                    url: 'https://web.whatsaap.com/',
                    type: 'trap',
                    reason: "Trap! Perhatikan ejaan 'whatsaap.com' (menggunakan dua huruf 'a' dan satu 'p')."
                },
                {
                    context: 'Artikel Edukasi Kemenkeu DJKN',
                    url: 'https://www.djkn.kemenkeu.go.id/artikel/baca/15818/Bijak-Bermedia-Sosial.html',
                    type: 'aman',
                    reason: "Aman! Subdomain resmi di bawah 'kemenkeu.go.id' milik Kementerian Keuangan RI."
                }
            ];

            const urlListEn = [
                {
                    context: 'Google Account Sign-In Page',
                    url: 'https://accounts.goog1e.com/signin/v2',
                    type: 'trap',
                    reason: "Fake domain 'goog1e.com' replaces the letter 'l' with the number '1' (typosquatting attack)."
                },
                {
                    context: 'Purwakarta Regency Official Government Portal',
                    url: 'https://purwakartakab.go.id/berita',
                    type: 'aman',
                    reason: "'purwakartakab.go.id' is an authentic Indonesian regional government domain (.go.id)."
                },
                {
                    context: 'Electricity Subsidy Claim Link',
                    url: 'http://pln.co.id.klaim-token-gratis.xyz/login',
                    type: 'trap',
                    reason: "Subdomain Trap! The actual root domain is 'klaim-token-gratis.xyz', not pln.co.id."
                },
                {
                    context: 'National Digital Literacy Portal',
                    url: 'https://literasidigital.id/',
                    type: 'aman',
                    reason: "'literasidigital.id' is the official national digital literacy portal and safe to visit."
                },
                {
                    context: 'BCA Mobile Banking Verification',
                    url: 'https://klikbca-update-nasabah.com/auth',
                    type: 'trap',
                    reason: "Trap! Official BCA domains are klikbca.com or bca.co.id, not hyphenated lookalike domains."
                },
                {
                    context: 'MAFINDO Fact-Checking Community',
                    url: 'https://mafindo.or.id/',
                    type: 'aman',
                    reason: "'mafindo.or.id' is the official organization domain of Indonesia's Anti-Slander Society."
                },
                {
                    context: 'WhatsApp Web Login Page',
                    url: 'https://web.whatsaap.com/',
                    type: 'trap',
                    reason: "Trap! Look closely at the spelling 'whatsaap.com' (double 'a' and single 'p')."
                },
                {
                    context: 'Ministry of Finance Educational Article',
                    url: 'https://www.djkn.kemenkeu.go.id/artikel/baca/15818/Bijak-Bermedia-Sosial.html',
                    type: 'aman',
                    reason: "Safe! Authentic subdomain under 'kemenkeu.go.id' belonging to Indonesia’s Ministry of Finance."
                }
            ];

            const urlList = isEn ? urlListEn : urlListId;

            return {
                state: 'menu',
                level: 'easy',
                activeLevelConfig: {
                    badge_label: 'LEVEL 1 • EASY',
                    waktu_detik: 10,
                    poin_benar: 10,
                    penalti_salah: 5
                },
                items: urlList,
                currentIndex: 0,
                score: 0,
                combo: 0,
                multiplier: 1,
                maxCombo: 1,
                correctCount: 0,
                wrongCount: 0,
                maxTime: 10,
                timeLeft: 10,
                timerId: null,
                answered: false,
                isCorrect: false,
                feedbackHeader: '',
                lastPointsText: '',
                saveStatus: '',

                startGame(selectedLevel = 'easy') {
                    this.level = selectedLevel;
                    const cfg = levelsMap[selectedLevel] || {
                        badge_label: 'LEVEL • ' + selectedLevel.toUpperCase(),
                        waktu_detik: 10,
                        poin_benar: 10,
                        penalti_salah: 5
                    };
                    this.activeLevelConfig = cfg;
                    this.maxTime = parseInt(cfg.waktu_detik || 10, 10);

                    const customFromDb = (dbQuestions || [])
                        .filter(q => q.level === selectedLevel)
                        .map(q => ({
                            mode: 'mcq',
                            context: (isEn ? 'URL Inspection Case (' : 'Kasus Inspeksi Tautan (') + cfg.badge_label + ')',
                            url: q.pertanyaan,
                            options: q.opsi,
                            type: q.kunci,
                            reason: (isEn ? 'Correct answer is (' : 'Jawaban tepat adalah (') + q.kunci + '): ' + q.kunci_teks
                        }));

                    this.items = [...customFromDb, ...urlList];
                    this.currentIndex = 0;
                    this.score = 0;
                    this.combo = 0;
                    this.multiplier = 1;
                    this.maxCombo = 1;
                    this.correctCount = 0;
                    this.wrongCount = 0;
                    this.state = 'playing';
                    this.startUrlTimer();
                },

                currentItem() {
                    return this.items[this.currentIndex] || {};
                },

                startUrlTimer() {
                    this.answered = false;
                    this.timeLeft = this.maxTime;
                    if (this.timerId) clearInterval(this.timerId);

                    this.timerId = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            clearInterval(this.timerId);
                            this.handleTimeOut();
                        }
                    }, 1000);
                },

                handleTimeOut() {
                    if (this.answered) return;
                    this.answered = true;
                    this.isCorrect = false;
                    this.wrongCount++;
                    this.combo = 0;
                    this.multiplier = 1;
                    this.feedbackHeader = isEn ? 'Time Up! Combo Reset' : 'Waktu Habis! Combo Reset';
                    this.lastPointsText = '0 pts';
                },

                choose(choice) {
                    if (this.answered) return;
                    if (this.timerId) clearInterval(this.timerId);
                    this.answered = true;

                    const basePts = parseInt(this.activeLevelConfig.poin_benar || 10, 10);

                    if (choice === this.currentItem().type) {
                        this.isCorrect = true;
                        this.correctCount++;
                        this.combo++;
                        this.multiplier = Math.min(3, this.combo);
                        if (this.multiplier > this.maxCombo) {
                            this.maxCombo = this.multiplier;
                        }
                        const earned = basePts * this.multiplier;
                        this.score += earned;
                        this.feedbackHeader = isEn ? 'Accurate Analysis!' : 'Analisis Tepat!';
                        this.lastPointsText = '+' + earned + ' pts (x' + this.multiplier + ')';
                    } else {
                        this.isCorrect = false;
                        this.wrongCount++;
                        this.combo = 0;
                        this.multiplier = 1;
                        this.feedbackHeader = isEn ? 'Trapped! Combo Reset' : 'Terjebak! Combo Reset';
                        this.lastPointsText = '0 pts';
                    }
                },

                nextUrl() {
                    if (this.currentIndex + 1 < this.items.length) {
                        this.currentIndex++;
                        this.startUrlTimer();
                    } else {
                        this.finishGame();
                    }
                },

                finishGame() {
                    if (this.timerId) clearInterval(this.timerId);
                    this.state = 'finished';
                    this.saveStatus = isEn ? 'Saving score to server...' : 'Menyimpan skor ke server...';

                    fetch('{{ route('minigame.save-score') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            game_type: 'link-trap',
                            skor: this.score,
                            benar: this.correctCount,
                            salah: this.wrongCount,
                            avg_time_sec: 7.5
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.saveStatus = data.message || (isEn ? 'Completed!' : 'Selesai!');
                    })
                    .catch(() => {
                        this.saveStatus = isEn ? 'Score calculated.' : 'Skor selesai dihitung.';
                    });
                }
            };
        }
    </script>

@endsection
