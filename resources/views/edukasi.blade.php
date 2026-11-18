@extends('layouts.app')

@section('title', 'Edukasi Keamanan Digital — Digi-Safe Purwakarta')

@section('content')

    <!-- hero background 4K galeri 3D minimalis dengan state modal teks manual -->
    <section
        x-data="{ modalOpen: false, activeModul: null, openTeks(card) { this.activeModul = card; this.modalOpen = true; } }"
        class="relative min-h-screen pt-28 pb-20 px-4 sm:px-6 lg:px-8 flex flex-col justify-between bg-no-repeat bg-center bg-contain lg:bg-cover bg-fixed"
        style="background-color: #FFFFFF; background-image: url('{{ asset('images/edu-bg-gallery.jpg') }}');"
    >

        <div class="relative z-10 max-w-6xl w-full mx-auto">

            <!-- bar status atas bergaya referensi -->
            <div class="reveal flex flex-wrap items-center justify-between gap-4 mb-10">
                <!-- kapsul informasi utama -->
                <div class="inline-flex items-center gap-3.5 rounded-full bg-white/95 border border-gray-100 shadow-neutral px-4 py-2 text-ink">
                    <span class="w-7 h-7 rounded-full bg-lime text-ink flex items-center justify-center font-heading font-bold text-xs">
                        DS
                    </span>
                    <div class="text-left leading-tight pr-2">
                        <span class="font-heading font-bold text-xs text-ink block">{{ __('ui.edu.center_title') }}</span>
                        <span class="text-[11px] text-muted block">{{ __('ui.edu.center_sub') }}</span>
                    </div>
                    <span class="rounded-full bg-gray-100 text-ink font-semibold text-[11px] px-2.5 py-1">
                        {{ __('ui.edu.materials_count', ['count' => count($cards)]) }}
                    </span>
                </div>

                <div class="hidden sm:inline-flex items-center gap-1 rounded-full bg-white/95 border border-gray-200/80 shadow-neutral p-1 text-xs">
                    <span class="px-3 py-1.5 rounded-full text-muted font-medium">{{ __('ui.edu.catalog') }}</span>
                    <span class="px-3.5 py-1.5 rounded-full bg-ink text-white font-semibold shadow-2xs">{{ __('ui.edu.education') }}</span>
                </div>
            </div>

            <!-- grid kartu edukasi asimetris (bento grid adaptif 1:1 saat hover) -->
            <div class="flex flex-col gap-4 sm:gap-5">

                @foreach (array_chunk($cards, 3) as $rowIdx => $rowCards)
                    @php
                        $rowPattern = ($rowIdx % 2 === 0) ? [5, 4, 3] : [3, 4, 5];
                        $countInRow = count($rowCards);
                        if ($countInRow === 2) {
                            $baseWeights = [6, 6];
                        } elseif ($countInRow === 1) {
                            $baseWeights = [12];
                        } else {
                            $baseWeights = array_slice($rowPattern, 0, $countInRow);
                        }
                        $totalBase = array_sum($baseWeights);
                        $squareFlex = 3.62;

                        $hoverMatrix = [];
                        foreach ($baseWeights as $hIdx => $hw) {
                            $rowMap = [];
                            if ($countInRow === 1) {
                                $rowMap[$hIdx] = 3.62;
                            } else {
                                $otherSum = max(1, $totalBase - $hw);
                                $remaining = max(2, $totalBase - $squareFlex);
                                foreach ($baseWeights as $cIdx => $cw) {
                                    $rowMap[$cIdx] = ($cIdx === $hIdx)
                                        ? $squareFlex
                                        : round($remaining * ($cw / $otherSum), 2);
                                }
                            }
                            $hoverMatrix[$hIdx] = $rowMap;
                        }
                    @endphp

                    <div
                        x-data="{
                            hoveredCol: null,
                            base: @js($baseWeights),
                            matrix: @js($hoverMatrix),
                            flexFor(idx) {
                                return this.hoveredCol === null ? this.base[idx] : this.matrix[this.hoveredCol][idx];
                            }
                        }"
                        @mouseleave="hoveredCol = null"
                        :class="{ 'row-hovered': hoveredCol !== null }"
                        class="reveal edu-bento-row flex flex-col lg:flex-row gap-4 sm:gap-5 items-stretch"
                    >
                @foreach ($rowCards as $colIdx => $card)
                    @php
                        $surfaceClass = match ($card['variant']) {
                            'glass' => 'bento-glass text-white',
                            'glass-dark' => 'bento-glass-olive text-white',
                            default => 'bento-card-white text-ink',
                        };

                        $isLightText = in_array($card['variant'], ['glass', 'glass-dark']);
                        $isTeks = ($card['type'] ?? 'link') === 'teks';
                    @endphp

                    @if ($isTeks)
                        <button
                            type="button"
                            @click="openTeks(@js($card))"
                            @mouseenter="hoveredCol = {{ $colIdx }}"
                            @focusin="hoveredCol = {{ $colIdx }}"
                            @focusout="if (!$el.parentElement.contains($event.relatedTarget)) hoveredCol = null"
                            :style="'--edu-flex: ' + flexFor({{ $colIdx }})"
                            :class="{ 'is-hovered': hoveredCol === {{ $colIdx }} }"
                            class="text-left cursor-pointer {{ $surfaceClass }} edu-card-fluid group rounded-[24px] p-6 sm:p-7 flex flex-col justify-between min-h-[230px] lg:min-h-0 focus:outline-none focus:ring-2 focus:ring-lime overflow-hidden"
                        >
                    @else
                        <a
                            href="{{ $card['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            @mouseenter="hoveredCol = {{ $colIdx }}"
                            @focusin="hoveredCol = {{ $colIdx }}"
                            @focusout="if (!$el.parentElement.contains($event.relatedTarget)) hoveredCol = null"
                            :style="'--edu-flex: ' + flexFor({{ $colIdx }})"
                            :class="{ 'is-hovered': hoveredCol === {{ $colIdx }} }"
                            class="{{ $surfaceClass }} edu-card-fluid group rounded-[24px] p-6 sm:p-7 flex flex-col justify-between min-h-[230px] lg:min-h-0 focus:outline-none focus:ring-2 focus:ring-lime overflow-hidden"
                        >
                    @endif

                        <!-- bagian atas kartu: judul, deskripsi, dan ikon lini -->
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <span class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider mb-2 {{ $isLightText ? 'bg-white/20 text-white' : ($isTeks ? 'bg-lime/40 text-ink' : 'bg-sky/10 text-sky') }}">
                                        {{ $isTeks ? __('ui.edu.read_module') : __('ui.edu.external_link') }}
                                    </span>
                                    <h2 class="font-heading font-bold text-lg sm:text-xl leading-snug tracking-[-0.02em] max-w-[260px] {{ $isLightText ? 'text-white' : 'text-ink group-hover:text-sky transition-colors' }}">
                                        {{ $card['title'] }}
                                    </h2>
                                </div>

                                <span class="w-8 h-8 rounded-full {{ $isLightText ? 'bg-white/15 text-white' : 'bg-gray-100 text-muted group-hover:bg-lime group-hover:text-ink' }} flex items-center justify-center shrink-0 transition-colors">
                                    @switch($card['icon'])
                                        @case('journal')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                            </svg>
                                            @break
                                        @case('shield')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                                <path d="M9 12l2 2 4-4" />
                                            </svg>
                                            @break
                                        @case('check')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="9" />
                                                <path d="M9 12l2 2 4-4" />
                                            </svg>
                                            @break
                                        @case('globe')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="3" y1="12" x2="21" y2="12" />
                                                <path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18" />
                                            </svg>
                                            @break
                                        @case('users')
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            </svg>
                                            @break
                                        @default
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                    @endswitch
                                </span>
                            </div>

                            <p class="mt-2 text-xs sm:text-sm leading-relaxed {{ $isLightText ? 'text-white/80' : 'text-muted' }}">
                                {{ $card['description'] }}
                            </p>
                        </div>

                        <!-- elemen visual tengah sesuai variasi kartu referensi -->
                        @if ($card['variant'] === 'chart')
                            <div class="my-5">
                                <svg class="w-full h-10" viewBox="0 0 220 36" fill="none" aria-hidden="true">
                                    @for ($bar = 0; $bar < 26; $bar++)
                                        @php
                                            $heights = [14, 24, 18, 30, 22, 16, 28, 34, 20, 26, 15, 22, 12, 18, 10, 16, 22, 14, 12, 18, 10, 14, 20, 12, 16, 10];
                                            $bh = $heights[$bar];
                                            $by = 34 - $bh;
                                            $bx = $bar * 8.4 + 2;
                                            $activeBar = $bar < 12;
                                        @endphp
                                        <rect
                                            x="{{ $bx }}"
                                            y="{{ $by }}"
                                            width="3.2"
                                            height="{{ $bh }}"
                                            rx="1.6"
                                            fill="{{ $activeBar ? '#1E88E5' : '#E5E7EB' }}"
                                        />
                                    @endfor
                                </svg>
                            </div>
                        @elseif ($card['variant'] === 'featured')
                            <div class="my-4 grid grid-cols-2 gap-2.5">
                                <div class="rounded-xl bg-sky/10 border border-sky/15 p-3 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] font-semibold text-sky block uppercase tracking-wider">{{ __('ui.edu.literacy') }}</span>
                                        <span class="text-xs font-bold text-ink mt-0.5 block">{{ __('ui.edu.healthy_tech') }}</span>
                                    </div>
                                    <span class="w-6 h-6 rounded-full bg-white text-sky flex items-center justify-center text-xs font-bold shadow-2xs">+</span>
                                </div>
                                <div class="rounded-xl bg-lime/35 border border-lime p-3 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] font-semibold text-ink/70 block uppercase tracking-wider">{{ __('ui.edu.protection') }}</span>
                                        <span class="text-xs font-bold text-ink mt-0.5 block">{{ __('ui.edu.public_space') }}</span>
                                    </div>
                                    <span class="w-6 h-6 rounded-full bg-ink text-lime flex items-center justify-center text-xs font-bold">✓</span>
                                </div>
                            </div>
                        @endif

                        <!-- bagian bawah kartu: badge kategori, statistik kecil, dan indikator panah kanan bawah -->
                        <div class="pt-5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center -space-x-1.5">
                                    <span class="w-7 h-7 rounded-full {{ $isLightText ? 'bg-white/25 text-white ring-1 ring-white/40' : 'bg-sky/15 text-sky ring-2 ring-white' }} flex items-center justify-center text-[10px] font-bold">
                                        DS
                                    </span>
                                    <span class="w-7 h-7 rounded-full {{ $isLightText ? 'bg-lime text-ink ring-1 ring-white/40' : 'bg-ink text-white ring-2 ring-white' }} flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                    </span>
                                </div>
                                <span class="text-[11px] font-medium {{ $isLightText ? 'text-white/85' : 'text-muted' }}">
                                    {{ $card['tag'] }}
                                </span>
                            </div>

                            <!-- angka statistik & panah kanan bawah -->
                            <div class="flex items-center gap-2">
                                <span class="w-9 h-9 rounded-full {{ $isLightText ? 'bg-white/20 text-white' : 'bg-gray-100 text-ink' }} flex items-center justify-center text-xs font-bold">
                                    {{ $card['stat'] }}
                                </span>
                                <span class="w-9 h-9 rounded-full {{ $isLightText ? 'bg-white text-ink group-hover:bg-lime' : 'bg-ink text-white group-hover:bg-lime group-hover:text-ink' }} flex items-center justify-center transition-colors">
                                    @if ($isTeks)
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="7" y1="17" x2="17" y2="7" />
                                            <polyline points="7 7 17 7 17 17" />
                                        </svg>
                                    @endif
                                </span>
                            </div>
                        </div>

                    @if ($isTeks)
                        </button>
                    @else
                        </a>
                    @endif
                @endforeach
                    </div>
                @endforeach

            </div>

            <!-- bar bawah kecil terinspirasi referensi -->
            <div class="reveal mt-10 flex justify-center">
                <div class="inline-flex items-center gap-3 rounded-full bento-glass-olive px-5 py-2.5 text-white text-xs">
                    <div class="flex items-center -space-x-2">
                        <span class="w-7 h-7 rounded-full bg-white text-ink font-bold text-[10px] flex items-center justify-center ring-2 ring-white/30">IT</span>
                        <span class="w-7 h-7 rounded-full bg-sky text-white font-bold text-[10px] flex items-center justify-center ring-2 ring-white/30">MF</span>
                        <span class="w-7 h-7 rounded-full bg-lime text-ink font-bold text-[10px] flex items-center justify-center ring-2 ring-white/30">LD</span>
                    </div>
                    <span class="font-medium text-white/90">{{ __('ui.edu.verified_sources') }}</span>
                    <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-bold">+{{ count($cards) }}</span>
                </div>
            </div>

        </div>

        <!-- pop-up modal baca modul tipe teks manual -->
        <div
            x-show="modalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(10, 10, 10, 0.55);"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            @keydown.escape.window="modalOpen = false"
        >
            <div
                @click.outside="modalOpen = false"
                class="w-full max-w-2xl max-h-[85vh] rounded-[28px] bg-white border border-gray-100 shadow-neutral flex flex-col overflow-hidden"
            >
                <div class="p-6 border-b border-gray-100 flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="rounded-full bg-lime px-3 py-0.5 text-[10px] font-extrabold text-ink uppercase tracking-wider">
                                {{ __('ui.edu.read_module') }}
                            </span>
                            <span class="rounded-full bg-sky/10 text-sky px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider" x-text="activeModul ? activeModul.tag : ''"></span>
                        </div>
                        <h3 class="font-heading font-extrabold text-lg sm:text-xl text-ink leading-snug" x-text="activeModul ? activeModul.title : ''"></h3>
                    </div>

                    <button
                        type="button"
                        @click="modalOpen = false"
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center shrink-0 transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 sm:p-7 overflow-y-auto space-y-4">
                    <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-5 text-xs sm:text-sm text-ink leading-relaxed whitespace-pre-line" x-text="activeModul ? activeModul.konten : ''"></div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] text-muted font-medium">{{ __('ui.edu.curriculum_footer') }}</span>
                    <button
                        type="button"
                        @click="modalOpen = false"
                        class="rounded-full bg-ink text-white hover:bg-sky font-heading font-bold text-xs px-5 py-2 transition-colors cursor-pointer"
                    >
                        {{ __('ui.edu.finish_reading') }}
                    </button>
                </div>
            </div>
        </div>

    </section>

@endsection
