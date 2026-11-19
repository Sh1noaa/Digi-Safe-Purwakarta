@extends('layouts.app')

@section('title', 'Edukasi Keamanan Digital - Digi-Safe Purwakarta')

@section('content')

    @php
        $isEnEdu = app()->getLocale() === 'en';
        $botDigikaId = (int) (\App\Models\User::where('email', \App\Models\User::BOT_DIGIKA_EMAIL)->value('id') ?? 0);
        $latestDecision = ($myRequests ?? collect())->first(fn ($r) => in_array($r->status, ['approved', 'rejected'], true) && ! $r->user_notified_read);
    @endphp

    <!-- hero background 4K galeri 3D minimalis dengan state modal teks manual & request tema edukasi -->
    <section
        x-data="{
            modalOpen: false,
            activeModul: null,
            requestModalOpen: {{ $errors->has('tema') || $errors->has('deskripsi') || $errors->has('kategori') ? 'true' : 'false' }},
            requestTab: 'form',
            unreadDecisionCount: {{ (int) ($unreadDecisionsCount ?? 0) }},
            botDigikaId: {{ $botDigikaId }},
            openTeks(card) { this.activeModul = card; this.modalOpen = true; },
            async markDecisionsSeen() {
                this.unreadDecisionCount = 0;
                const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                try {
                    await fetch('{{ route('edukasi.request.seen') }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                } catch (_) {}
            },
            openBotMessage() {
                this.markDecisionsSeen();
                if (this.botDigikaId > 0) {
                    window.dispatchEvent(new CustomEvent('open-chat-with', { detail: { userId: this.botDigikaId } }));
                }
            }
        }"
        class="edu-section-surface relative min-h-screen pt-28 pb-20 px-4 sm:px-6 lg:px-8 flex flex-col justify-between bg-no-repeat bg-center bg-cover bg-scroll lg:bg-fixed"
        style="background-color: #FFFFFF; background-image: url('{{ asset('images/edu-bg-gallery.jpg') }}');"
    >

        <div class="relative z-10 max-w-6xl w-full mx-auto">

            <!-- bar status atas bergaya referensi + tombol Request Tema Edukasi -->
            <div class="reveal flex flex-wrap items-center justify-between gap-4 mb-6 sm:mb-8">
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

                <div class="flex flex-wrap items-center gap-2.5">
                    <button
                        type="button"
                        @click="requestModalOpen = true; requestTab = unreadDecisionCount > 0 ? 'history' : 'form';"
                        class="relative inline-flex items-center gap-2 rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-4 sm:px-5 py-2.5 shadow-neutral transition-all hover:-translate-y-0.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-lime shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                        </svg>
                        <span>{{ __('ui.edu.request_btn') }}</span>
                        <span
                            x-show="unreadDecisionCount > 0"
                            x-text="unreadDecisionCount"
                            style="display: none;"
                            class="min-w-[18px] h-[18px] px-1 rounded-full bg-lime text-ink text-[10px] font-extrabold flex items-center justify-center"
                        ></span>
                    </button>

                    <div class="hidden sm:inline-flex items-center gap-1 rounded-full bg-white/95 border border-gray-200/80 shadow-neutral p-1 text-xs">
                        <span class="px-3 py-1.5 rounded-full text-muted font-medium">{{ __('ui.edu.catalog') }}</span>
                        <span class="px-3.5 py-1.5 rounded-full bg-ink text-white font-semibold shadow-2xs">{{ __('ui.edu.education') }}</span>
                    </div>
                </div>
            </div>

            @if ($latestDecision)
                <div
                    x-show="unreadDecisionCount > 0"
                    class="mb-7 rounded-2xl bg-white/95 border {{ $latestDecision->status === 'approved' ? 'border-emerald-300' : 'border-amber-300' }} p-4 sm:p-5 shadow-neutral flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="flex items-start gap-3">
                        <img src="{{ asset('images/bot-digika.jpg') }}" alt="Bot Digika" class="w-11 h-11 rounded-full object-cover ring-2 {{ $latestDecision->status === 'approved' ? 'ring-emerald-400/70' : 'ring-amber-400/70' }} bg-white shrink-0 shadow-xs">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider {{ $latestDecision->status === 'approved' ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $isEnEdu ? 'Notification from Bot Digika' : 'Notifikasi Evaluasi • Bot Digika' }}
                                </span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $latestDecision->status === 'approved' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                                    {{ $latestDecision->status === 'approved' ? ($isEnEdu ? 'Approved' : 'Disetujui') : ($isEnEdu ? 'Rejected' : 'Ditolak') }}
                                </span>
                            </div>
                            <p class="mt-1 font-heading font-bold text-xs sm:text-sm text-ink">
                                "{{ $latestDecision->tema }}"
                            </p>
                            <p class="mt-0.5 text-[11px] text-muted">
                                {{ $latestDecision->status === 'approved'
                                    ? ($isEnEdu ? 'Admin approved your topic request! Bot Digika sent an automated message to your inbox.' : 'Admin menyetujui usulan tema Anda! Bot Digika telah mengirim pesan otomatis ke kotak pesan Anda.')
                                    : ($isEnEdu ? 'Your topic request was reviewed. Open Bot Digika messages to read the evaluation details.' : 'Usulan tema Anda telah dievaluasi. Buka pesan Bot Digika untuk melihat keterangan lengkapnya.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            @click="openBotMessage()"
                            class="rounded-full bg-[#1E63E9] hover:bg-ink text-white font-heading font-bold text-xs px-4 py-2 transition-colors cursor-pointer"
                        >
                            {{ $isEnEdu ? 'Open Bot Digika Message' : 'Buka Pesan Bot Digika' }}
                        </button>
                        <button
                            type="button"
                            @click="markDecisionsSeen()"
                            class="rounded-full bg-gray-100 hover:bg-gray-200 text-ink font-semibold text-xs px-3 py-2 cursor-pointer"
                        >
                            {{ $isEnEdu ? 'Dismiss' : 'Tutup' }}
                        </button>
                    </div>
                </div>
            @endif

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
                        class="reveal edu-bento-row grid grid-cols-1 md:grid-cols-2 lg:flex lg:flex-row gap-4 sm:gap-5 items-stretch"
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

            <!-- bar bawah kecil + ajakan request tema -->
            <div class="reveal mt-10 flex flex-wrap items-center justify-center gap-3">
                <div class="inline-flex items-center gap-3 rounded-full bento-glass-olive px-5 py-2.5 text-white text-xs">
                    <div class="flex items-center -space-x-2">
                        <span class="w-7 h-7 rounded-full bg-white text-ink font-bold text-[10px] flex items-center justify-center ring-2 ring-white/30">IT</span>
                        <span class="w-7 h-7 rounded-full bg-sky text-white font-bold text-[10px] flex items-center justify-center ring-2 ring-white/30">MF</span>
                        <span class="w-7 h-7 rounded-full bg-lime text-ink font-bold text-[10px] flex items-center justify-center ring-2 ring-white/30">LD</span>
                    </div>
                    <span class="font-medium text-white/90">{{ __('ui.edu.verified_sources') }}</span>
                    <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-bold">+{{ count($cards) }}</span>
                </div>

                <button
                    type="button"
                    @click="requestModalOpen = true; requestTab = 'form';"
                    class="inline-flex items-center gap-2 rounded-full bg-lime hover:bg-sky text-ink hover:text-white font-heading font-extrabold text-xs px-5 py-2.5 shadow-neutral transition-colors cursor-pointer"
                >
                    <span>+ {{ __('ui.edu.request_btn') }}</span>
                </button>
            </div>

        </div>

        <!-- pop-up modal Request Tema Edukasi & Riwayat Evaluasi -->
        <div
            x-show="requestModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(10, 10, 10, 0.58);"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            @keydown.escape.window="requestModalOpen = false"
        >
            <div
                @click.outside="requestModalOpen = false"
                class="w-full max-w-xl max-h-[88vh] rounded-[28px] bg-white border border-gray-100 shadow-neutral flex flex-col overflow-hidden"
            >
                <div class="p-5 sm:p-6 border-b border-gray-100 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="relative shrink-0 mt-0.5">
                            <img src="{{ asset('images/bot-digika.jpg') }}" alt="Bot Digika" class="w-11 h-11 rounded-full object-cover ring-2 ring-sky/30 bg-white shadow-xs">
                            <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-sky/10 text-sky px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider mb-1">
                                Bot Digika Sync
                            </span>
                            <h3 class="font-heading font-extrabold text-lg text-ink leading-snug">{{ __('ui.edu.request_title') }}</h3>
                            <p class="mt-0.5 text-xs text-muted leading-relaxed">{{ __('ui.edu.request_sub') }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="requestModalOpen = false"
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center shrink-0 cursor-pointer"
                    >
                        &times;
                    </button>
                </div>

                @guest
                    <div class="p-7 text-center space-y-4 bg-[#F8FAFC]">
                        <img src="{{ asset('images/bot-digika.jpg') }}" alt="Bot Digika" class="w-16 h-16 rounded-full object-cover ring-4 ring-sky/20 bg-white mx-auto shadow-sm">
                        <div>
                            <h4 class="font-heading font-extrabold text-base text-ink">
                                {{ $isEnEdu ? 'Sign in to Request a Topic' : 'Masuk untuk Mengajukan Tema Edukasi' }}
                            </h4>
                            <p class="mt-1.5 text-xs text-muted max-w-sm mx-auto leading-relaxed">
                                {{ $isEnEdu
                                    ? 'Sign in so our Admin team can track your suggestion and Bot Digika can send the approval notification directly to your inbox.'
                                    : 'Masuk terlebih dahulu agar Admin dapat mengevaluasi usulan Anda dan Bot Digika bisa mengirimkan notifikasi persetujuan/penolakan langsung ke pesan Anda.' }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center gap-2.5">
                            <a href="{{ route('login') }}" class="rounded-full bg-[#1E63E9] hover:bg-ink text-white font-heading font-bold text-xs px-5 py-2.5 transition-colors">
                                {{ $isEnEdu ? 'Sign In' : 'Masuk Sekarang' }}
                            </a>
                            <a href="{{ route('register') }}" class="rounded-full bg-lime text-ink font-heading font-bold text-xs px-5 py-2.5 transition-colors">
                                {{ $isEnEdu ? 'Register' : 'Daftar Akun' }}
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Tab Form vs Riwayat -->
                    <div class="px-5 py-2.5 bg-[#F8FAFC] border-b border-gray-100 grid grid-cols-2 gap-2 shrink-0">
                        <button
                            type="button"
                            @click="requestTab = 'form'"
                            :class="requestTab === 'form' ? 'bg-white text-ink shadow-2xs border-gray-200' : 'text-muted border-transparent hover:text-ink'"
                            class="rounded-xl border py-2 text-xs font-heading font-bold cursor-pointer transition-all"
                        >
                            {{ $isEnEdu ? 'Submit New Request' : 'Ajukan Tema Baru' }}
                        </button>
                        <button
                            type="button"
                            @click="requestTab = 'history'; markDecisionsSeen();"
                            :class="requestTab === 'history' ? 'bg-white text-ink shadow-2xs border-gray-200' : 'text-muted border-transparent hover:text-ink'"
                            class="rounded-xl border py-2 text-xs font-heading font-bold flex items-center justify-center gap-1.5 cursor-pointer transition-all"
                        >
                            <span>{{ $isEnEdu ? 'My Requests' : 'Status Request Saya' }}</span>
                            <span class="rounded-full bg-sky/15 text-sky px-2 py-0.5 text-[10px] font-extrabold">{{ ($myRequests ?? collect())->count() }}</span>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-5 sm:p-6">
                        <form
                            x-show="requestTab === 'form'"
                            x-data="{
                                selectedKategori: @js(old('kategori', 'Anti-Phishing')),
                                selectedType: @js(old('preferred_type', 'teks'))
                            }"
                            action="{{ route('edukasi.request.store') }}"
                            method="POST"
                            class="space-y-4"
                        >
                            @csrf

                            @if ($errors->any())
                                <div class="rounded-xl bg-red-50 border border-red-200 px-3.5 py-2.5 text-xs font-semibold text-red-600">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-bold text-ink mb-1.5">
                                    {{ $isEnEdu ? 'Requested Topic / Theme Title' : 'Tema / Judul Materi yang Diinginkan' }}
                                </label>
                                <input
                                    type="text"
                                    name="tema"
                                    value="{{ old('tema') }}"
                                    required
                                    maxlength="180"
                                    placeholder="{{ $isEnEdu ? 'e.g. How to spot fake QRIS payment scams' : 'Contoh: Cara mengenali modus penipuan QRIS palsu & pinjol ilegal' }}"
                                    class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                                >
                            </div>

                            <!-- Pilihan Kategori Topik Berupa Kartu/Pill Interaktif -->
                            <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <label class="block text-xs font-bold text-ink">
                                        {{ $isEnEdu ? 'Select Topic Category' : 'Pilih Kategori Topik' }}
                                    </label>
                                    <span class="rounded-full bg-sky/10 text-sky px-2 py-0.5 text-[10px] font-bold" x-text="selectedKategori"></span>
                                </div>
                                <input type="hidden" name="kategori" :value="selectedKategori">

                                @php
                                    $eduCatOptions = [
                                        ['value' => 'Anti-Phishing', 'label' => $isEnEdu ? 'Anti-Phishing & Scams' : 'Anti-Phishing & Penipuan', 'tone' => 'sky'],
                                        ['value' => 'Keamanan Akun', 'label' => $isEnEdu ? 'Account & 2FA' : 'Keamanan Akun & 2FA', 'tone' => 'emerald'],
                                        ['value' => 'Privasi Data', 'label' => $isEnEdu ? 'Data Privacy' : 'Privasi & Data Pribadi', 'tone' => 'indigo'],
                                        ['value' => 'Anti-Hoaks', 'label' => $isEnEdu ? 'Fact Check & Hoaxes' : 'Cek Fakta & Anti-Hoaks', 'tone' => 'amber'],
                                        ['value' => 'Panduan Publik', 'label' => $isEnEdu ? 'General Public Guide' : 'Panduan Publik Lainnya', 'tone' => 'lime'],
                                    ];
                                @endphp
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($eduCatOptions as $catOpt)
                                        <button
                                            type="button"
                                            @click="selectedKategori = @js($catOpt['value'])"
                                            :class="selectedKategori === @js($catOpt['value'])
                                                ? 'bg-ink text-white border-ink shadow-xs'
                                                : 'bg-white text-ink/80 border-gray-200 hover:border-sky/60 hover:text-sky'"
                                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[11px] font-heading font-bold transition-all cursor-pointer"
                                        >
                                            <span
                                                :class="selectedKategori === @js($catOpt['value']) ? 'bg-lime' : 'bg-sky/40'"
                                                class="w-1.5 h-1.5 rounded-full shrink-0"
                                            ></span>
                                            <span>{{ $catOpt['label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Pilihan Format Materi Harapan (2 Kartu Visual) -->
                            <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-3.5 space-y-2.5">
                                <label class="block text-xs font-bold text-ink">
                                    {{ $isEnEdu ? 'Preferred Material Format' : 'Format Materi Harapan' }}
                                </label>
                                <input type="hidden" name="preferred_type" :value="selectedType">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <button
                                        type="button"
                                        @click="selectedType = 'teks'"
                                        :class="selectedType === 'teks'
                                            ? 'bg-[#EFF6FF] border-sky ring-1 ring-sky/40 shadow-2xs'
                                            : 'bg-white border-gray-200 hover:border-sky/50'"
                                        class="rounded-xl border p-3 text-left flex items-start gap-2.5 transition-all cursor-pointer"
                                    >
                                        <span
                                            :class="selectedType === 'teks' ? 'bg-lime text-ink' : 'bg-gray-100 text-muted'"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                                        >
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                <polyline points="14 2 14 8 20 8" />
                                                <line x1="16" y1="13" x2="8" y2="13" />
                                                <line x1="16" y1="17" x2="8" y2="17" />
                                            </svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-heading font-bold text-xs text-ink block truncate">
                                                    {{ $isEnEdu ? 'Reading Guide' : 'Modul Teks Bacaan' }}
                                                </span>
                                                <span x-show="selectedType === 'teks'" class="text-[10px] font-extrabold text-sky">✓</span>
                                            </div>
                                            <span class="text-[10px] text-muted leading-snug block mt-0.5">
                                                {{ $isEnEdu ? 'Read directly inside Digi-Safe pop-up' : 'Dibaca langsung lewat pop-up materi' }}
                                            </span>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectedType = 'link'"
                                        :class="selectedType === 'link'
                                            ? 'bg-[#EFF6FF] border-sky ring-1 ring-sky/40 shadow-2xs'
                                            : 'bg-white border-gray-200 hover:border-sky/50'"
                                        class="rounded-xl border p-3 text-left flex items-start gap-2.5 transition-all cursor-pointer"
                                    >
                                        <span
                                            :class="selectedType === 'link' ? 'bg-sky text-white' : 'bg-gray-100 text-muted'"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                                        >
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                            </svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-heading font-bold text-xs text-ink block truncate">
                                                    {{ $isEnEdu ? 'Reference Link' : 'Rujukan Link Resmi' }}
                                                </span>
                                                <span x-show="selectedType === 'link'" class="text-[10px] font-extrabold text-sky">✓</span>
                                            </div>
                                            <span class="text-[10px] text-muted leading-snug block mt-0.5">
                                                {{ $isEnEdu ? 'Curated national portal or journal' : 'Tautan portal literasi / jurnal resmi' }}
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-ink mb-1.5">
                                    {{ $isEnEdu ? 'Why is this topic helpful?' : 'Penjelasan / Poin yang Ingin Dibahas' }}
                                </label>
                                <textarea
                                    name="deskripsi"
                                    rows="3"
                                    required
                                    maxlength="1000"
                                    placeholder="{{ $isEnEdu ? 'Describe the cyber issue or questions citizens often face about this topic...' : 'Jelaskan kasus atau poin-poin penting yang menurut Anda perlu dijelaskan dalam modul ini...' }}"
                                    class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky resize-none"
                                >{{ old('deskripsi') }}</textarea>
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-2.5">
                                <button
                                    type="button"
                                    @click="requestModalOpen = false"
                                    class="rounded-full border border-gray-200 bg-white px-5 py-2.5 text-xs font-semibold text-muted hover:text-ink cursor-pointer"
                                >
                                    {{ __('ui.edu.close') }}
                                </button>
                                <button
                                    type="submit"
                                    class="rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-6 py-2.5 shadow-xs transition-colors cursor-pointer"
                                >
                                    {{ $isEnEdu ? 'Send Request' : 'Kirim Request Tema' }}
                                </button>
                            </div>
                        </form>

                        <!-- Tab Riwayat Request -->
                        <div x-show="requestTab === 'history'" style="display: none;" class="space-y-3">
                            @forelse ($myRequests ?? collect() as $req)
                                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4 space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-sky">{{ $req->kategori }}</span>
                                            <h4 class="font-heading font-bold text-xs sm:text-sm text-ink mt-0.5">{{ $req->tema }}</h4>
                                        </div>
                                        @if ($req->status === 'approved')
                                            <span class="shrink-0 rounded-full bg-emerald-100 text-emerald-700 px-2.5 py-0.5 text-[10px] font-extrabold">
                                                {{ $isEnEdu ? 'Approved' : 'Disetujui ✓' }}
                                            </span>
                                        @elseif ($req->status === 'rejected')
                                            <span class="shrink-0 rounded-full bg-rose-100 text-rose-700 px-2.5 py-0.5 text-[10px] font-extrabold">
                                                {{ $isEnEdu ? 'Declined' : 'Ditolak' }}
                                            </span>
                                        @else
                                            <span class="shrink-0 rounded-full bg-amber-100 text-amber-800 px-2.5 py-0.5 text-[10px] font-extrabold">
                                                {{ $isEnEdu ? 'Pending Review' : 'Menunggu Evaluasi' }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-[11px] text-muted leading-relaxed">{{ $req->deskripsi }}</p>

                                    @if (filled($req->admin_note))
                                        <p class="rounded-xl bg-white border border-gray-200/80 px-3 py-2 text-[11px] text-ink">
                                            <strong>Catatan Admin:</strong> {{ $req->admin_note }}
                                        </p>
                                    @endif

                                    <div class="pt-1 flex items-center justify-between text-[10px] text-muted">
                                        <span>{{ $req->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                                        @if ($req->status !== 'pending')
                                            <button
                                                type="button"
                                                @click="requestModalOpen = false; openBotMessage();"
                                                class="inline-flex items-center gap-1.5 font-bold text-[#1E63E9] hover:underline cursor-pointer"
                                            >
                                                <img src="{{ asset('images/bot-digika.jpg') }}" alt="Bot Digika" class="w-4 h-4 rounded-full object-cover bg-white">
                                                <span>{{ $isEnEdu ? 'View Bot Digika Message' : 'Lihat Pesan Bot Digika' }} &rarr;</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-10 text-center text-xs text-muted">
                                    {{ $isEnEdu ? 'You have not submitted any education topic requests yet.' : 'Anda belum pernah mengajukan request tema edukasi.' }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endguest
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
