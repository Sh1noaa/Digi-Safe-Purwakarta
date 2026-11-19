@extends('layouts.app')

@section('title', 'Cyber Defender - Minigame Digi-Safe Purwakarta')

@section('content')

    <section
        x-data="cyberDefenderGame()"
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
                            href="{{ route('admin.minigame.index', ['game' => 'cyber-defender']) }}"
                            class="rounded-full bg-ink text-lime text-xs font-bold px-3.5 py-1.5 hover:bg-sky hover:text-white transition-colors"
                        >
                            {{ __('ui.game.manage_levels_admin') }}
                        </a>
                    @endif
                    <span class="rounded-full bg-[#DBEAFE] text-sky text-xs font-bold px-3.5 py-1.5">
                        Cyber Defender • {{ $levels->count() }} Level
                    </span>
                </div>
            </div>

            <!-- layar awal & pilih level -->
            <div x-show="state === 'menu'" class="rounded-[28px] bg-white border border-gray-100 p-7 sm:p-10 shadow-neutral text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#DBEAFE] text-sky flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <line x1="12" y1="8" x2="12" y2="16" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                    </svg>
                </div>

                <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-ink">
                    Cyber Defender
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-muted max-w-lg mx-auto leading-relaxed">
                    {{ __('ui.game.defender_intro') }}
                </p>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-left max-w-2xl mx-auto">
                    <div class="rounded-2xl bg-[#F7F8FC] p-3.5 border border-gray-100">
                        <span class="text-[10px] font-bold uppercase text-sky block">Antivirus</span>
                        <p class="text-xs font-semibold text-ink mt-1">{{ __('ui.game.antivirus_desc') }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F7F8FC] p-3.5 border border-gray-100">
                        <span class="text-[10px] font-bold uppercase text-sky block">Firewall</span>
                        <p class="text-xs font-semibold text-ink mt-1">{{ __('ui.game.firewall_desc') }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F7F8FC] p-3.5 border border-gray-100">
                        <span class="text-[10px] font-bold uppercase text-emerald-600 block">{{ app()->getLocale() === 'en' ? 'Awareness' : 'Edukasi' }}</span>
                        <p class="text-xs font-semibold text-ink mt-1">{{ __('ui.game.edukasi_desc') }}</p>
                    </div>
                </div>

                <!-- Pilihan Level Pertahanan -->
                <div class="mt-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-left">
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
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-red-500">
                                        <svg class="w-3.5 h-3.5 fill-red-500 shrink-0" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" /></svg>
                                        <span>{{ $lvl->hp_awal }} HP</span>
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
                                <span class="text-emerald-600">{{ __('ui.game.pts_per_block', ['pts' => $lvl->poin_benar]) }}</span>
                                <span class="text-sky group-hover:translate-x-1 transition-transform">{{ __('ui.game.start') }} &rarr;</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- arena permainan aktif -->
            <div x-show="state === 'playing'" style="display: none;" class="space-y-4">

                <!-- bar nyawa, level, waktu, dan skor sticky di HP -->
                <div class="sticky top-[68px] sm:top-[76px] z-30 rounded-2xl bg-white border border-gray-100 p-3.5 sm:p-4 flex flex-wrap items-center justify-between gap-3 sm:gap-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-[#DBEAFE] text-sky px-3 py-1 text-xs font-extrabold uppercase" x-text="activeLevelConfig.badge_label"></span>
                        <div class="flex items-center gap-1" aria-label="Nyawa Sistem">
                            <template x-for="i in maxHp" :key="i">
                                <svg
                                    class="w-5 h-5 transition-colors duration-200"
                                    :class="i <= hp ? 'text-red-500 fill-red-500' : 'text-gray-200 fill-gray-200'"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <polyline points="12 7 12 12 15 15" />
                        </svg>
                        <span class="font-heading font-extrabold text-sm text-ink" x-text="sessionTime + '{{ __('ui.game.sec_short') }}'"></span>
                    </div>

                    <div class="rounded-full bg-lime px-3.5 py-1 text-xs font-extrabold text-ink" x-text="'{{ __('ui.game.score_label') }}: ' + score"></div>
                </div>

                <!-- layar radar pertahanan -->
                <div class="relative h-[340px] rounded-[26px] bg-[#0B1120] text-white overflow-hidden border border-gray-800 p-6 flex flex-col justify-between">
                    <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(#38BDF8 1px, transparent 1px); background-size: 22px 22px;"></div>

                    <div class="relative z-10 flex items-center justify-between text-xs">
                        <span class="text-white/60 font-mono" x-text="'MODE: ' + (activeLevelConfig.nama_level || 'CORE DEFENSE').toUpperCase()"></span>
                        <span
                            x-show="statusBanner !== ''"
                            x-text="statusBanner"
                            class="rounded-full px-3 py-0.5 text-[11px] font-bold"
                            :class="statusOk ? 'bg-lime text-[#0A0A0A]' : 'bg-red-500 text-white'"
                        ></span>
                    </div>

                    <!-- card ancaman -->
                    <div
                        class="absolute left-1/2 -translate-x-1/2 w-full max-w-md px-4 z-20 transition-all duration-100"
                        :style="'top: ' + threatProgress + '%;'"
                    >
                        <div class="rounded-2xl bg-white text-[#0A0A0A] p-4 shadow-neutral border-2 border-red-400 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                        <line x1="12" y1="9" x2="12" y2="13" />
                                        <line x1="12" y1="17" x2="12.01" y2="17" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-red-600 block" x-text="'{{ __('ui.game.threat_label') }}: ' + activeThreat.categoryLabel"></span>
                                    <p class="font-heading font-bold text-sm text-[#0A0A0A]" x-text="activeThreat.name"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- representasi sistem -->
                    <div class="relative z-10 mt-auto flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl bg-sky/20 border border-sky/50 text-lime flex items-center justify-center shadow-sm">
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60 mt-1.5">Digi-Safe Core Server</span>
                    </div>
                </div>

                <!-- 3 tombol pilihan pertahanan -->
                <div class="grid grid-cols-3 gap-3">
                    <button
                        type="button"
                        @click="defend('virus')"
                        class="rounded-2xl bg-white hover:bg-sky hover:text-white border border-gray-200 p-4 text-center transition-all shadow-2xs cursor-pointer group"
                    >
                        <div class="w-9 h-9 rounded-xl bg-sky/10 text-sky group-hover:bg-white/20 group-hover:text-white flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm block">Antivirus</span>
                        <span class="text-[10px] opacity-70 block mt-0.5">{{ __('ui.game.block_virus') }}</span>
                    </button>

                    <button
                        type="button"
                        @click="defend('malware')"
                        class="rounded-2xl bg-white hover:bg-sky hover:text-white border border-gray-200 p-4 text-center transition-all shadow-2xs cursor-pointer group"
                    >
                        <div class="w-9 h-9 rounded-xl bg-[#DBEAFE] text-sky group-hover:bg-white/20 group-hover:text-white flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm block">Firewall</span>
                        <span class="text-[10px] opacity-70 block mt-0.5">{{ __('ui.game.block_malware') }}</span>
                    </button>

                    <button
                        type="button"
                        @click="defend('phishing')"
                        class="rounded-2xl bg-white hover:bg-emerald-600 hover:text-white border border-gray-200 p-4 text-center transition-all shadow-2xs cursor-pointer group"
                    >
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 group-hover:bg-white/20 group-hover:text-white flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                            </svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm block">{{ app()->getLocale() === 'en' ? 'Awareness' : 'Edukasi' }}</span>
                        <span class="text-[10px] opacity-70 block mt-0.5">{{ __('ui.game.reject_phishing') }}</span>
                    </button>
                </div>

            </div>

            <!-- layar game over / selesai -->
            <div x-show="state === 'finished'" style="display: none;" class="rounded-[28px] bg-white border border-gray-100 p-8 sm:p-10 shadow-neutral text-center">
                <div
                    class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 font-heading font-extrabold text-xl"
                    :class="hp > 0 ? 'bg-lime text-ink' : 'bg-red-100 text-red-600'"
                    x-text="hp > 0 ? 'WIN' : 'KO'"
                ></div>

                <h2 class="font-heading font-extrabold text-2xl text-ink" x-text="hp > 0 ? '{{ __('ui.game.def_win') }}' : '{{ __('ui.game.def_ko') }}'"></h2>
                <p class="mt-1 text-xs text-muted" x-text="saveStatus"></p>

                <div class="mt-6 grid grid-cols-3 gap-3 max-w-md mx-auto">
                    <div class="rounded-2xl bg-[#F7F8FC] p-4">
                        <span class="text-[10px] font-semibold text-muted block">{{ __('ui.game.final_score') }}</span>
                        <span class="font-heading font-extrabold text-xl text-ink mt-1 block" x-text="score"></span>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-4">
                        <span class="text-[10px] font-semibold text-emerald-700 block">{{ __('ui.game.defended') }}</span>
                        <span class="font-heading font-extrabold text-xl text-emerald-700 mt-1 block" x-text="correctCount"></span>
                    </div>
                    <div class="rounded-2xl bg-red-50 p-4">
                        <span class="text-[10px] font-semibold text-red-600 block">{{ __('ui.game.breached') }}</span>
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
        function cyberDefenderGame() {
            const isEn = @js(app()->getLocale() === 'en');
            const levelsMap = @js($levels->keyBy('slug'));

            const threatPoolId = [
                { name: 'Trojan File Unduhan .exe', type: 'virus', categoryLabel: 'VIRUS FILE' },
                { name: 'Worm Flashdisk Publik', type: 'virus', categoryLabel: 'VIRUS FILE' },
                { name: 'Macro Virus Lampiran Word', type: 'virus', categoryLabel: 'VIRUS FILE' },
                { name: 'Keylogger Installer Bajakan', type: 'virus', categoryLabel: 'VIRUS FILE' },
                { name: 'Serangan Ransomware Jaringan', type: 'malware', categoryLabel: 'MALWARE JARINGAN' },
                { name: 'Intrusi Port Terbuka 4444', type: 'malware', categoryLabel: 'MALWARE JARINGAN' },
                { name: 'Spyware Injeksi Paket Data', type: 'malware', categoryLabel: 'MALWARE JARINGAN' },
                { name: 'Botnet DDoS Traffic Flood', type: 'malware', categoryLabel: 'MALWARE JARINGAN' },
                { name: 'Pesan WhatsApp Undangan .APK', type: 'phishing', categoryLabel: 'PHISHING SOSIAL' },
                { name: 'Telepon Penyamar Petugas Bank', type: 'phishing', categoryLabel: 'PHISHING SOSIAL' },
                { name: 'Tautan Login Hadiah Palsu', type: 'phishing', categoryLabel: 'PHISHING SOSIAL' },
                { name: 'Manipulasi OAuth Kalender Palsu', type: 'phishing', categoryLabel: 'PHISHING SOSIAL' }
            ];

            const threatPoolEn = [
                { name: 'Trojan .exe Download File', type: 'virus', categoryLabel: 'FILE VIRUS' },
                { name: 'Public USB Flash Drive Worm', type: 'virus', categoryLabel: 'FILE VIRUS' },
                { name: 'Word Attachment Macro Virus', type: 'virus', categoryLabel: 'FILE VIRUS' },
                { name: 'Cracked Installer Keylogger', type: 'virus', categoryLabel: 'FILE VIRUS' },
                { name: 'Network Ransomware Outbreak', type: 'malware', categoryLabel: 'NETWORK MALWARE' },
                { name: 'Open Port 4444 Intrusion', type: 'malware', categoryLabel: 'NETWORK MALWARE' },
                { name: 'Packet Injection Spyware', type: 'malware', categoryLabel: 'NETWORK MALWARE' },
                { name: 'Botnet DDoS Traffic Flood', type: 'malware', categoryLabel: 'NETWORK MALWARE' },
                { name: 'WhatsApp Fake .APK Invitation', type: 'phishing', categoryLabel: 'SOCIAL PHISHING' },
                { name: 'Fake Bank Officer OTP Phone Call', type: 'phishing', categoryLabel: 'SOCIAL PHISHING' },
                { name: 'Fake Prize Login Link Trap', type: 'phishing', categoryLabel: 'SOCIAL PHISHING' },
                { name: 'Rogue Calendar OAuth Consent Trap', type: 'phishing', categoryLabel: 'SOCIAL PHISHING' }
            ];

            const threatPool = isEn ? threatPoolEn : threatPoolId;

            return {
                state: 'menu',
                level: 'easy',
                activeLevelConfig: {
                    nama_level: 'Pemula Siber',
                    badge_label: 'LEVEL 1 • EASY',
                    waktu_detik: 15,
                    poin_benar: 15,
                    hp_awal: 3
                },
                maxHp: 3,
                hp: 3,
                score: 0,
                correctCount: 0,
                wrongCount: 0,
                sessionTime: 60,
                threatProgress: 12,
                activeThreat: threatPool[0],
                statusBanner: '',
                statusOk: true,
                saveStatus: '',
                sessionInterval: null,
                fallInterval: null,

                startGame(selectedLevel = 'easy') {
                    this.level = selectedLevel;
                    const cfg = levelsMap[selectedLevel] || {
                        nama_level: selectedLevel.toUpperCase(),
                        badge_label: 'LEVEL • ' + selectedLevel.toUpperCase(),
                        waktu_detik: 15,
                        poin_benar: 15,
                        hp_awal: 3
                    };
                    this.activeLevelConfig = cfg;
                    this.maxHp = parseInt(cfg.hp_awal || 3, 10);
                    this.hp = this.maxHp;
                    this.score = 0;
                    this.correctCount = 0;
                    this.wrongCount = 0;
                    this.sessionTime = 60;
                    this.statusBanner = '';
                    this.state = 'playing';
                    this.spawnThreat();

                    if (this.sessionInterval) clearInterval(this.sessionInterval);
                    if (this.fallInterval) clearInterval(this.fallInterval);

                    const baseSpeed = Math.max(1.2, (20 - Math.min(16, parseInt(cfg.waktu_detik || 15, 10))) * 0.22);

                    this.sessionInterval = setInterval(() => {
                        if (this.sessionTime > 1) {
                            this.sessionTime--;
                        } else {
                            this.sessionTime = 0;
                            this.endSession();
                        }
                    }, 1000);

                    this.fallInterval = setInterval(() => {
                        const speedBoost = this.score >= 100 ? 0.85 : (this.score >= 50 ? 0.45 : 0);
                        this.threatProgress += (baseSpeed + speedBoost);

                        if (this.threatProgress >= 66) {
                            this.takeDamage(isEn ? 'Threat Breached Server! -1 HP' : 'Ancaman Menyentuh Server! -1 HP');
                        }
                    }, 100);
                },

                spawnThreat() {
                    this.threatProgress = 12;
                    const idx = Math.floor(Math.random() * threatPool.length);
                    this.activeThreat = threatPool[idx];
                },

                defend(choice) {
                    if (this.state !== 'playing') return;
                    const pts = parseInt(this.activeLevelConfig.poin_benar || 15, 10);

                    if (choice === this.activeThreat.type) {
                        this.score += pts;
                        this.correctCount++;
                        this.statusOk = true;
                        this.statusBanner = (isEn ? 'Threat Neutralized! +' : 'Ancaman Dihancurkan! +') + pts;
                        this.spawnThreat();
                    } else {
                        this.takeDamage(isEn ? 'Wrong Defense! -1 HP' : 'Pertahanan Salah! -1 HP');
                    }
                },

                takeDamage(msg) {
                    this.hp--;
                    this.wrongCount++;
                    this.statusOk = false;
                    this.statusBanner = msg;

                    if (this.hp <= 0) {
                        this.endSession();
                    } else {
                        this.spawnThreat();
                    }
                },

                endSession() {
                    if (this.sessionInterval) clearInterval(this.sessionInterval);
                    if (this.fallInterval) clearInterval(this.fallInterval);
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
                            game_type: 'cyber-defender',
                            skor: this.score,
                            benar: this.correctCount,
                            salah: this.wrongCount,
                            avg_time_sec: this.correctCount > 0 ? Math.max(1, (60 - this.sessionTime) / this.correctCount) : 99
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
