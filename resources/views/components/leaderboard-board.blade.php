@props([
    'datasets',
])

@php
    $isEn = app()->getLocale() === 'en';
@endphp

<div
    x-data="digiSafeLeaderboard(@js($datasets), @js($isEn))"
    class="w-full"
>
    <!-- wadah studio putih bersih tanpa sidebar kiri (navbar tetap normal di atas) -->
    <div class="relative rounded-[30px] bg-white border border-gray-200/85 shadow-[0_24px_65px_rgba(15,23,42,0.08)] p-5 sm:p-8 lg:p-10 overflow-visible">

        <!-- header & kontrol switch kategori -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-7 border-b border-gray-100">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-ink tracking-tight">
                        Leaderboard
                    </h2>
                    <span class="rounded-full bg-[#EFF6FF] border border-sky/25 text-[#1E63E9] px-3 py-1 text-[10px] font-heading font-extrabold uppercase tracking-wider">
                        {{ $isEn ? 'Live Community Rank' : 'Peringkat Komunitas' }}
                    </span>
                </div>

                <!-- tab kategori desktop -->
                <div class="hidden md:flex flex-wrap items-center gap-5 mt-5">
                    <template x-for="cat in categories" :key="cat.key">
                        <button
                            type="button"
                            @click="switchCategory(cat.key)"
                            :class="activeKey === cat.key
                                ? 'text-ink font-extrabold border-b-2 border-ink pb-1.5'
                                : 'text-muted hover:text-ink font-semibold pb-1.5 border-b-2 border-transparent'"
                            class="text-xs sm:text-sm font-heading transition-all cursor-pointer"
                            x-text="cat.label"
                        ></button>
                    </template>

                    <span class="h-4 w-px bg-gray-200 mx-1"></span>

                    <button
                        type="button"
                        @click="switchLimit(5)"
                        :class="rowLimit === 5 ? 'bg-ink text-lime' : 'bg-gray-100 text-muted hover:text-ink'"
                        class="rounded-full px-3 py-1 text-[11px] font-bold transition-colors cursor-pointer"
                    >
                        Top 5
                    </button>
                    <button
                        type="button"
                        @click="switchLimit(10)"
                        :class="rowLimit === 10 ? 'bg-ink text-lime' : 'bg-gray-100 text-muted hover:text-ink'"
                        class="rounded-full px-3 py-1 text-[11px] font-bold transition-colors cursor-pointer"
                    >
                        {{ $isEn ? 'All' : 'Semua' }}
                    </button>
                </div>
            </div>

            <!-- dekorasi taktil dial rotary switcher terinspirasi gambar 2 & 3 (desktop) -->
            <div class="hidden md:flex items-center gap-3 rounded-2xl bg-[#F4F6F9] border border-gray-200/80 p-2.5 shadow-2xs shrink-0">
                <div class="rounded-xl bg-white border border-gray-200/70 px-3 py-2 flex flex-col justify-between gap-1.5">
                    <div class="flex items-center gap-1.5">
                        <span class="rounded bg-lime px-1.5 py-0.5 font-mono text-[10px] font-extrabold text-ink">ALL INFO</span>
                        <span class="font-mono text-[9px] font-bold text-muted tracking-wider">WIB</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 pt-0.5">
                        <button
                            type="button"
                            @click="switchCategory('minigame_benar')"
                            :class="activeKey === 'minigame_benar' ? 'bg-ink text-lime' : 'bg-[#F8FAFC] text-muted hover:text-ink'"
                            class="rounded-lg px-2 py-1 font-mono text-[9px] font-bold transition-colors cursor-pointer"
                        >
                            GAME
                        </button>
                        <button
                            type="button"
                            @click="switchCategory('forum')"
                            :class="activeKey === 'forum' ? 'bg-lime text-ink' : 'bg-[#F8FAFC] text-muted hover:text-ink'"
                            class="rounded-lg px-2 py-1 font-mono text-[9px] font-bold transition-colors cursor-pointer"
                        >
                            FORUM
                        </button>
                    </div>
                </div>

                <!-- dial mekanis berputar mengikuti kategori aktif -->
                <div class="relative w-24 h-20 rounded-xl bg-[#272A30] border border-ink p-2 flex flex-col items-center justify-between text-white overflow-hidden">
                    <div class="w-full flex items-center justify-between text-[8px] font-mono text-slate-300 px-1">
                        <button type="button" @click="switchCategory('minigame_benar')" :class="activeKey === 'minigame_benar' ? 'text-lime font-bold' : ''" class="cursor-pointer">B</button>
                        <button type="button" @click="switchCategory('minigame_salah')" :class="activeKey === 'minigame_salah' ? 'text-lime font-bold' : ''" class="cursor-pointer">S</button>
                        <button type="button" @click="switchCategory('forum')" :class="activeKey === 'forum' ? 'text-lime font-bold' : ''" class="cursor-pointer">F</button>
                        <button type="button" @click="switchCategory('badges')" :class="activeKey === 'badges' ? 'text-lime font-bold' : ''" class="cursor-pointer">L</button>
                    </div>

                    <!-- jarum penunjuk yang berotasi halus -->
                    <div class="relative w-12 h-8 flex items-end justify-center">
                        <div class="w-10 h-5 rounded-t-full border-t-2 border-x-2 border-slate-500/60"></div>
                        <div
                            :style="'transform: rotate(' + currentDialDeg + 'deg); transform-origin: 50% 90%;'"
                            class="absolute bottom-0 w-1.5 h-7 rounded-full bg-lime shadow-sm transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                        ></div>
                        <span class="absolute -bottom-1 w-3 h-3 rounded-full bg-white border-2 border-[#272A30]"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= TAMPILAN DESKTOP (IMAGE 1: COLORFUL BARS + OVERHANGING AVATAR) ======================= -->
        <div class="hidden md:block pt-8">
            <template x-if="visibleRows.length === 0">
                <div class="rounded-2xl bg-[#F8FAFC] border border-dashed border-gray-200 py-14 text-center text-xs font-semibold text-muted">
                    {{ $isEn ? 'No ranking data yet for this category.' : 'Belum ada data peringkat untuk kategori ini.' }}
                </div>
            </template>

            <div class="space-y-4 sm:space-y-5 relative">
                <template x-for="(row, idx) in visibleRows" :key="row.id">
                    <div
                        :data-lb-uid="row.id"
                        @click="inspectUser = row"
                        :style="barStyle(idx)"
                        :class="idx === 0 ? 'min-h-[96px] py-5 shadow-[0_18px_36px_rgba(244,63,94,0.22)]' : 'min-h-[68px] py-3.5 shadow-[0_10px_24px_rgba(15,23,42,0.08)]'"
                        class="lb-row-card group relative rounded-[18px] px-6 sm:px-8 flex items-center justify-between text-white cursor-pointer hover:brightness-105 overflow-visible"
                    >
                        <!-- rank & identitas -->
                        <div class="flex items-center gap-5 sm:gap-7 min-w-0 flex-1 pr-4">
                            <span
                                :class="idx === 0 ? 'text-3xl sm:text-4xl' : 'text-xl sm:text-2xl'"
                                class="font-heading font-extrabold w-8 text-center shrink-0 tabular-nums"
                                x-text="row.rank"
                            ></span>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3
                                        :class="idx === 0 ? 'text-lg sm:text-2xl' : 'text-sm sm:text-base'"
                                        class="font-heading font-extrabold truncate leading-tight"
                                        x-text="row.nama"
                                    ></h3>
                                    <span
                                        x-show="row.specialty_role"
                                        class="hidden xl:inline-block rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-bold text-white shrink-0"
                                        x-text="row.specialty_role"
                                    ></span>
                                </div>

                                <!-- baris bintang rating + keterangan metrik -->
                                <div class="flex flex-wrap items-center gap-2.5 mt-1">
                                    <div class="flex items-center gap-0.5" aria-hidden="true">
                                        <template x-for="s in 5" :key="s">
                                            <svg
                                                class="w-3.5 h-3.5"
                                                :class="s <= row.stars ? 'text-white' : 'text-white/35'"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                            >
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                        </template>
                                    </div>
                                    <span class="text-[11px] font-medium text-white/90 truncate" x-text="row.subtitle"></span>
                                </div>
                            </div>
                        </div>

                        <!-- skor besar + foto profil pop-out menembus batas kanan bar -->
                        <div class="flex items-center shrink-0 pr-24 sm:pr-28">
                            <span
                                :class="idx === 0 ? 'text-2xl sm:text-3xl' : 'text-lg sm:text-xl'"
                                class="font-heading font-extrabold tracking-tight tabular-nums whitespace-nowrap"
                                x-text="row.metric_main"
                            ></span>
                        </div>

                        <!-- foto profil kanan yang melewati bar + border avatar jika terpasang -->
                        <div
                            :class="[
                                idx === 0
                                    ? '-top-6 right-5 w-24 h-24 rotate-3 group-hover:rotate-0 group-hover:scale-110'
                                    : '-top-2 right-6 w-16 h-16 -rotate-2 group-hover:rotate-3 group-hover:scale-110',
                                row.avatar_border_url ? 'rounded-full' : (idx === 0 ? 'rounded-[24px] ring-4 ring-white shadow-xl' : 'rounded-[18px] ring-[3px] ring-white shadow-xl')
                            ]"
                            class="absolute bg-white transition-transform duration-300 flex items-center justify-center z-20"
                        >
                            <div :class="row.avatar_border_url ? 'rounded-full' : (idx === 0 ? 'rounded-[24px]' : 'rounded-[18px]')" class="w-full h-full overflow-hidden flex items-center justify-center">
                                <template x-if="row.avatar_url">
                                    <img :src="row.avatar_url" :alt="row.nama" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!row.avatar_url">
                                    <div :class="row.pastel_class" class="w-full h-full flex items-center justify-center font-heading font-extrabold text-xl" x-text="row.inisial"></div>
                                </template>
                            </div>
                            <template x-if="row.avatar_border_url">
                                <img :src="row.avatar_border_url" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                            </template>
                        </div>

                        <!-- hiasan mahkota kecil untuk peringkat 1 -->
                        <template x-if="idx === 0">
                            <span class="absolute -top-9 right-11 w-7 h-7 rounded-full bg-lime text-ink border-2 border-white shadow-md flex items-center justify-center z-30 pointer-events-none">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M2 5l3 11h14l3-11-5.5 6L12 4l-4.5 7L2 5zm3 13h14v2H5v-2z" />
                                </svg>
                            </span>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- ======================= TAMPILAN MOBILE (IMAGE 4: PODIUM 1-2-3 + BIRU DIGI-SAFE TANPA UNGU) ======================= -->
        <div class="block md:hidden pt-5 space-y-6">
            <!-- selector pil kategori biru digi-safe -->
            <div class="rounded-full bg-[#EFF6FF] border border-sky/20 p-1 grid grid-cols-4 gap-1">
                <template x-for="cat in categories" :key="cat.key">
                    <button
                        type="button"
                        @click="switchCategory(cat.key)"
                        :class="activeKey === cat.key ? 'bg-[#1E63E9] text-white shadow-xs' : 'text-ink/70 hover:text-ink'"
                        class="rounded-full py-2 px-1 text-[10px] font-heading font-bold transition-all cursor-pointer truncate"
                        x-text="cat.short"
                    ></button>
                </template>
            </div>

            <template x-if="visibleRows.length === 0">
                <p class="py-10 text-center text-xs text-muted">{{ $isEn ? 'No ranking data yet.' : 'Belum ada data peringkat.' }}</p>
            </template>

            <!-- podium top 3 mobile (#2 kiri, #1 tengah besar + mahkota, #3 kanan) -->
            <template x-if="visibleRows.length > 0">
                <div>
                    <div class="pt-5 pb-3 grid grid-cols-3 items-end gap-2 text-center">
                        <!-- rank 2 -->
                        <div class="flex flex-col items-center">
                            <template x-if="visibleRows[1]">
                                <button type="button" @click="inspectUser = visibleRows[1]" class="flex flex-col items-center cursor-pointer">
                                    <div class="relative">
                                        <div class="w-16 h-16 rounded-full border-[3px] border-[#38BDF8] bg-white overflow-hidden shadow-md flex items-center justify-center">
                                            <template x-if="visibleRows[1].avatar_url">
                                                <img :src="visibleRows[1].avatar_url" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!visibleRows[1].avatar_url">
                                                <span :class="visibleRows[1].pastel_class" class="w-full h-full flex items-center justify-center font-heading font-extrabold text-base" x-text="visibleRows[1].inisial"></span>
                                            </template>
                                        </div>
                                        <template x-if="visibleRows[1].avatar_border_url">
                                            <img :src="visibleRows[1].avatar_border_url" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                                        </template>
                                        <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-5 h-5 rounded-full bg-[#1E63E9] text-white text-[10px] font-extrabold border-2 border-white flex items-center justify-center z-20">2</span>
                                    </div>
                                    <p class="mt-3 font-heading font-bold text-xs text-ink max-w-[92px] truncate" x-text="visibleRows[1].nama"></p>
                                    <p class="text-[10px] font-semibold text-sky" x-text="visibleRows[1].metric_main"></p>
                                </button>
                            </template>
                        </div>

                        <!-- rank 1 -->
                        <div class="flex flex-col items-center -mt-3">
                            <button type="button" @click="inspectUser = visibleRows[0]" class="flex flex-col items-center cursor-pointer">
                                <span class="mb-1 text-amber-400 drop-shadow-2xs">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M2 5l3 11h14l3-11-5.5 6L12 4l-4.5 7L2 5zm3 13h14v2H5v-2z" />
                                    </svg>
                                </span>
                                <div class="relative">
                                    <div class="w-20 h-20 rounded-full border-[4px] border-[#1E63E9] bg-white overflow-hidden shadow-lg flex items-center justify-center">
                                        <template x-if="visibleRows[0].avatar_url">
                                            <img :src="visibleRows[0].avatar_url" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!visibleRows[0].avatar_url">
                                            <span :class="visibleRows[0].pastel_class" class="w-full h-full flex items-center justify-center font-heading font-extrabold text-xl" x-text="visibleRows[0].inisial"></span>
                                        </template>
                                    </div>
                                    <template x-if="visibleRows[0].avatar_border_url">
                                        <img :src="visibleRows[0].avatar_border_url" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                                    </template>
                                    <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-lime text-ink text-[11px] font-extrabold border-2 border-white flex items-center justify-center z-20">1</span>
                                </div>
                                <p class="mt-3.5 font-heading font-extrabold text-xs text-ink max-w-[105px] truncate" x-text="visibleRows[0].nama"></p>
                                <p class="text-[11px] font-bold text-[#1E63E9]" x-text="visibleRows[0].metric_main"></p>
                            </button>
                        </div>

                        <!-- rank 3 -->
                        <div class="flex flex-col items-center">
                            <template x-if="visibleRows[2]">
                                <button type="button" @click="inspectUser = visibleRows[2]" class="flex flex-col items-center cursor-pointer">
                                    <div class="relative">
                                        <div class="w-16 h-16 rounded-full border-[3px] border-[#10B981] bg-white overflow-hidden shadow-md flex items-center justify-center">
                                            <template x-if="visibleRows[2].avatar_url">
                                                <img :src="visibleRows[2].avatar_url" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!visibleRows[2].avatar_url">
                                                <span :class="visibleRows[2].pastel_class" class="w-full h-full flex items-center justify-center font-heading font-extrabold text-base" x-text="visibleRows[2].inisial"></span>
                                            </template>
                                        </div>
                                        <template x-if="visibleRows[2].avatar_border_url">
                                            <img :src="visibleRows[2].avatar_border_url" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                                        </template>
                                        <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-5 h-5 rounded-full bg-[#10B981] text-white text-[10px] font-extrabold border-2 border-white flex items-center justify-center z-20">3</span>
                                    </div>
                                    <p class="mt-3 font-heading font-bold text-xs text-ink max-w-[92px] truncate" x-text="visibleRows[2].nama"></p>
                                    <p class="text-[10px] font-semibold text-emerald-600" x-text="visibleRows[2].metric_main"></p>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- daftar peringkat 4 ke bawah berwarna biru digi-safe -->
                    <template x-if="visibleRows.length > 3">
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-[11px] font-bold text-muted px-3 pb-2">
                                <span>{{ $isEn ? 'Rank • Player' : 'Peringkat • Pemain' }}</span>
                                <span>{{ $isEn ? 'Score' : 'Capaian' }}</span>
                            </div>
                            <div class="rounded-2xl bg-[#F0F8FF] border border-sky/15 p-2.5 space-y-2">
                                <template x-for="mRow in visibleRows.slice(3)" :key="mRow.id">
                                    <button
                                        type="button"
                                        @click="inspectUser = mRow"
                                        class="w-full rounded-xl bg-gradient-to-r from-[#1E63E9] to-[#1E88E5] px-3.5 py-2.5 text-white flex items-center justify-between gap-2.5 shadow-2xs cursor-pointer"
                                    >
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="font-heading font-extrabold text-xs w-5 text-center shrink-0" x-text="mRow.rank"></span>
                                            <div class="relative w-7 h-7 shrink-0">
                                                <div class="w-full h-full rounded-full bg-white overflow-hidden flex items-center justify-center">
                                                    <template x-if="mRow.avatar_url">
                                                        <img :src="mRow.avatar_url" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!mRow.avatar_url">
                                                        <span :class="mRow.pastel_class" class="w-full h-full flex items-center justify-center font-bold text-[10px]" x-text="mRow.inisial"></span>
                                                    </template>
                                                </div>
                                                <template x-if="mRow.avatar_border_url">
                                                    <img :src="mRow.avatar_border_url" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -inset-[22%] w-[144%] h-[144%] max-w-none object-contain z-10">
                                                </template>
                                            </div>
                                            <span class="font-heading font-bold text-xs truncate" x-text="mRow.nama"></span>
                                        </div>
                                        <span class="text-xs font-extrabold text-lime shrink-0" x-text="mRow.metric_main"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

    </div>

    <!-- modal preview cepat data profil saat bar atau foto leaderboard diklik -->
    <div
        x-show="inspectUser !== null"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none; background: rgba(10, 10, 10, 0.55);"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
        @keydown.escape.window="inspectUser = null"
    >
        <template x-if="inspectUser">
            <div
                @click.outside="inspectUser = null"
                class="w-full max-w-md rounded-[28px] bg-white border border-gray-200/90 shadow-2xl overflow-hidden"
            >
                <div
                    class="h-24 w-full relative"
                    :style="inspectUser.banner_url ? `background-image: url('${inspectUser.banner_url}'); background-size: cover; background-position: center;` : 'background: linear-gradient(120deg, #1E63E9 0%, #38BDF8 100%);'"
                >
                    <button type="button" @click="inspectUser = null" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-ink font-bold flex items-center justify-center shadow-sm cursor-pointer">&times;</button>
                </div>

                <div class="px-6 pb-6">
                    <div class="relative z-20 -mt-10 flex items-end justify-between gap-3">
                        <div class="w-20 h-20 rounded-2xl bg-white p-1 shadow-md overflow-hidden shrink-0">
                            <template x-if="inspectUser.avatar_url">
                                <img :src="inspectUser.avatar_url" class="w-full h-full object-cover rounded-xl">
                            </template>
                            <template x-if="!inspectUser.avatar_url">
                                <div :class="inspectUser.pastel_class" class="w-full h-full rounded-xl flex items-center justify-center font-heading font-extrabold text-2xl" x-text="inspectUser.inisial"></div>
                            </template>
                        </div>

                        <span class="rounded-full bg-[#EFF6FF] border border-sky/25 text-[#1E63E9] px-3 py-1 text-[11px] font-heading font-extrabold" x-text="'Peringkat #' + inspectUser.rank"></span>
                    </div>

                    <div class="mt-3">
                        <h3 class="font-heading font-extrabold text-lg text-ink" x-text="inspectUser.nama"></h3>
                        <p class="text-xs font-semibold text-sky" x-text="inspectUser.specialty_role || ('@' + inspectUser.username)"></p>
                        <p class="mt-1.5 text-xs text-muted leading-relaxed" x-text="inspectUser.bio"></p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                        <div class="rounded-xl bg-[#F8FAFC] border border-gray-100 p-2.5">
                            <span class="block font-heading font-extrabold text-sm text-emerald-600" x-text="inspectUser.total_benar"></span>
                            <span class="block text-[10px] text-muted">{{ $isEn ? 'Correct' : 'Benar Game' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#F8FAFC] border border-gray-100 p-2.5">
                            <span class="block font-heading font-extrabold text-sm text-amber-600" x-text="inspectUser.total_salah"></span>
                            <span class="block text-[10px] text-muted">{{ $isEn ? 'Missed' : 'Salah Game' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#F8FAFC] border border-gray-100 p-2.5">
                            <span class="block font-heading font-extrabold text-sm text-[#1E63E9]" x-text="inspectUser.total_jawaban"></span>
                            <span class="block text-[10px] text-muted">{{ $isEn ? 'Replies' : 'Jwb Forum' }}</span>
                        </div>
                        <div class="rounded-xl bg-[#F8FAFC] border border-gray-100 p-2.5">
                            <span class="block font-heading font-extrabold text-sm text-ink" x-text="inspectUser.badges_count"></span>
                            <span class="block text-[10px] text-muted">{{ $isEn ? 'Badges' : 'Lencana' }}</span>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-2.5">
                        <a
                            :href="inspectUser.profile_url"
                            class="flex-1 rounded-full bg-ink hover:bg-[#1E63E9] text-white font-heading font-bold text-xs py-2.5 text-center transition-colors"
                        >
                            {{ $isEn ? 'Open Full Profile ->' : 'Lihat Profil Lengkap ->' }}
                        </a>
                        @auth
                            <button
                                x-show="inspectUser.id !== {{ (int) Auth::id() }}"
                                type="button"
                                @click="const uid = inspectUser.id; inspectUser = null; window.dispatchEvent(new CustomEvent('open-chat-with', { detail: { userId: uid } }))"
                                class="rounded-full bg-[#1E63E9] hover:bg-ink text-white font-heading font-bold text-xs px-5 py-2.5 transition-colors cursor-pointer"
                            >
                                Chat
                            </button>
                        @endauth
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('digiSafeLeaderboard', (datasets, isEn) => ({
            categories: datasets.categories || [],
            activeKey: 'minigame_benar',
            rowLimit: 5,
            inspectUser: null,
            isEn,

            get currentCategory() {
                return this.categories.find((c) => c.key === this.activeKey) || this.categories[0] || { rows: [], dial_deg: -55 };
            },

            get visibleRows() {
                const rows = this.currentCategory.rows || [];
                return rows.slice(0, this.rowLimit);
            },

            get currentDialDeg() {
                return this.currentCategory.dial_deg ?? -55;
            },

            barStyle(idx) {
                const gradients = [
                    'background: linear-gradient(90deg, #F43F5E 0%, #FB7185 100%);',
                    'background: linear-gradient(90deg, #4F6EF7 0%, #60A5FA 100%);',
                    'background: linear-gradient(90deg, #22C55E 0%, #4ADE80 100%);',
                    'background: linear-gradient(90deg, #F59E0B 0%, #FBBF24 100%);',
                    'background: linear-gradient(90deg, #0EA5E9 0%, #38BDF8 100%);',
                    'background: linear-gradient(90deg, #14B8A6 0%, #2DD4BF 100%);',
                    'background: linear-gradient(90deg, #EC4899 0%, #F472B6 100%);',
                    'background: linear-gradient(90deg, #1D4ED8 0%, #3B82F6 100%);',
                ];
                return gradients[idx % gradients.length];
            },

            switchLimit(limit) {
                if (this.rowLimit === limit) return;
                this.animateReorder(() => {
                    this.rowLimit = limit;
                });
            },

            switchCategory(nextKey) {
                if (this.activeKey === nextKey) return;
                this.animateReorder(() => {
                    this.activeKey = nextKey;
                });
            },

            animateReorder(mutateFn) {
                // FLIP Step 1: Catat posisi Y setiap user sebelum pergantian kategori
                const oldTops = {};
                this.$el.querySelectorAll('[data-lb-uid]').forEach((el) => {
                    oldTops[el.dataset.lbUid] = el.getBoundingClientRect().top;
                });

                mutateFn();

                // FLIP Step 2 & 3: Hitung selisih perpindahan peringkat (membalap naik / turun)
                this.$nextTick(() => {
                    const cards = this.$el.querySelectorAll('[data-lb-uid]');
                    cards.forEach((el) => {
                        const uid = el.dataset.lbUid;
                        const newTop = el.getBoundingClientRect().top;
                        el.style.transition = 'none';

                        if (oldTops[uid] !== undefined) {
                            const dy = oldTops[uid] - newTop;
                            if (Math.abs(dy) > 1) {
                                // Card yang naik peringkat membalap di layer depan (zIndex lebih tinggi + sedikit scale up)
                                const climbing = dy > 0;
                                el.style.zIndex = climbing ? '35' : '10';
                                el.style.transform = `translateY(${dy}px) scale(${climbing ? 1.035 : 0.97})`;
                                el.style.opacity = climbing ? '1' : '0.86';
                            }
                        } else {
                            // Pemain baru masuk ke top list meluncur halus dari kiri
                            el.style.transform = 'translateX(-24px) scale(0.97)';
                            el.style.opacity = '0';
                        }

                        // Paksa reflow lalu mainkan transisi halus ke posisi baru
                        void el.offsetHeight;
                        el.style.transition = 'transform 680ms cubic-bezier(0.22, 1, 0.36, 1), opacity 480ms ease';
                        el.style.transform = 'translate3d(0, 0, 0) scale(1)';
                        el.style.opacity = '1';

                        setTimeout(() => {
                            el.style.zIndex = '';
                            el.style.transition = '';
                            el.style.transform = '';
                        }, 700);
                    });
                });
            },
        }));
    });
</script>

