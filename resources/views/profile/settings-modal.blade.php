<!-- Modal Pengaturan: Bahasa (ID/EN), Mode Layar (Terang/Gelap), & Kontrol Privasi Profil -->
@if ($isOwnProfile)
    <div
        x-show="settingsOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none; background: rgba(10, 10, 10, 0.58);"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
        @keydown.escape.window="settingsOpen = false"
    >
        <div
            x-data="{
                selectedLocale: @js($profileUser->locale ?: ($currentLocale ?? 'id')),
                selectedTheme: @js($profileUser->theme ?: ($currentTheme ?? 'light')),
                privPrivate: @js($privacy['is_private_profile']),
                privLocation: @js($privacy['hide_location']),
                privEmail: @js($privacy['hide_email']),
                privStats: @js($privacy['hide_minigame_stats']),
                privBadges: @js($privacy['hide_badges']),
                privActivity: @js($privacy['hide_activity'])
            }"
            @click.outside="settingsOpen = false"
            class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-[28px] bg-white border border-gray-100 p-6 sm:p-8 shadow-neutral"
        >
            <!-- Header Modal Pengaturan -->
            <div class="flex items-start justify-between gap-4 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-sky/10 text-sky flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="font-heading font-extrabold text-lg text-ink">{{ __('ui.profile.settings_title') }}</h3>
                        <p class="text-xs text-muted mt-0.5">{{ __('ui.profile.settings_sub') }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="settingsOpen = false"
                    class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center transition-colors cursor-pointer shrink-0"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('profile.settings') }}" method="POST" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- 1. PILIHAN BAHASA (INDONESIA / ENGLISH) -->
                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4 sm:p-5">
                    <div class="mb-3">
                        <h4 class="font-heading font-extrabold text-sm text-ink flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="2" y1="12" x2="22" y2="12" /><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" /></svg>
                            <span>{{ __('ui.profile.lang_label') }}</span>
                        </h4>
                        <p class="text-[11px] text-muted mt-0.5">{{ __('ui.profile.lang_desc') }}</p>
                    </div>

                    <input type="hidden" name="locale" :value="selectedLocale">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button
                            type="button"
                            @click="selectedLocale = 'id'"
                            :class="selectedLocale === 'id'
                                ? 'border-sky bg-sky/10 ring-2 ring-sky/25'
                                : 'border-gray-200 bg-white hover:border-gray-300'"
                            class="rounded-2xl border p-3.5 text-left flex items-center justify-between gap-3 transition-all cursor-pointer"
                        >
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-red-50 text-red-600 font-heading font-extrabold text-xs flex items-center justify-center border border-red-200">ID</span>
                                <div>
                                    <span class="font-heading font-bold text-xs text-ink block">{{ __('ui.profile.lang_id') }}</span>
                                    <span class="text-[10px] text-muted">Default Purwakarta</span>
                                </div>
                            </div>
                            <span
                                class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="selectedLocale === 'id' ? 'border-sky bg-sky' : 'border-gray-300'"
                            >
                                <span x-show="selectedLocale === 'id'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="selectedLocale = 'en'"
                            :class="selectedLocale === 'en'
                                ? 'border-sky bg-sky/10 ring-2 ring-sky/25'
                                : 'border-gray-200 bg-white hover:border-gray-300'"
                            class="rounded-2xl border p-3.5 text-left flex items-center justify-between gap-3 transition-all cursor-pointer"
                        >
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-[#DBEAFE] text-sky font-heading font-extrabold text-xs flex items-center justify-center border border-sky/30">EN</span>
                                <div>
                                    <span class="font-heading font-bold text-xs text-ink block">{{ __('ui.profile.lang_en') }}</span>
                                    <span class="text-[10px] text-muted">Full App &amp; Minigames</span>
                                </div>
                            </div>
                            <span
                                class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="selectedLocale === 'en' ? 'border-sky bg-sky' : 'border-gray-300'"
                            >
                                <span x-show="selectedLocale === 'en'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- 2. PILIHAN MODE TAMPILAN LAYAR (TERANG / GELAP) -->
                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4 sm:p-5">
                    <div class="mb-3">
                        <h4 class="font-heading font-extrabold text-sm text-ink flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5" /><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" /></svg>
                            <span>{{ __('ui.profile.theme_label') }}</span>
                        </h4>
                        <p class="text-[11px] text-muted mt-0.5">{{ __('ui.profile.theme_desc') }}</p>
                    </div>

                    <input type="hidden" name="theme" :value="selectedTheme">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Mode Terang -->
                        <button
                            type="button"
                            @click="selectedTheme = 'light'; document.documentElement.classList.remove('dark');"
                            :class="selectedTheme === 'light'
                                ? 'border-sky bg-sky/10 ring-2 ring-sky/25'
                                : 'border-gray-200 bg-white hover:border-gray-300'"
                            class="rounded-2xl border p-3.5 text-left flex items-center justify-between gap-3 transition-all cursor-pointer"
                        >
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-500 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <circle cx="12" cy="12" r="5" />
                                        <line x1="12" y1="1" x2="12" y2="3" /><line x1="12" y1="21" x2="12" y2="23" />
                                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" /><line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                                        <line x1="1" y1="12" x2="3" y2="12" /><line x1="21" y1="12" x2="23" y2="12" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="font-heading font-bold text-xs text-ink block">{{ __('ui.profile.theme_light') }}</span>
                                    <span class="text-[10px] text-muted">Clean Sky &amp; Lime</span>
                                </div>
                            </div>
                            <span
                                class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="selectedTheme === 'light' ? 'border-sky bg-sky' : 'border-gray-300'"
                            >
                                <span x-show="selectedTheme === 'light'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            </span>
                        </button>

                        <!-- Mode Gelap -->
                        <button
                            type="button"
                            @click="selectedTheme = 'dark'; document.documentElement.classList.add('dark');"
                            :class="selectedTheme === 'dark'
                                ? 'border-sky bg-sky/15 ring-2 ring-sky/30'
                                : 'border-gray-200 bg-white hover:border-gray-300'"
                            class="rounded-2xl border p-3.5 text-left flex items-center justify-between gap-3 transition-all cursor-pointer"
                        >
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-[#0F172A] border border-slate-700 text-lime flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="font-heading font-bold text-xs text-ink block">{{ __('ui.profile.theme_dark') }}</span>
                                    <span class="text-[10px] text-muted">Midnight Cyber Palette</span>
                                </div>
                            </div>
                            <span
                                class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="selectedTheme === 'dark' ? 'border-sky bg-sky' : 'border-gray-300'"
                            >
                                <span x-show="selectedTheme === 'dark'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- 3. PENGATURAN PRIVASI & KERAHASIAAN PROFIL -->
                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4 sm:p-5 space-y-3.5">
                    <div>
                        <h4 class="font-heading font-extrabold text-sm text-ink flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                            <span>{{ __('ui.profile.privacy_label') }}</span>
                        </h4>
                        <p class="text-[11px] text-muted mt-0.5">{{ __('ui.profile.privacy_desc') }}</p>
                    </div>

                    <!-- Switch Utama: Mode Profil Privat (Hanya tampilkan Nama, PP & Banner) -->
                    <label
                        class="flex items-center justify-between gap-4 rounded-2xl p-4 border transition-all cursor-pointer select-none"
                        :class="privPrivate ? 'bg-amber-50 border-amber-300 shadow-2xs' : 'bg-white border-gray-200 hover:border-sky/40'"
                    >
                        <div class="pr-2">
                            <div class="flex items-center gap-2">
                                <span class="font-heading font-extrabold text-xs text-ink">{{ __('ui.profile.priv_private_title') }}</span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider transition-colors"
                                    :class="privPrivate ? 'bg-amber-400/25 text-amber-700 dark:text-amber-300' : 'bg-gray-100 text-muted'"
                                    x-text="privPrivate ? 'Privat' : 'Publik'"
                                ></span>
                            </div>
                            <span class="text-[11px] text-muted leading-relaxed block mt-0.5">{{ __('ui.profile.priv_private_desc') }}</span>
                        </div>
                        <div class="relative shrink-0">
                            <input type="checkbox" name="is_private_profile" value="1" x-model="privPrivate" class="sr-only">
                            <div
                                class="w-11 h-6 rounded-full transition-colors duration-200 flex items-center px-0.5"
                                :class="privPrivate ? 'bg-amber-500' : 'bg-gray-300 dark:bg-slate-600'"
                            >
                                <span
                                    class="w-5 h-5 rounded-full bg-white shadow-xs transform transition-transform duration-200"
                                    :class="privPrivate ? 'translate-x-5' : 'translate-x-0'"
                                ></span>
                            </div>
                        </div>
                    </label>

                    <!-- Opsi Granular Variatif -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @php
                            $granularPrivacy = [
                                ['name' => 'hide_location', 'model' => 'privLocation', 'title' => __('ui.profile.priv_location_title'), 'desc' => __('ui.profile.priv_location_desc'), 'span' => ''],
                                ['name' => 'hide_email', 'model' => 'privEmail', 'title' => __('ui.profile.priv_email_title'), 'desc' => __('ui.profile.priv_email_desc'), 'span' => ''],
                                ['name' => 'hide_minigame_stats', 'model' => 'privStats', 'title' => __('ui.profile.priv_stats_title'), 'desc' => __('ui.profile.priv_stats_desc'), 'span' => ''],
                                ['name' => 'hide_badges', 'model' => 'privBadges', 'title' => __('ui.profile.priv_badges_title'), 'desc' => __('ui.profile.priv_badges_desc'), 'span' => ''],
                                ['name' => 'hide_activity', 'model' => 'privActivity', 'title' => __('ui.profile.priv_activity_title'), 'desc' => __('ui.profile.priv_activity_desc'), 'span' => 'sm:col-span-2'],
                            ];
                        @endphp

                        @foreach ($granularPrivacy as $privItem)
                            <label
                                class="{{ $privItem['span'] }} flex items-center justify-between gap-3 rounded-xl border p-3.5 cursor-pointer transition-all select-none"
                                :class="{{ $privItem['model'] }} ? 'bg-[#EFF6FF] border-sky/40 shadow-2xs' : 'bg-white border-gray-200 hover:border-sky/40'"
                            >
                                <div class="min-w-0">
                                    <span class="font-heading font-bold text-[11px] text-ink block">{{ $privItem['title'] }}</span>
                                    <span class="text-[10px] text-muted leading-snug block mt-0.5">{{ $privItem['desc'] }}</span>
                                </div>
                                <div class="relative shrink-0">
                                    <input type="checkbox" name="{{ $privItem['name'] }}" value="1" x-model="{{ $privItem['model'] }}" class="sr-only">
                                    <div
                                        class="w-10 h-5.5 rounded-full transition-colors duration-200 flex items-center px-0.5"
                                        :class="{{ $privItem['model'] }} ? 'bg-sky' : 'bg-gray-300 dark:bg-slate-600'"
                                    >
                                        <span
                                            class="w-4.5 h-4.5 rounded-full bg-white shadow-xs transform transition-transform duration-200"
                                            :class="{{ $privItem['model'] }} ? 'translate-x-4.5' : 'translate-x-0'"
                                        ></span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Tombol Simpan Pengaturan -->
                <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-gray-100">
                    <button
                        type="button"
                        @click="settingsOpen = false"
                        class="rounded-full border border-gray-200 bg-white px-5 py-2.5 text-xs font-semibold text-muted hover:text-ink cursor-pointer"
                    >
                        {{ __('ui.profile.cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-6 py-2.5 shadow-xs transition-colors cursor-pointer"
                    >
                        {{ __('ui.profile.save_settings') }}
                    </button>
                </div>
            </form>

            <!-- 3B. RIWAYAT AKTIVITAS LOGIN & KEAMANAN AKUN -->
            @php
                $recentSecurityLogs = $securityActivities ?? \App\Services\SecurityActivityService::recentForUser($profileUser, 8);
            @endphp
            <div class="mt-6 pt-6 border-t border-gray-200/80 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h4 class="font-heading font-extrabold text-xs sm:text-sm text-ink flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>{{ app()->getLocale() === 'en' ? 'Login & Security Activity History' : 'Riwayat Aktivitas Login & Keamanan' }}</span>
                        </h4>
                        <p class="text-[11px] text-muted mt-0.5">
                            {{ app()->getLocale() === 'en' ? 'Recent sign-ins and sensitive security changes on your account.' : 'Catatan login terbaru dan perubahan keamanan penting pada akun Anda.' }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 text-slate-700 px-2.5 py-1 text-[10px] font-heading font-bold">
                        {{ count($recentSecurityLogs) }} {{ app()->getLocale() === 'en' ? 'events' : 'catatan' }}
                    </span>
                </div>

                @if (count($recentSecurityLogs) > 0)
                    <div class="rounded-2xl border border-gray-200/80 bg-[#F8FAFC] divide-y divide-gray-200/70 max-h-56 overflow-y-auto">
                        @foreach ($recentSecurityLogs as $secLog)
                            @php
                                $dotClass = match ($secLog['tone']) {
                                    'emerald' => 'bg-emerald-500',
                                    'amber' => 'bg-amber-500',
                                    'red' => 'bg-red-500',
                                    default => 'bg-sky',
                                };
                            @endphp
                            <div class="px-3.5 py-2.5 flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $dotClass }}"></span>
                                    <div class="min-w-0">
                                        <span class="block font-heading font-bold text-xs text-ink leading-snug">{{ $secLog['label'] }}</span>
                                        <span class="block text-[10.5px] text-muted truncate mt-0.5">
                                            {{ $secLog['device'] }} &middot; IP {{ $secLog['ip'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="block text-[10.5px] font-semibold text-ink/80">{{ $secLog['time_wib'] }}</span>
                                    <span class="block text-[10px] text-muted">{{ $secLog['time_ago'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-gray-200 bg-[#F8FAFC] px-4 py-3 text-[11px] text-muted">
                        {{ app()->getLocale() === 'en'
                            ? 'No security activity recorded yet. Future logins and password changes will appear here.'
                            : 'Belum ada riwayat login tercatat. Login berikutnya dan perubahan keamanan akun Anda akan otomatis muncul di sini.' }}
                    </div>
                @endif
            </div>

            <!-- 4. KEAMANAN AKUN: RESET PASSWORD & HAPUS AKUN VIA OTP GMAIL -->
            <div
                x-data="{
                    delPassword: '',
                    showDelPass: false,
                    delOtpSent: false,
                    delSending: false,
                    delSubmitting: false,
                    delCooldown: 0,
                    delTimer: null,
                    delOtp: '',
                    delConfirm: false,
                    delNotice: '',
                    delError: '',
                    startDelCooldown(sec) {
                        this.delCooldown = Math.max(0, Number(sec) || 0);
                        if (this.delTimer) clearInterval(this.delTimer);
                        if (this.delCooldown <= 0) return;
                        this.delTimer = setInterval(() => {
                            this.delCooldown--;
                            if (this.delCooldown <= 0) {
                                clearInterval(this.delTimer);
                                this.delTimer = null;
                            }
                        }, 1000);
                    },
                    async sendDeleteOtp() {
                        if (this.delSending || this.delCooldown > 0) return;
                        this.delError = '';
                        this.delNotice = '';
                        if (!this.delPassword || this.delPassword.trim() === '') {
                            this.delError = @js(app()->getLocale() === 'en' ? 'Please enter your account password first before requesting an OTP code.' : 'Masukkan password akun Anda terlebih dahulu sebelum mengirim kode OTP.');
                            return;
                        }
                        this.delSending = true;
                        try {
                            const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                            const res = await fetch('{{ route('profile.delete.send-otp') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({
                                    current_password: this.delPassword,
                                    locale: @js(app()->getLocale())
                                })
                            });
                            const data = await res.json().catch(() => ({}));
                            if (res.ok && data.ok) {
                                this.delOtp = '';
                                this.delOtpSent = true;
                                this.delNotice = data.message || @js(__('ui.profile.delete_otp_sent'));
                                this.startDelCooldown(data.cooldown || 20);
                            } else {
                                const firstErr = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                                this.delError = firstErr || data.message || @js(__('ui.profile.delete_otp_failed'));
                                if (data.retry_after && res.status === 429 && this.delOtpSent) {
                                    this.startDelCooldown(data.retry_after);
                                }
                            }
                        } catch (_) {
                            this.delError = @js(__('ui.profile.delete_otp_failed'));
                        } finally {
                            this.delSending = false;
                        }
                    },
                    async submitDeleteAccount() {
                        if (this.delSubmitting) return;
                        this.delError = '';
                        if (!this.delPassword || this.delPassword.trim() === '') {
                            this.delError = @js(app()->getLocale() === 'en' ? 'Your account password is required.' : 'Password akun wajib diisi.');
                            return;
                        }
                        if (!/^\d{6}$/.test((this.delOtp || '').trim())) {
                            this.delError = @js(app()->getLocale() === 'en' ? 'Please enter the 6-digit OTP code.' : 'Masukkan kode OTP 6 digit terlebih dahulu.');
                            return;
                        }
                        if (!this.delConfirm) {
                            this.delError = @js(app()->getLocale() === 'en' ? 'Please check the permanent deletion confirmation box.' : 'Centang kotak konfirmasi penghapusan permanen terlebih dahulu.');
                            return;
                        }
                        this.delSubmitting = true;
                        try {
                            const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                            const res = await fetch('{{ route('profile.delete.destroy') }}', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({
                                    current_password: this.delPassword,
                                    otp: this.delOtp.trim(),
                                    confirm_delete: true
                                })
                            });
                            const data = await res.json().catch(() => ({}));
                            if (res.ok && data.ok) {
                                window.location.href = data.redirect || '{{ route('beranda') }}';
                            } else {
                                const firstErr = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                                this.delError = firstErr || data.message || @js(__('ui.profile.delete_otp_failed'));
                            }
                        } catch (_) {
                            this.delError = @js(__('ui.profile.delete_otp_failed'));
                        } finally {
                            this.delSubmitting = false;
                        }
                    }
                }"
                class="mt-6 pt-6 border-t border-gray-200/80 space-y-4"
            >
                <!-- Akses Cepat Reset Password via OTP Gmail -->
                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="font-heading font-extrabold text-xs sm:text-sm text-ink flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 9.9-1" />
                            </svg>
                            <span>{{ __('ui.profile.reset_pass_otp_title') }}</span>
                        </h4>
                        <p class="text-[11px] text-muted mt-0.5">{{ __('ui.profile.reset_pass_otp_desc') }}</p>
                    </div>
                    <a
                        href="{{ route('password.otp.request') }}"
                        class="shrink-0 inline-flex items-center justify-center rounded-full bg-white border border-sky/40 hover:bg-sky hover:text-white text-sky font-heading font-bold text-xs px-4 py-2 min-h-[40px] transition-colors"
                    >
                        {{ __('ui.profile.reset_pass_otp_btn') }}
                    </a>
                </div>

                <!-- Zona Berbahaya: Hapus Akun Permanen via Verifikasi Password + OTP Gmail -->
                <div class="rounded-2xl bg-red-50/70 border border-red-200 p-4 sm:p-5 space-y-3.5">
                    <div>
                        <h4 class="font-heading font-extrabold text-sm text-red-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                            <span>{{ __('ui.profile.delete_account_title') }}</span>
                        </h4>
                        <p class="text-[11px] text-red-800/85 mt-1 leading-relaxed">
                            {{ __('ui.profile.delete_account_desc', ['email' => $profileUser->email]) }}
                        </p>
                    </div>

                    @if ($profileUser->isOwner())
                        <p class="rounded-xl bg-white/80 border border-red-200 px-3.5 py-2.5 text-[11px] font-semibold text-red-700">
                            {{ __('ui.profile.delete_owner_protected') }}
                        </p>
                    @else
                        <!-- Langkah 1: Verifikasi Password Akun sebelum bisa kirim OTP -->
                        <div class="space-y-2">
                            <label for="delete-account-password" class="block text-xs font-heading font-bold text-red-900">
                                {{ app()->getLocale() === 'en' ? '1. Verify Account Password' : '1. Verifikasi Password Akun' }}
                            </label>
                            <div class="flex flex-col sm:flex-row gap-2.5">
                                <div class="relative flex-1">
                                    <input
                                        id="delete-account-password"
                                        name="delete_verify_pass"
                                        :type="showDelPass ? 'text' : 'password'"
                                        x-model="delPassword"
                                        maxlength="128"
                                        autocomplete="new-password"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        spellcheck="false"
                                        data-lpignore="true"
                                        data-1p-ignore="true"
                                        readonly
                                        @focus="$el.removeAttribute('readonly')"
                                        placeholder="{{ app()->getLocale() === 'en' ? 'Type your account password manually' : 'Ketik password akun Anda secara manual' }}"
                                        class="w-full rounded-xl bg-white border border-red-300 pl-3.5 pr-10 py-2.5 text-xs text-ink placeholder:text-red-300 focus:outline-none focus:border-red-600 focus:ring-2 focus:ring-red-500/20"
                                    >
                                    <button
                                        type="button"
                                        @click="showDelPass = !showDelPass"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-red-400 hover:text-red-700 p-1 cursor-pointer"
                                        aria-label="{{ app()->getLocale() === 'en' ? 'Toggle password visibility' : 'Tampilkan password' }}"
                                    >
                                        <svg x-show="!showDelPass" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        <svg x-show="showDelPass" style="display: none;" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                            <line x1="1" y1="1" x2="23" y2="23" />
                                        </svg>
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    @click="sendDeleteOtp()"
                                    :disabled="delSending || delCooldown > 0 || !delPassword.trim()"
                                    class="shrink-0 rounded-xl bg-red-600 hover:bg-red-700 text-white font-heading font-bold text-xs px-4 py-2.5 min-h-[42px] inline-flex items-center justify-center gap-2 transition-colors cursor-pointer disabled:opacity-55 disabled:cursor-not-allowed"
                                >
                                    <span x-show="delSending" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin" style="display: none;"></span>
                                    <span x-text="delSending
                                        ? @js(app()->getLocale() === 'en' ? 'Verifying...' : 'Memverifikasi...')
                                        : (delCooldown > 0
                                            ? (@js(app()->getLocale() === 'en' ? 'Resend (' : 'Kirim ulang (') + delCooldown + @js(app()->getLocale() === 'en' ? 's)' : 'd)'))
                                            : (delOtpSent ? @js(__('ui.profile.delete_resend_otp')) : @js(app()->getLocale() === 'en' ? 'Verify & Send OTP' : 'Verifikasi & Kirim OTP')))"></span>
                                </button>
                            </div>

                            @if ($profileUser->oauth_provider === 'google' && ! data_get($profileUser->privacy_settings, 'has_local_password'))
                                <p class="text-[11px] text-red-800/80 leading-relaxed">
                                    {{ app()->getLocale() === 'en'
                                        ? 'Signed in with Google and have not set a password yet? Use "Reset Password" above to create one first.'
                                        : 'Masuk lewat Google dan belum pernah membuat password? Gunakan tombol "Reset Password" di atas untuk membuat password terlebih dahulu.' }}
                                </p>
                            @endif
                        </div>
                        <div
                            x-show="delNotice !== ''"
                            x-transition
                            style="display: none;"
                            role="status"
                            class="rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2.5 text-[11px] font-semibold text-emerald-800"
                            x-text="delNotice"
                        ></div>

                        <div
                            x-show="delError !== ''"
                            x-transition
                            style="display: none;"
                            role="alert"
                            class="rounded-xl bg-white border border-red-300 px-3.5 py-2.5 text-[11px] font-semibold text-red-700"
                            x-text="delError"
                        ></div>

                        <div x-show="delOtpSent" x-transition style="display: none;" class="space-y-3 pt-2 border-t border-red-200/80">
                            <div>
                                <div class="flex items-center justify-between mb-1.5 sm:max-w-[220px]">
                                    <label for="delete-account-otp" class="block text-xs font-heading font-bold text-red-900">
                                        {{ __('ui.profile.delete_otp_label') }}
                                    </label>
                                    <button
                                        type="button"
                                        @click="sendDeleteOtp()"
                                        :disabled="delSending || delCooldown > 0"
                                        class="text-[11px] font-semibold text-red-700 hover:text-red-900 underline cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-1"
                                    >
                                        <span x-show="delSending" class="w-3 h-3 border-2 border-red-700 border-t-transparent rounded-full animate-spin" style="display: none;"></span>
                                        <span x-text="delCooldown > 0
                                            ? (@js(app()->getLocale() === 'en' ? 'Resend (' : 'Kirim ulang (') + delCooldown + @js(app()->getLocale() === 'en' ? 's)' : 'd)'))
                                            : @js(__('ui.profile.delete_resend_otp'))"></span>
                                    </button>
                                </div>
                                <input
                                    id="delete-account-otp"
                                    type="text"
                                    x-model="delOtp"
                                    inputmode="numeric"
                                    maxlength="6"
                                    autocomplete="one-time-code"
                                    placeholder="000000"
                                    class="w-full sm:max-w-[220px] rounded-xl bg-white border border-red-300 px-4 py-2.5 text-center font-mono font-extrabold text-base tracking-[0.38em] text-ink focus:outline-none focus:border-red-600 focus:ring-2 focus:ring-red-500/20"
                                >
                            </div>

                            <label
                                for="delete-account-confirm-checkbox"
                                class="group flex items-start gap-3.5 rounded-xl border p-3.5 transition-all cursor-pointer select-none"
                                :class="delConfirm
                                    ? 'bg-red-50/90 border-red-400 shadow-2xs'
                                    : 'bg-white/95 border-red-200 hover:border-red-300 hover:bg-red-50/35'"
                            >
                                <span class="relative mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center">
                                    <input
                                        id="delete-account-confirm-checkbox"
                                        type="checkbox"
                                        x-model="delConfirm"
                                        class="peer sr-only"
                                    >
                                    <span
                                        class="flex h-5 w-5 items-center justify-center rounded-md border-2 transition-all peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/40 peer-focus-visible:ring-offset-1"
                                        :class="delConfirm
                                            ? 'bg-red-600 border-red-600 text-white'
                                            : 'bg-white border-red-300 group-hover:border-red-400 text-transparent'"
                                    >
                                        <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="delConfirm ? 'scale-100' : 'scale-75'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </span>
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block font-heading font-bold text-xs text-red-900 leading-snug">
                                        {{ app()->getLocale() === 'en' ? 'Permanent Deletion Confirmation' : 'Konfirmasi Penghapusan Permanen' }}
                                    </span>
                                    <span class="mt-0.5 block text-[11px] leading-relaxed text-red-900/80">
                                        {{ __('ui.profile.delete_confirm_checkbox') }}
                                    </span>
                                </span>
                            </label>

                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <button
                                    type="button"
                                    @click="submitDeleteAccount()"
                                    :disabled="delSubmitting"
                                    class="rounded-full bg-red-700 hover:bg-red-800 text-white font-heading font-extrabold text-xs px-5 py-2.5 min-h-[42px] inline-flex items-center justify-center gap-2 transition-colors cursor-pointer disabled:opacity-60"
                                >
                                    <span x-show="delSubmitting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin" style="display: none;"></span>
                                    <span>{{ __('ui.profile.delete_confirm_btn') }}</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

