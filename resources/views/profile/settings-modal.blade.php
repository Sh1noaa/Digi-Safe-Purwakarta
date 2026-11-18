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
                            <span>🌐</span>
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
                            <span>🎨</span>
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
                            <span>🔒</span>
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
        </div>
    </div>
@endif

