@php
    $isEnChat = app()->getLocale() === 'en';
    $authUser = Auth::user();
@endphp

<div
    x-data="digiSafeChatWidget(@js((bool) $authUser), @js($authUser?->id ?? null), @js($isEnChat))"
    x-init="init()"
    class="fixed bottom-5 right-6 sm:bottom-6 sm:right-7 z-50 flex flex-col items-end gap-3 pointer-events-none"
>
    <!-- toast notifikasi pesan baru di atas ikon chat -->
    <template x-if="!open && toastAlert">
        <button
            type="button"
            @click="openConversationById(toastAlert.sender_id)"
            class="pointer-events-auto max-w-[280px] rounded-2xl bg-white border border-gray-200/90 shadow-[0_14px_34px_rgba(15,23,42,0.16)] px-3.5 py-2.5 text-left hover:border-sky transition-colors cursor-pointer flex items-center gap-2.5"
        >
            <template x-if="toastAlert.is_bot">
                <img src="{{ asset('images/bot-digika.jpg') }}" alt="Bot Digika" class="w-9 h-9 rounded-full object-cover ring-2 ring-sky/30 bg-white shrink-0">
            </template>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2 mb-0.5">
                    <span class="text-[10px] font-heading font-extrabold uppercase tracking-wider text-[#1E63E9]">
                        <span x-text="toastAlert.is_bot ? @js($isEnChat ? 'Bot Digika Notice' : 'Notifikasi Sistem') : @js($isEnChat ? 'New Message' : 'Pesan Baru')"></span>
                    </span>
                    <span @click.stop="toastAlert = null" class="text-xs text-muted hover:text-ink">&times;</span>
                </div>
                <p class="font-heading font-bold text-xs text-ink truncate" x-text="toastAlert.sender_name"></p>
                <p class="text-[11px] text-muted truncate" x-text="toastAlert.preview"></p>
            </div>
        </button>
    </template>

    <!-- pop-up jendela chat ringkas tepat di atas tombol kanan bawah -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-95"
        style="display: none;"
        class="pointer-events-auto w-[calc(100vw-3rem)] sm:w-[368px] h-[470px] max-h-[74vh] rounded-[24px] bg-white border border-gray-200/90 shadow-[0_24px_60px_rgba(15,23,42,0.20)] flex flex-col overflow-hidden"
    >
        @guest
            <!-- tampilan ringkas untuk pengunjung yang belum login -->
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-full bg-[#1E63E9] text-white flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="13" rx="4" />
                            <path d="M8 17l-2.5 3.5V17" />
                            <circle cx="9" cy="10.5" r="1" fill="currentColor" stroke="none" />
                            <circle cx="12" cy="10.5" r="1" fill="currentColor" stroke="none" />
                            <circle cx="15" cy="10.5" r="1" fill="currentColor" stroke="none" />
                        </svg>
                    </span>
                    <span class="font-heading font-extrabold text-sm text-ink">{{ $isEnChat ? 'Digi-Safe Chat' : 'Obrolan Warga' }}</span>
                </div>
                <button type="button" @click="open = false" class="w-7 h-7 rounded-full hover:bg-gray-100 text-muted hover:text-ink flex items-center justify-center cursor-pointer">&times;</button>
            </div>
            <div class="flex-1 p-6 flex flex-col items-center justify-center text-center bg-[#F8FAFC]">
                <div class="w-12 h-12 rounded-2xl bg-[#DBEAFE] text-[#1E63E9] flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <h3 class="font-heading font-extrabold text-sm text-ink">
                    {{ $isEnChat ? 'Sign in to Chat & Connect' : 'Masuk untuk Berteman & Chat' }}
                </h3>
                <p class="mt-1.5 text-xs text-muted leading-relaxed max-w-[260px]">
                    {{ $isEnChat
                        ? 'Add friends across Digi-Safe Purwakarta and start private conversations safely.'
                        : 'Tambahkan teman sesama warga Digi-Safe Purwakarta dan mulai obrolan pribadi secara aman.' }}
                </p>
                <a href="{{ route('login') }}" class="mt-4 rounded-full bg-[#1E63E9] hover:bg-ink text-white font-heading font-bold text-xs px-5 py-2.5 transition-colors">
                    {{ $isEnChat ? 'Sign In Now' : 'Masuk Sekarang' }}
                </a>
            </div>
        @else
            <!-- header popup chat -->
            <div class="px-4 py-3 border-b border-gray-100 bg-white flex items-center justify-between gap-2 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <template x-if="activeUser">
                        <button
                            type="button"
                            @click="activeUser = null; loadSummary()"
                            class="w-7 h-7 rounded-full hover:bg-gray-100 text-ink flex items-center justify-center shrink-0 cursor-pointer"
                            aria-label="Kembali"
                        >
                            &larr;
                        </button>
                    </template>

                    <template x-if="!activeUser">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-[#1E63E9] text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="13" rx="4" />
                                    <path d="M8 17l-2.5 3.5V17" />
                                    <circle cx="9" cy="10.5" r="1" fill="currentColor" stroke="none" />
                                    <circle cx="12" cy="10.5" r="1" fill="currentColor" stroke="none" />
                                    <circle cx="15" cy="10.5" r="1" fill="currentColor" stroke="none" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-heading font-extrabold text-sm text-ink leading-tight">
                                    {{ $isEnChat ? 'Digi-Safe Chat' : 'Obrolan Warga' }}
                                </h3>
                                <span class="text-[10px] text-muted block leading-tight" x-text="friends.length + (isEn ? ' friends connected' : ' teman terhubung')"></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeUser">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <template x-if="activeUser.is_bot">
                                <img :src="activeUser.avatar_url || '{{ asset('images/bot-digika.jpg') }}'" alt="Bot Digika" class="w-8 h-8 rounded-full object-cover ring-2 ring-sky/35 bg-white shrink-0">
                            </template>
                            <template x-if="!activeUser.is_bot && activeUser.avatar_url">
                                <a :href="activeUser.profile_url" class="shrink-0">
                                    <img :src="activeUser.avatar_url" class="w-8 h-8 rounded-full object-cover">
                                </a>
                            </template>
                            <template x-if="!activeUser.is_bot && !activeUser.avatar_url">
                                <a :href="activeUser.profile_url" class="shrink-0">
                                    <span :class="activeUser.pastel_class" class="w-8 h-8 rounded-full font-heading font-bold text-xs flex items-center justify-center" x-text="activeUser.inisial"></span>
                                </a>
                            </template>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <template x-if="activeUser.is_bot">
                                        <span class="font-heading font-extrabold text-xs text-ink truncate" x-text="activeUser.nama"></span>
                                    </template>
                                    <template x-if="!activeUser.is_bot">
                                        <a :href="activeUser.profile_url" class="font-heading font-bold text-xs text-ink hover:text-[#1E63E9] truncate" x-text="activeUser.nama"></a>
                                    </template>
                                    <span x-show="activeUser.is_bot" class="shrink-0 rounded-full bg-sky/15 text-sky px-1.5 py-0.5 text-[9px] font-extrabold uppercase">Sistem Otomatis</span>
                                    <span x-show="!activeUser.is_bot && activeRelation === 'accepted'" class="shrink-0 rounded-full bg-emerald-100 text-emerald-700 px-1.5 py-0.5 text-[9px] font-bold">Teman</span>
                                </div>
                                <span class="text-[10px] text-muted block truncate" x-text="activeUser.is_bot ? 'Notifikasi Resmi Digi-Safe Purwakarta' : (activeUser.specialty_role || ('@' + activeUser.username))"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="w-7 h-7 rounded-full hover:bg-gray-100 text-muted hover:text-ink flex items-center justify-center text-base leading-none shrink-0 cursor-pointer"
                    aria-label="Tutup chat"
                >
                    &times;
                </button>
            </div>

            <!-- tampilan daftar obrolan & teman (saat belum memilih percakapan) -->
            <template x-if="!activeUser">
                <div class="flex-1 flex flex-col min-h-0">
                    <!-- tab navigator -->
                    <div class="px-3 py-2 bg-[#F8FAFC] border-b border-gray-100 grid grid-cols-2 gap-1.5 shrink-0">
                        <button
                            type="button"
                            @click="tab = 'chats'"
                            :class="tab === 'chats' ? 'bg-white text-ink shadow-2xs border-gray-200' : 'text-muted border-transparent hover:text-ink'"
                            class="rounded-xl border py-1.5 text-[11px] font-heading font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                        >
                            <span>{{ $isEnChat ? 'Messages' : 'Obrolan' }}</span>
                            <span x-show="unreadMessages > 0" class="rounded-full bg-[#1E63E9] text-white px-1.5 py-0.5 text-[9px]" x-text="unreadMessages"></span>
                        </button>
                        <button
                            type="button"
                            @click="tab = 'people'; searchUsers()"
                            :class="tab === 'people' ? 'bg-white text-ink shadow-2xs border-gray-200' : 'text-muted border-transparent hover:text-ink'"
                            class="rounded-xl border py-1.5 text-[11px] font-heading font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                        >
                            <span>{{ $isEnChat ? 'Friends & Find' : 'Teman & Cari' }}</span>
                            <span x-show="pendingRequests > 0" class="rounded-full bg-lime text-ink px-1.5 py-0.5 text-[9px] font-extrabold" x-text="pendingRequests"></span>
                        </button>
                    </div>

                    <!-- tab 1: daftar obrolan -->
                    <div x-show="tab === 'chats'" class="flex-1 overflow-y-auto divide-y divide-gray-100 bg-white">
                        <template x-if="threads.length === 0">
                            <div class="p-7 text-center">
                                <p class="font-heading font-bold text-xs text-ink">{{ $isEnChat ? 'No chats yet' : 'Belum ada obrolan' }}</p>
                                <p class="text-[11px] text-muted mt-1">{{ $isEnChat ? 'Find users or accept requests in the Friends tab.' : 'Cari pengguna lain atau terima permintaan di tab Teman & Cari.' }}</p>
                                <button type="button" @click="tab = 'people'; searchUsers()" class="mt-3 rounded-full bg-[#1E63E9] text-white text-[11px] font-bold px-4 py-1.5 cursor-pointer">
                                    {{ $isEnChat ? 'Find People' : 'Cari Warga' }}
                                </button>
                            </div>
                        </template>

                        <template x-for="item in threads" :key="item.user.id">
                            <button
                                type="button"
                                @click="openConversation(item.user)"
                                class="w-full px-4 py-3 flex items-center gap-3 text-left hover:bg-[#F8FAFC] transition-colors cursor-pointer"
                            >
                                <template x-if="item.user.is_bot">
                                    <img :src="item.user.avatar_url || '{{ asset('images/bot-digika.jpg') }}'" alt="Bot Digika" class="w-10 h-10 rounded-full object-cover ring-2 ring-sky/30 bg-white shrink-0">
                                </template>
                                <template x-if="!item.user.is_bot && item.user.avatar_url">
                                    <img :src="item.user.avatar_url" class="w-10 h-10 rounded-full object-cover shrink-0">
                                </template>
                                <template x-if="!item.user.is_bot && !item.user.avatar_url">
                                    <span :class="item.user.pastel_class" class="w-10 h-10 rounded-full font-heading font-bold text-xs flex items-center justify-center shrink-0" x-text="item.user.inisial"></span>
                                </template>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="font-heading font-bold text-xs text-ink truncate" x-text="item.user.nama"></span>
                                            <span x-show="item.user.is_bot" class="shrink-0 rounded-full bg-sky/15 text-sky px-1.5 py-0.5 text-[8px] font-extrabold uppercase">BOT</span>
                                        </div>
                                        <span class="text-[10px] text-muted shrink-0" x-text="item.last_time || ''"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 mt-0.5">
                                        <p class="text-[11px] text-muted truncate" x-text="item.last_message ? ((item.last_sender_me ? 'Kamu: ' : '') + item.last_message) : (item.user.specialty_role || 'Berteman — mulai chat!')"></p>
                                        <span x-show="item.unread_count > 0" class="shrink-0 rounded-full bg-[#1E63E9] text-white font-bold text-[9px] min-w-[18px] h-[18px] px-1 flex items-center justify-center" x-text="item.unread_count"></span>
                                        <span x-show="item.unread_count === 0 && item.relation === 'pending_incoming'" class="shrink-0 rounded-full bg-amber-100 text-amber-800 font-bold text-[9px] px-1.5 py-0.5">Konfirmasi</span>
                                        <span x-show="item.unread_count === 0 && item.relation === 'pending_outgoing'" class="shrink-0 rounded-full bg-gray-100 text-muted font-bold text-[9px] px-1.5 py-0.5">Menunggu</span>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>

                    <!-- tab 2: permintaan masuk, teman, & pencarian warga -->
                    <div x-show="tab === 'people'" class="flex-1 overflow-y-auto p-3.5 space-y-4 bg-[#F8FAFC]">
                        <!-- permintaan masuk (pertemanan / chat non-teman) -->
                        <template x-if="requests.length > 0">
                            <div class="space-y-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 block px-1">
                                    {{ $isEnChat ? 'Incoming Requests' : 'Menunggu Persetujuan Anda' }}
                                </span>
                                <template x-for="req in requests" :key="req.friendship_id">
                                    <div class="rounded-2xl bg-white border border-amber-200/80 p-3 space-y-2 shadow-2xs">
                                        <div class="flex items-center gap-2.5">
                                            <template x-if="req.user.avatar_url">
                                                <img :src="req.user.avatar_url" class="w-8 h-8 rounded-full object-cover shrink-0">
                                            </template>
                                            <template x-if="!req.user.avatar_url">
                                                <span :class="req.user.pastel_class" class="w-8 h-8 rounded-full font-bold text-[11px] flex items-center justify-center shrink-0" x-text="req.user.inisial"></span>
                                            </template>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-heading font-bold text-xs text-ink truncate" x-text="req.user.nama"></p>
                                                <p class="text-[10px] text-muted truncate" x-text="req.user.specialty_role || ('@' + req.user.username)"></p>
                                            </div>
                                        </div>
                                        <p x-show="req.message_preview" class="text-[11px] bg-[#F8FAFC] rounded-xl px-2.5 py-1.5 text-ink/85 italic" x-text="'\"' + req.message_preview + '\"'"></p>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="respondRequest(req.user.id, 'accept')" class="flex-1 rounded-full bg-[#1E63E9] hover:bg-ink text-white text-[11px] font-bold py-1.5 transition-colors cursor-pointer">
                                                {{ $isEnChat ? 'Accept & Chat' : 'Konfirmasi ✓' }}
                                            </button>
                                            <button type="button" @click="respondRequest(req.user.id, 'decline')" class="rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-700 text-muted text-[11px] font-bold px-3 py-1.5 transition-colors cursor-pointer">
                                                {{ $isEnChat ? 'Decline' : 'Tolak' }}
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- input cari pengguna -->
                        <div>
                            <input
                                type="text"
                                x-model="query"
                                @input.debounce.300ms="searchUsers()"
                                placeholder="{{ $isEnChat ? 'Search name, @username, or role...' : 'Cari nama, @username, atau role...' }}"
                                class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2 text-xs text-ink placeholder:text-gray-400 focus:outline-none focus:border-[#1E63E9]"
                            >
                        </div>

                        <!-- hasil daftar pengguna -->
                        <div class="space-y-2">
                            <template x-for="row in directory" :key="row.user.id">
                                <div class="rounded-2xl bg-white border border-gray-100 p-2.5 flex items-center justify-between gap-2.5 shadow-2xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <template x-if="row.user.avatar_url">
                                            <img :src="row.user.avatar_url" class="w-9 h-9 rounded-full object-cover shrink-0">
                                        </template>
                                        <template x-if="!row.user.avatar_url">
                                            <span :class="row.user.pastel_class" class="w-9 h-9 rounded-full font-bold text-xs flex items-center justify-center shrink-0" x-text="row.user.inisial"></span>
                                        </template>
                                        <div class="min-w-0">
                                            <a :href="row.user.profile_url" class="font-heading font-bold text-xs text-ink hover:text-[#1E63E9] block truncate" x-text="row.user.nama"></a>
                                            <span class="text-[10px] text-muted block truncate" x-text="row.user.specialty_role || ('@' + row.user.username)"></span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button
                                            x-show="row.relation === 'none' || row.relation === 'declined'"
                                            type="button"
                                            @click="sendFriendReq(row.user.id)"
                                            class="rounded-full bg-gray-100 hover:bg-lime text-ink text-[10px] font-bold px-2.5 py-1.5 transition-colors cursor-pointer"
                                        >
                                            + Teman
                                        </button>
                                        <span x-show="row.relation === 'pending_outgoing'" class="rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-1">Menunggu</span>
                                        <span x-show="row.relation === 'accepted'" class="rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-1">Teman</span>

                                        <button
                                            type="button"
                                            @click="openConversation(row.user)"
                                            class="rounded-full bg-[#1E63E9] hover:bg-ink text-white text-[10px] font-bold px-3 py-1.5 transition-colors cursor-pointer"
                                        >
                                            Chat
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <!-- tampilan ruang obrolan 1-on-1 -->
            <template x-if="activeUser">
                <div class="flex-1 flex flex-col min-h-0">
                    <!-- banner persetujuan jika belum berteman -->
                    <div x-show="activeRelation === 'pending_incoming'" class="px-3.5 py-2.5 bg-amber-50 border-b border-amber-200 flex items-center justify-between gap-2 shrink-0">
                        <p class="text-[11px] font-semibold text-amber-900 leading-snug">
                            {{ $isEnChat ? 'Accept request to reply & be friends?' : 'Konfirmasi untuk membalas & berteman?' }}
                        </p>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="respondRequest(activeUser.id, 'accept')" class="rounded-full bg-[#1E63E9] text-white text-[10px] font-bold px-2.5 py-1 cursor-pointer">Terima</button>
                            <button type="button" @click="respondRequest(activeUser.id, 'decline')" class="rounded-full bg-white border border-gray-200 text-muted text-[10px] font-bold px-2.5 py-1 cursor-pointer">Tolak</button>
                        </div>
                    </div>

                    <div x-show="activeRelation === 'pending_outgoing'" class="px-3.5 py-2 bg-[#EFF6FF] border-b border-sky/20 text-[10.5px] font-medium text-[#1E40AF] shrink-0">
                        {{ $isEnChat ? 'Waiting for confirmation from this user to unlock full chat.' : 'Menunggu persetujuan pengguna ini untuk membuka obrolan dua arah.' }}
                    </div>

                    <!-- area pesan -->
                    <div x-ref="msgList" class="flex-1 overflow-y-auto p-3.5 space-y-2.5 bg-[#F8FAFC]">
                        <template x-if="messages.length === 0">
                            <div class="py-10 text-center text-[11px] text-muted">
                                <span x-text="activeRelation === 'accepted' ? (isEn ? 'Say hello to start chatting!' : 'Sapa temanmu untuk memulai obrolan!') : (isEn ? 'Send an intro chat. They can confirm to chat back!' : 'Kirim sapaan pembuka. Penerima dapat mengonfirmasi untuk membalas!')"></span>
                            </div>
                        </template>

                        <template x-for="m in messages" :key="m.id">
                            <div :class="m.is_mine ? 'justify-end' : 'justify-start'" class="flex">
                                <div
                                    :class="m.is_mine ? 'bg-[#1E63E9] text-white rounded-2xl rounded-br-sm' : (activeUser.is_bot ? 'bg-white border border-sky/30 text-ink rounded-2xl rounded-bl-sm' : 'bg-white border border-gray-200/80 text-ink rounded-2xl rounded-bl-sm')"
                                    class="max-w-[84%] px-3.5 py-2.5 shadow-2xs"
                                >
                                    <p class="text-xs leading-relaxed break-words whitespace-pre-line" x-text="m.message"></p>
                                    <span :class="m.is_mine ? 'text-white/70' : 'text-muted'" class="text-[9px] block text-right mt-1" x-text="m.time"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- input kirim pesan -->
                    <div class="p-3 border-t border-gray-100 bg-white shrink-0">
                        <template x-if="chatError">
                            <p class="text-[10px] font-semibold text-red-600 mb-1.5" x-text="chatError"></p>
                        </template>

                        <template x-if="activeUser.is_bot">
                            <div class="flex items-center justify-between gap-2 py-0.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <img src="{{ asset('images/bot-digika.jpg') }}" alt="Bot Digika" class="w-5 h-5 rounded-full object-cover shrink-0 bg-white">
                                    <span class="text-[10.5px] text-muted font-medium truncate">
                                        {{ $isEnChat ? 'Automated by Bot Digika • No reply needed' : 'Pesan otomatis Bot Digika • Tidak perlu dibalas' }}
                                    </span>
                                </div>
                                <a href="{{ route('edukasi') }}" class="shrink-0 rounded-full bg-sky/10 hover:bg-sky text-sky hover:text-white text-[10px] font-bold px-3 py-1.5 transition-colors">
                                    {{ $isEnChat ? 'Education' : 'Buka Edukasi' }} &rarr;
                                </a>
                            </div>
                        </template>

                        <template x-if="!activeUser.is_bot && activeRelation === 'pending_incoming'">
                            <p class="text-[11px] text-center text-muted py-1">
                                {{ $isEnChat ? 'Click Accept above to reply.' : 'Klik Terima di atas untuk membalas pesan ini.' }}
                            </p>
                        </template>

                        <template x-if="!activeUser.is_bot && activeRelation === 'declined'">
                            <p class="text-[11px] text-center text-red-600 py-1">
                                {{ $isEnChat ? 'Chat request was declined.' : 'Permintaan obrolan ditolak.' }}
                            </p>
                        </template>

                        <form x-show="!activeUser.is_bot && activeRelation !== 'pending_incoming' && activeRelation !== 'declined'" @submit.prevent="sendMessage()" class="flex items-center gap-2">
                            <input
                                type="text"
                                x-model="draft"
                                maxlength="1000"
                                placeholder="{{ $isEnChat ? 'Write a message...' : 'Tulis pesan...' }}"
                                class="flex-1 rounded-full bg-[#F8FAFC] border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-[#1E63E9]"
                            >
                            <button
                                type="submit"
                                :disabled="sending || !draft.trim()"
                                class="w-9 h-9 rounded-full bg-[#1E63E9] hover:bg-ink disabled:opacity-45 text-white flex items-center justify-center shrink-0 transition-colors cursor-pointer"
                                aria-label="Kirim"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13" />
                                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        @endguest
    </div>

    <!-- tombol floating chat di sudut kanan bawah -->
    <button
        type="button"
        @click="toggleOpen()"
        aria-label="Buka Chat & Pertemanan"
        class="chat-fab-btn pointer-events-auto relative w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-[#1E63E9] hover:bg-[#1750C4] text-white ring-[3px] ring-white shadow-[0_12px_28px_rgba(30,99,233,0.36)] flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 cursor-pointer"
    >
        <template x-if="!open">
            <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="13" rx="4.5" />
                <path d="M8.5 17L6 20.5V17" fill="#1E63E9" />
                <circle cx="8.5" cy="10.5" r="1.15" fill="currentColor" stroke="none" />
                <circle cx="12" cy="10.5" r="1.15" fill="currentColor" stroke="none" />
                <circle cx="15.5" cy="10.5" r="1.15" fill="currentColor" stroke="none" />
            </svg>
        </template>
        <template x-if="open">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </template>

        <span
            x-show="totalBadge > 0"
            x-text="totalBadge > 99 ? '99+' : totalBadge"
            style="display: none;"
            class="chat-fab-badge absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-lime text-ink border-2 border-white font-heading font-extrabold text-[10px] flex items-center justify-center shadow-xs"
        ></span>
    </button>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('digiSafeChatWidget', (isLoggedIn, myId, isEn) => ({
            isLoggedIn,
            myId,
            isEn,
            open: false,
            tab: 'chats',
            threads: [],
            requests: [],
            friends: [],
            directory: [],
            query: '',
            activeUser: null,
            activeRelation: 'none',
            messages: [],
            draft: '',
            sending: false,
            chatError: '',
            unreadMessages: 0,
            pendingRequests: 0,
            totalBadge: 0,
            toastAlert: null,
            lastAlertSig: '',
            poller: null,

            init() {
                window.addEventListener('open-chat-with', (e) => {
                    if (!this.isLoggedIn) {
                        this.open = true;
                        return;
                    }
                    if (e.detail && e.detail.userId) {
                        this.openConversationById(e.detail.userId);
                    }
                });

                if (this.isLoggedIn) {
                    this.pollUnread();
                    this.poller = setInterval(() => {
                        if (this.open) {
                            if (this.activeUser) {
                                this.loadMessages(this.activeUser.id, false);
                            } else {
                                this.loadSummary();
                            }
                        } else {
                            this.pollUnread();
                        }
                    }, 5000);
                }
            },

            csrf() {
                return document.querySelector('meta[name=csrf-token]')?.content || '';
            },

            toggleOpen() {
                this.open = !this.open;
                if (this.open && this.isLoggedIn) {
                    this.toastAlert = null;
                    this.loadSummary();
                }
            },

            async pollUnread() {
                try {
                    const res = await fetch('/chat/unread-count', { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.unreadMessages = data.unread_messages || 0;
                    this.pendingRequests = data.pending_requests || 0;
                    this.totalBadge = data.total_badge || 0;
                    if (data.latest_alert) {
                        const sig = `${data.latest_alert.message_id || ''}:${data.latest_alert.sender_id}:${data.latest_alert.preview}`;
                        if (sig !== this.lastAlertSig) {
                            this.lastAlertSig = sig;
                            this.toastAlert = data.latest_alert;
                        }
                    }
                } catch (_) {}
            },

            async loadSummary() {
                try {
                    const res = await fetch('/chat/summary', { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.threads = data.threads || [];
                    this.requests = data.requests || [];
                    this.friends = data.friends || [];
                    this.unreadMessages = data.unread_messages || 0;
                    this.pendingRequests = data.pending_requests || 0;
                    this.totalBadge = data.total_badge || 0;
                } catch (_) {}
            },

            async searchUsers() {
                try {
                    const res = await fetch('/chat/users?q=' + encodeURIComponent(this.query || ''), {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.directory = data.users || [];
                } catch (_) {}
            },

            async openConversation(user) {
                this.open = true;
                this.toastAlert = null;
                this.chatError = '';
                this.activeUser = user;
                await this.loadMessages(user.id, true);
            },

            async openConversationById(userId) {
                this.open = true;
                this.toastAlert = null;
                this.chatError = '';
                await this.loadMessages(userId, true);
            },

            async loadMessages(userId, scrollBottom) {
                try {
                    const res = await fetch(`/chat/messages/${userId}`, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    const prevLen = this.messages.length;
                    this.activeUser = data.user;
                    this.activeRelation = data.relation || 'none';
                    this.messages = data.messages || [];
                    this.pollUnread();
                    if (scrollBottom || this.messages.length > prevLen) {
                        this.$nextTick(() => {
                            if (this.$refs.msgList) {
                                this.$refs.msgList.scrollTop = this.$refs.msgList.scrollHeight;
                            }
                        });
                    }
                } catch (_) {}
            },

            async sendMessage() {
                if (!this.activeUser || !this.draft.trim() || this.sending) return;
                this.sending = true;
                this.chatError = '';
                try {
                    const res = await fetch(`/chat/messages/${this.activeUser.id}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf(),
                        },
                        body: JSON.stringify({ message: this.draft }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.chatError = data.message || 'Gagal mengirim pesan.';
                        return;
                    }
                    this.activeRelation = data.relation || this.activeRelation;
                    this.messages.push(data.message);
                    this.draft = '';
                    this.$nextTick(() => {
                        if (this.$refs.msgList) {
                            this.$refs.msgList.scrollTop = this.$refs.msgList.scrollHeight;
                        }
                    });
                } finally {
                    this.sending = false;
                }
            },

            async sendFriendReq(userId) {
                await fetch(`/friends/request/${userId}`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                await this.searchUsers();
                await this.loadSummary();
            },

            async respondRequest(userId, action) {
                await fetch(`/friends/${action}/${userId}`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                await this.loadSummary();
                await this.searchUsers();
                if (this.activeUser && this.activeUser.id === userId) {
                    await this.loadMessages(userId, false);
                }
            },
        }));
    });
</script>

