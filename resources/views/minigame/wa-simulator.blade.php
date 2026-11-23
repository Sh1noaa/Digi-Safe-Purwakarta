@extends('layouts.app')

@section('title', app()->getLocale() === 'en' ? 'WhatsApp & .APK Scam Simulator - Digi-Safe Purwakarta' : 'Simulasi Chat WhatsApp & Penipuan .APK - Digi-Safe Purwakarta')

@section('content')
    @php
        $isEn = app()->getLocale() === 'en';
        $isDark = ($currentTheme ?? 'light') === 'dark';
    @endphp

    <section
        x-data="{
            contacts: @js($contacts),
            activeId: @js($contacts[0]['id'] ?? ''),
            search: '',
            filterStatus: 'all',
            mobileView: 'list',
            typing: false,
            messages: [],
            currentNode: 'start',
            activeEnding: null,
            progress: {},

            init() {
                try {
                    const saved = JSON.parse(localStorage.getItem('digisafe_wa_sim_progress_v1') || '{}');
                    if (saved && typeof saved === 'object') this.progress = saved;
                } catch (_) {}
                if (this.activeId) {
                    this.openContact(this.activeId, false);
                }
            },

            saveProgress() {
                try {
                    localStorage.setItem('digisafe_wa_sim_progress_v1', JSON.stringify(this.progress));
                } catch (_) {}
            },

            get activeContact() {
                return this.contacts.find(c => c.id === this.activeId) || this.contacts[0];
            },

            get filteredContacts() {
                const q = (this.search || '').toLowerCase().trim();
                return this.contacts.filter(c => {
                    const matchQ = !q
                        || c.name.toLowerCase().includes(q)
                        || c.category.toLowerCase().includes(q)
                        || c.preview.toLowerCase().includes(q);
                    if (!matchQ) return false;
                    const p = this.progress[c.id];
                    if (this.filterStatus === 'pending') return !p;
                    if (this.filterStatus === 'safe') return p && p.type === 'safe';
                    if (this.filterStatus === 'danger') return p && p.type !== 'safe';
                    return true;
                });
            },

            get safeCount() {
                return Object.values(this.progress).filter(p => p && p.type === 'safe').length;
            },

            get completedCount() {
                return Object.keys(this.progress).length;
            },

            get currentChoices() {
                if (this.activeEnding || this.typing || !this.activeContact) return [];
                const node = this.activeContact.nodes[this.currentNode];
                return node ? (node.choices || []) : [];
            },

            openContact(id, switchMobile = true) {
                this.activeId = id;
                this.currentNode = 'start';
                this.activeEnding = null;
                this.typing = false;
                this.messages = [];
                if (switchMobile) this.mobileView = 'chat';

                const contact = this.activeContact;
                if (!contact) return;

                const startNode = contact.nodes['start'];
                if (startNode && startNode.messages) {
                    startNode.messages.forEach((m) => {
                        this.messages.push({ sender: 'them', ...m });
                    });
                }
                this.scrollToBottom();
            },

            chooseOption(choice) {
                if (this.typing || this.activeEnding) return;
                const nowTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                this.messages.push({
                    sender: 'me',
                    text: choice.user_text,
                    time: nowTime
                });
                this.scrollToBottom();

                const nextKey = choice.next;
                const contact = this.activeContact;
                this.typing = true;

                setTimeout(() => {
                    this.typing = false;
                    if (contact.nodes[nextKey]) {
                        this.currentNode = nextKey;
                        const node = contact.nodes[nextKey];
                        (node.messages || []).forEach(m => {
                            this.messages.push({ sender: 'them', ...m });
                        });
                    } else if (contact.endings[nextKey]) {
                        this.activeEnding = contact.endings[nextKey];
                        this.progress[contact.id] = {
                            type: this.activeEnding.type,
                            score: this.activeEnding.score,
                            title: this.activeEnding.title
                        };
                        this.saveProgress();
                        this.syncScore(contact.id, this.activeEnding);
                    }
                    this.scrollToBottom();
                }, 650);
            },

            async syncScore(contactId, ending) {
                try {
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    await fetch('{{ route('minigame.wa-simulator.record') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            contact_id: contactId,
                            ending_type: ending.type,
                            score: ending.score
                        })
                    });
                } catch (_) {}
            },

            nextUnfinished() {
                const next = this.contacts.find(c => c.id !== this.activeId && !this.progress[c.id])
                    || this.contacts[(this.contacts.findIndex(c => c.id === this.activeId) + 1) % this.contacts.length];
                if (next) this.openContact(next.id, true);
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const el = this.$refs.chatScroll;
                    if (el) el.scrollTop = el.scrollHeight;
                });
            }
        }"
        class="min-h-screen pt-24 sm:pt-28 pb-16 px-3 sm:px-6 lg:px-8 {{ $isDark ? 'bg-[#0B1120]' : 'bg-[#F4F6FB]' }}"
    >
        <div class="max-w-7xl mx-auto space-y-5">

            <!-- Header Bar Ringkas -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('minigame.index') }}"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full border transition-colors {{ $isDark ? 'bg-slate-900 border-slate-700 text-slate-200 hover:border-sky' : 'bg-white border-gray-200 text-ink hover:border-sky' }}"
                        aria-label="{{ $isEn ? 'Back to Minigame Hub' : 'Kembali ke Minigame' }}"
                    >
                        &larr;
                    </a>
                    <div>
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-heading font-extrabold uppercase tracking-wider text-emerald-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $isEn ? 'Interactive Branching Chat Simulation' : 'Simulasi Chat Interaktif Multi-Ending' }}
                        </span>
                        <h1 class="font-heading font-extrabold text-xl sm:text-2xl {{ $isDark ? 'text-white' : 'text-ink' }}">
                            {{ $isEn ? 'WhatsApp & .APK Scam Simulator' : 'Simulasi Waspada Penipuan WhatsApp & File .APK' }}
                        </h1>
                    </div>
                </div>

                <!-- Statistik Progres Kontak -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="rounded-2xl border px-4 py-2 flex items-center gap-3 {{ $isDark ? 'bg-slate-900 border-slate-800' : 'bg-white border-gray-200/80 shadow-2xs' }}">
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-muted block">{{ $isEn ? 'Chats Solved Safely' : 'Kontak Diselesaikan Aman' }}</span>
                            <span class="font-heading font-extrabold text-sm {{ $isDark ? 'text-white' : 'text-ink' }}">
                                <span x-text="safeCount"></span> / <span x-text="contacts.length"></span> {{ $isEn ? 'Contacts' : 'Orang' }}
                            </span>
                        </div>
                        <span class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-600 flex items-center justify-center font-heading font-extrabold text-xs" x-text="Math.round((safeCount / Math.max(1, contacts.length)) * 100) + '%'"></span>
                    </div>
                </div>
            </div>

            <!-- Frame Utama Bergaya WhatsApp (2 Kolom: Daftar Kontak Kiri + Ruang Chat Kanan) -->
            <div class="rounded-[26px] overflow-hidden border shadow-neutral grid grid-cols-1 lg:grid-cols-12 min-h-[640px] lg:h-[720px] {{ $isDark ? 'bg-[#111B21] border-slate-800' : 'bg-white border-gray-200' }}">

                <!-- PANEL KIRI: DAFTAR 10 KONTAK -->
                <aside
                    :class="mobileView === 'chat' ? 'hidden lg:flex' : 'flex'"
                    class="lg:col-span-4 flex-col border-r {{ $isDark ? 'border-slate-800 bg-[#111B21]' : 'border-gray-200 bg-white' }} h-full overflow-hidden"
                >
                    <!-- Topbar Daftar Chat -->
                    <div class="px-4 py-3.5 bg-[#075E54] text-white flex items-center justify-between gap-2 shrink-0">
                        <div class="flex items-center gap-2.5">
                            <img
                                src="{{ asset('images/icons/wa-logo.jpg') }}"
                                alt="WhatsApp"
                                width="36"
                                height="36"
                                class="w-9 h-9 rounded-xl object-cover shrink-0 shadow-xs"
                            >
                            <div>
                                <h2 class="font-heading font-extrabold text-sm leading-none">Digi-Safe Chat Sim</h2>
                                <span class="text-[10px] text-emerald-100/90" x-text="contacts.length + @js($isEn ? ' simulated contacts' : ' kontak simulasi aktif')"></span>
                            </div>
                        </div>
                        <span class="rounded-full bg-white/15 px-2.5 py-0.5 text-[10px] font-bold">
                            <span x-text="completedCount"></span>/<span x-text="contacts.length"></span>
                        </span>
                    </div>

                    <!-- Pencarian & Filter -->
                    <div class="p-3 space-y-2 border-b {{ $isDark ? 'border-slate-800 bg-[#111B21]' : 'border-gray-100 bg-[#F8FAFC]' }} shrink-0">
                        <input
                            type="text"
                            x-model="search"
                            placeholder="{{ $isEn ? 'Search contact or scam mode...' : 'Cari kontak atau modus (kurir, tilang, BRI)...' }}"
                            class="w-full rounded-xl px-3.5 py-2 text-xs border focus:outline-none focus:border-emerald-500 {{ $isDark ? 'bg-[#202C33] border-slate-700 text-white placeholder:text-slate-400' : 'bg-white border-gray-200 text-ink placeholder:text-gray-400' }}"
                        >
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5">
                            @foreach ([
                                ['k' => 'all', 'l' => $isEn ? 'All' : 'Semua'],
                                ['k' => 'pending', 'l' => $isEn ? 'Unplayed' : 'Belum'],
                                ['k' => 'safe', 'l' => $isEn ? 'Safe' : 'Aman'],
                                ['k' => 'danger', 'l' => $isEn ? 'Trapped' : 'Kena Jebakan'],
                            ] as $f)
                                <button
                                    type="button"
                                    @click="filterStatus = '{{ $f['k'] }}'"
                                    :class="filterStatus === '{{ $f['k'] }}' ? 'bg-[#075E54] text-white' : '{{ $isDark ? 'bg-[#202C33] text-slate-300' : 'bg-white text-muted border border-gray-200' }}'"
                                    class="shrink-0 rounded-full px-3 py-1 text-[10px] font-heading font-bold transition-colors cursor-pointer"
                                >
                                    {{ $f['l'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Scrollable List Kontak -->
                    <div class="flex-1 overflow-y-auto divide-y {{ $isDark ? 'divide-slate-800/80' : 'divide-gray-100' }}">
                        <template x-for="c in filteredContacts" :key="c.id">
                            <button
                                type="button"
                                @click="openContact(c.id, true)"
                                :class="activeId === c.id ? '{{ $isDark ? 'bg-[#2A3942]' : 'bg-[#ECFDF5]' }}' : '{{ $isDark ? 'hover:bg-[#202C33]' : 'hover:bg-slate-50' }}'"
                                class="w-full text-left px-3.5 py-3 flex items-start gap-3 transition-colors cursor-pointer"
                            >
                                <template x-if="c.avatar_url">
                                    <img
                                        :src="c.avatar_url"
                                        :alt="c.name"
                                        width="44"
                                        height="44"
                                        loading="lazy"
                                        class="w-11 h-11 rounded-full object-cover object-top bg-white border border-slate-200/80 shrink-0 shadow-2xs"
                                    >
                                </template>
                                <template x-if="!c.avatar_url">
                                    <span
                                        class="w-11 h-11 rounded-full text-white font-heading font-extrabold text-xs flex items-center justify-center shrink-0 shadow-2xs"
                                        :style="'background-color:' + c.avatar_bg"
                                        x-text="c.initials"
                                    ></span>
                                </template>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-heading font-bold text-xs truncate {{ $isDark ? 'text-white' : 'text-ink' }}" x-text="c.name"></span>
                                        <span class="text-[10px] text-muted shrink-0" x-text="c.time"></span>
                                    </div>
                                    <p class="mt-0.5 text-[11px] truncate {{ $isDark ? 'text-slate-400' : 'text-muted' }}" x-text="c.preview"></p>
                                    <div class="mt-1.5 flex items-center justify-between gap-2">
                                        <span class="rounded-md px-1.5 py-0.5 text-[9.5px] font-semibold {{ $isDark ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-600' }}" x-text="c.category"></span>

                                        <template x-if="progress[c.id] && progress[c.id].type === 'safe'">
                                            <span class="rounded-full bg-emerald-500/15 text-emerald-600 px-2 py-0.5 text-[9.5px] font-extrabold">{{ $isEn ? '✓ Safe' : '✓ Aman' }}</span>
                                        </template>
                                        <template x-if="progress[c.id] && progress[c.id].type === 'partial'">
                                            <span class="rounded-full bg-amber-500/15 text-amber-600 px-2 py-0.5 text-[9.5px] font-extrabold">{{ $isEn ? '! Near-Miss' : '! Nyaris' }}</span>
                                        </template>
                                        <template x-if="progress[c.id] && progress[c.id].type === 'danger'">
                                            <span class="rounded-full bg-red-500/15 text-red-600 px-2 py-0.5 text-[9.5px] font-extrabold">{{ $isEn ? '✕ Trapped' : '✕ Terjebak' }}</span>
                                        </template>
                                        <template x-if="!progress[c.id]">
                                            <span class="w-5 h-5 rounded-full bg-[#25D366] text-white text-[10px] font-extrabold inline-flex items-center justify-center" x-text="c.unread"></span>
                                        </template>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>
                </aside>

                <!-- PANEL KANAN: RUANG CHAT AKTIF -->
                <div
                    :class="mobileView === 'list' ? 'hidden lg:flex' : 'flex'"
                    class="lg:col-span-8 flex-col h-full overflow-hidden"
                    style="background-color: {{ $isDark ? '#0B141A' : '#EFEAE2' }};"
                >
                    <!-- Header Kontak Aktif -->
                    <div class="px-4 py-3 bg-[#075E54] text-white flex items-center justify-between gap-3 shrink-0 shadow-xs">
                        <div class="flex items-center gap-3 min-w-0">
                            <button
                                type="button"
                                @click="mobileView = 'list'"
                                class="lg:hidden w-8 h-8 rounded-full bg-white/15 flex items-center justify-center text-sm font-bold shrink-0 cursor-pointer"
                            >
                                &larr;
                            </button>
                            <template x-if="activeContact && activeContact.avatar_url">
                                <img
                                    :src="activeContact.avatar_url"
                                    :alt="activeContact.name"
                                    width="40"
                                    height="40"
                                    class="w-10 h-10 rounded-full object-cover object-top bg-white shrink-0 ring-2 ring-white/30"
                                >
                            </template>
                            <template x-if="!activeContact || !activeContact.avatar_url">
                                <span
                                    class="w-10 h-10 rounded-full text-white font-heading font-extrabold text-xs flex items-center justify-center shrink-0 ring-2 ring-white/25"
                                    :style="activeContact ? 'background-color:' + activeContact.avatar_bg : ''"
                                    x-text="activeContact ? activeContact.initials : ''"
                                ></span>
                            </template>
                            <div class="min-w-0">
                                <h3 class="font-heading font-extrabold text-sm truncate" x-text="activeContact ? activeContact.name : ''"></h3>
                                <p class="text-[11px] text-emerald-100/90 truncate">
                                    <span x-show="typing" style="display: none;">{{ $isEn ? 'typing...' : 'sedang mengetik...' }}</span>
                                    <span x-show="!typing" x-text="activeContact ? (activeContact.phone + ' · ' + activeContact.category) : ''"></span>
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="openContact(activeId, true)"
                            class="shrink-0 rounded-full bg-white/15 hover:bg-white/25 px-3.5 py-1.5 text-[11px] font-heading font-bold transition-colors cursor-pointer"
                        >
                            {{ $isEn ? 'Restart Chat' : 'Ulangi Chat' }}
                        </button>
                    </div>

                    <!-- Area Pesan Scrollable -->
                    <div
                        x-ref="chatScroll"
                        class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-3"
                        style="background-image: radial-gradient({{ $isDark ? 'rgba(255,255,255,0.04)' : 'rgba(15,23,42,0.05)' }} 1px, transparent 1px); background-size: 18px 18px;"
                    >
                        <!-- Banner Peringatan Edukasi Simulasi -->
                        <div class="flex justify-center">
                            <span class="rounded-lg px-3 py-1 text-[10.5px] font-medium shadow-2xs {{ $isDark ? 'bg-[#182229] text-amber-200/90' : 'bg-[#FFF9DB] text-amber-900 border border-amber-200/70' }}">
                                {{ $isEn ? 'Simulation Mode: Safe training chat — no real files or links are opened.' : 'Mode Simulasi Aman: Semua file .APK & tautan di sini hanyalah simulasi latihan.' }}
                            </span>
                        </div>

                        <!-- Balon Pesan -->
                        <template x-for="(msg, idx) in messages" :key="idx">
                            <div class="flex" :class="msg.sender === 'me' ? 'justify-end' : 'justify-start'">
                                <div
                                    class="max-w-[88%] sm:max-w-[78%] rounded-2xl px-3.5 py-2.5 shadow-2xs text-xs sm:text-[13px] leading-relaxed space-y-2"
                                    :class="msg.sender === 'me'
                                        ? '{{ $isDark ? 'bg-[#005C4B] text-white rounded-tr-sm' : 'bg-[#D9FDD3] text-ink rounded-tr-sm' }}'
                                        : '{{ $isDark ? 'bg-[#202C33] text-slate-100 rounded-tl-sm' : 'bg-white text-ink rounded-tl-sm' }}'"
                                >
                                    <!-- Kartu File .APK Realistis -->
                                    <template x-if="msg.attachment && msg.attachment.type === 'apk'">
                                        <div class="rounded-xl p-3 flex items-center gap-3 border {{ $isDark ? 'bg-[#111B21]/70 border-slate-700' : 'bg-slate-50 border-slate-200' }}">
                                            <span class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 font-mono font-extrabold text-[10px]">
                                                APK
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div class="font-heading font-bold text-xs truncate" x-text="msg.attachment.name"></div>
                                                <div class="text-[10px] text-muted" x-text="msg.attachment.size + ' · ' + msg.attachment.subtitle"></div>
                                            </div>
                                            <span class="w-8 h-8 rounded-full border border-slate-300/70 flex items-center justify-center text-muted shrink-0" aria-hidden="true">
                                                &#8595;
                                            </span>
                                        </div>
                                    </template>

                                    <p class="whitespace-pre-line" x-text="msg.text"></p>

                                    <template x-if="msg.link">
                                        <div class="rounded-lg px-2.5 py-1.5 text-[11px] font-mono break-all {{ $isDark ? 'bg-slate-900/80 text-sky-300' : 'bg-blue-50 text-blue-700' }}" x-text="msg.link"></div>
                                    </template>

                                    <div class="flex items-center justify-end gap-1 text-[10px] opacity-65">
                                        <span x-text="msg.time"></span>
                                        <span x-show="msg.sender === 'me'" class="text-sky-500 font-bold">✓✓</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Indikator Sedang Mengetik -->
                        <div x-show="typing" style="display: none;" class="flex justify-start">
                            <div class="rounded-2xl rounded-tl-sm px-4 py-2.5 shadow-2xs text-xs {{ $isDark ? 'bg-[#202C33] text-slate-300' : 'bg-white text-muted' }}">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce" style="animation-delay: 120ms;"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce" style="animation-delay: 240ms;"></span>
                                </span>
                            </div>
                        </div>

                        <!-- KARTU HASIL ENDING BERBEDA -->
                        <template x-if="activeEnding">
                            <div
                                class="mt-4 rounded-2xl border p-4 sm:p-5 space-y-3 shadow-sm"
                                :class="{
                                    'bg-emerald-50 border-emerald-300 text-emerald-950': activeEnding.type === 'safe',
                                    'bg-amber-50 border-amber-300 text-amber-950': activeEnding.type === 'partial',
                                    'bg-red-50 border-red-300 text-red-950': activeEnding.type === 'danger'
                                }"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span
                                        class="rounded-full px-3 py-1 text-[10px] font-heading font-extrabold uppercase tracking-wider text-white"
                                        :class="{
                                            'bg-emerald-600': activeEnding.type === 'safe',
                                            'bg-amber-600': activeEnding.type === 'partial',
                                            'bg-red-600': activeEnding.type === 'danger'
                                        }"
                                        x-text="activeEnding.type === 'safe' ? 'SAFE ENDING · +100%' : (activeEnding.type === 'partial' ? 'PARTIAL ENDING · +50%' : 'BAD ENDING · 0%')"
                                    ></span>
                                    <span class="text-[11px] font-bold opacity-80" x-text="activeContact ? activeContact.category : ''"></span>
                                </div>

                                <h4 class="font-heading font-extrabold text-base leading-snug" x-text="activeEnding.title"></h4>
                                <p class="text-xs leading-relaxed opacity-90" x-text="activeEnding.summary"></p>

                                <div class="rounded-xl bg-white/85 border border-black/5 p-3.5 space-y-1.5">
                                    <span class="font-heading font-bold text-[11px] uppercase tracking-wider block opacity-80">
                                        {{ $isEn ? 'Actionable Security Takeaways' : 'Langkah Keamanan & Pemulihan' }}
                                    </span>
                                    <ul class="list-disc pl-4 space-y-1 text-xs leading-relaxed">
                                        <template x-for="(step, sIdx) in activeEnding.steps" :key="sIdx">
                                            <li x-text="step"></li>
                                        </template>
                                    </ul>
                                </div>

                                <div class="pt-1 flex flex-wrap items-center gap-2.5">
                                    <button
                                        type="button"
                                        @click="openContact(activeId, true)"
                                        class="rounded-full bg-white border border-black/15 hover:bg-slate-50 text-ink font-heading font-bold text-xs px-4 py-2 transition-colors cursor-pointer"
                                    >
                                        {{ $isEn ? 'Try Another Branch' : 'Coba Jalur Jawaban Lain' }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="nextUnfinished()"
                                        class="rounded-full bg-[#075E54] hover:bg-ink text-white font-heading font-bold text-xs px-5 py-2 transition-colors cursor-pointer"
                                    >
                                        {{ $isEn ? 'Chat Next Contact' : 'Lanjut Chat Orang Berikutnya' }} &rarr;
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Area Pilihan Jawaban Interaktif (2-3 Opsi) -->
                    <div
                        x-show="currentChoices.length > 0"
                        class="p-3.5 sm:p-4 border-t shrink-0 space-y-2 {{ $isDark ? 'bg-[#1F2C34] border-slate-800' : 'bg-[#F0F2F5] border-gray-200' }}"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-heading font-bold {{ $isDark ? 'text-emerald-300' : 'text-[#075E54]' }}">
                                {{ $isEn ? 'Choose your reply (each answer leads to a different ending):' : 'Pilih balasan Anda (setiap jawaban menuju ending berbeda):' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            <template x-for="(choice, cIdx) in currentChoices" :key="cIdx">
                                <button
                                    type="button"
                                    @click="chooseOption(choice)"
                                    class="w-full text-left rounded-xl border px-3.5 py-2.5 text-xs font-medium leading-snug transition-all cursor-pointer {{ $isDark ? 'bg-[#2A3942] border-slate-700 text-white hover:border-emerald-400 hover:bg-[#324550]' : 'bg-white border-gray-200 text-ink hover:border-[#075E54] hover:bg-emerald-50/50' }}"
                                    x-text="choice.label"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
