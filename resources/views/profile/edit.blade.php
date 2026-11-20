<!-- modal overlay edit profil & banner -->
@if ($isOwnProfile)
    @php
        $isEnEdit = app()->getLocale() === 'en';
        $roleOptions = \App\Models\User::specialtyRoleOptions($isEnEdit);
    @endphp
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none; background: rgba(10, 10, 10, 0.55);"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
        @keydown.escape.window="if (cropOpen) { closeCropModal(); } else { open = false; }"
        x-data="profileImageCropper()"
    >
        <div
            @click.outside="if (!cropOpen) open = false"
            class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-[28px] bg-white border border-gray-100 p-6 sm:p-8 shadow-neutral"
        >
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-heading font-extrabold text-lg text-ink">{{ __('ui.profile.edit_heading') }}</h3>
                    <p class="text-xs text-muted mt-0.5">{{ __('ui.profile.edit_sub') }}</p>
                </div>
                <button
                    type="button"
                    @click="open = false"
                    class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center transition-colors cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- form update profil -->
            <form
                action="{{ route('profile.update') }}"
                method="POST"
                enctype="multipart/form-data"
                class="mt-5 space-y-4"
            >
                @csrf
                @method('PUT')

                <!-- upload banner profil (mendukung foto/gif + pilihan warna default Solid & Gradient) -->
                @php
                    $bannerPresetCatalog = \App\Models\User::bannerPresets($isEnEdit);
                    $initialPresetKey = $profileUser->banner_preset_key;
                    $initialCustomHex = str_starts_with($initialPresetKey, 'solid-custom:')
                        ? substr($initialPresetKey, strlen('solid-custom:'))
                        : '#1E63E9';
                @endphp
                <div
                    x-data="{
                        bannerTab: @js(str_starts_with($initialPresetKey, 'solid-') ? 'solid' : 'gradient'),
                        selectedPreset: @js($initialPresetKey),
                        customHex: @js($initialCustomHex),
                        useDefaultBanner: {{ $profileUser->banner_url ? 'false' : 'true' }},
                        presetsSolid: @js($bannerPresetCatalog['solid']),
                        presetsGradient: @js($bannerPresetCatalog['gradient']),
                        activePresetCss() {
                            if (this.selectedPreset.startsWith('solid-custom:')) {
                                return this.selectedPreset.replace('solid-custom:', '');
                            }
                            const all = [...this.presetsSolid, ...this.presetsGradient];
                            const found = all.find(p => p.key === this.selectedPreset);
                            return found ? found.css : 'linear-gradient(118deg, #F59E0B 0%, #FB923C 28%, #FDA4AF 58%, #67E8F9 84%, #38BDF8 100%)';
                        },
                        pickPreset(key) {
                            this.selectedPreset = key;
                            this.useDefaultBanner = true;
                            bannerPreviewUrl = null;
                            if ($refs.bannerInput) $refs.bannerInput.value = '';
                        }
                    }"
                    class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4 space-y-3.5"
                >
                    <input type="hidden" name="banner_preset" :value="selectedPreset">
                    <input type="hidden" name="use_default_banner" :value="useDefaultBanner ? '1' : '0'">

                    <div
                        class="relative h-28 w-full rounded-xl overflow-hidden border border-gray-200 transition-all duration-300"
                        :style="'background: ' + activePresetCss()"
                    >
                        <template x-if="bannerPreviewUrl && !useDefaultBanner">
                            <img :src="bannerPreviewUrl" alt="Preview Banner" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!bannerPreviewUrl && !useDefaultBanner">
                            @if ($profileUser->banner_url)
                                <img src="{{ $profileUser->banner_url }}" alt="Banner Saat Ini" class="w-full h-full object-cover">
                            @endif
                        </template>
                        <template x-if="useDefaultBanner">
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="rounded-full bg-white/90 px-3 py-1 text-[10px] font-heading font-bold text-ink shadow-2xs">
                                    <span x-text="selectedPreset.startsWith('solid-') ? @js($isEnEdit ? 'Solid Color Banner' : 'Banner Warna Solid') : @js($isEnEdit ? 'Gradient Color Banner' : 'Banner Warna Gradient')"></span>
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Pemilih Warna Default Banner: Tab Solid vs Gradient -->
                    <div class="rounded-xl bg-white border border-gray-200/80 p-3 space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-[11px] font-heading font-bold text-ink">
                                {{ $isEnEdit ? 'Default Banner Color (Solid / Gradient)' : 'Warna Default Banner (Solid / Gradient)' }}
                            </span>
                            <div class="inline-flex rounded-lg bg-slate-100 p-0.5 text-[10px] font-heading font-bold">
                                <button
                                    type="button"
                                    @click="bannerTab = 'gradient'"
                                    :class="bannerTab === 'gradient' ? 'bg-white text-ink shadow-2xs' : 'text-muted hover:text-ink'"
                                    class="rounded-md px-2.5 py-1 transition-all cursor-pointer"
                                >Gradient</button>
                                <button
                                    type="button"
                                    @click="bannerTab = 'solid'"
                                    :class="bannerTab === 'solid' ? 'bg-white text-ink shadow-2xs' : 'text-muted hover:text-ink'"
                                    class="rounded-md px-2.5 py-1 transition-all cursor-pointer"
                                >Solid</button>
                            </div>
                        </div>

                        <!-- Swatch Gradient -->
                        <div x-show="bannerTab === 'gradient'" class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                            <template x-for="g in presetsGradient" :key="g.key">
                                <button
                                    type="button"
                                    @click="pickPreset(g.key)"
                                    :class="selectedPreset === g.key && useDefaultBanner ? 'ring-2 ring-ink ring-offset-1 scale-[1.02]' : 'opacity-90 hover:opacity-100'"
                                    class="group rounded-lg p-1 border border-gray-200 bg-white text-center transition-all cursor-pointer"
                                >
                                    <span class="block h-7 w-full rounded-md" :style="'background:' + g.css"></span>
                                    <span class="mt-1 block text-[9.5px] font-semibold text-ink truncate" x-text="g.label"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Swatch Solid + Custom Color Picker -->
                        <div x-show="bannerTab === 'solid'" style="display: none;" class="space-y-2">
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                <template x-for="s in presetsSolid" :key="s.key">
                                    <button
                                        type="button"
                                        @click="pickPreset(s.key)"
                                        :class="selectedPreset === s.key && useDefaultBanner ? 'ring-2 ring-ink ring-offset-1 scale-[1.02]' : 'opacity-90 hover:opacity-100'"
                                        class="group rounded-lg p-1 border border-gray-200 bg-white text-center transition-all cursor-pointer"
                                    >
                                        <span class="block h-7 w-full rounded-md" :style="'background:' + s.css"></span>
                                        <span class="mt-1 block text-[9.5px] font-semibold text-ink truncate" x-text="s.label"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="pt-1 flex items-center justify-between gap-2 border-t border-gray-100">
                                <span class="text-[10.5px] text-muted">{{ $isEnEdit ? 'Or pick custom solid color:' : 'Atau pilih warna solid bebas:' }}</span>
                                <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-2.5 py-1 bg-slate-50 hover:bg-slate-100 cursor-pointer">
                                    <input
                                        type="color"
                                        x-model="customHex"
                                        @input="pickPreset('solid-custom:' + customHex)"
                                        class="w-5 h-5 rounded border-0 p-0 cursor-pointer bg-transparent"
                                    >
                                    <span class="font-mono font-bold text-[10.5px] text-ink uppercase" x-text="customHex"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                            <label class="block text-xs font-bold text-ink">{{ __('ui.profile.banner_label') }}</label>
                            <button
                                type="button"
                                x-show="rawBannerFile"
                                @click="reopenRawCrop('banner')"
                                class="inline-flex items-center gap-1 rounded-full bg-sky/10 hover:bg-sky text-sky hover:text-white px-2.5 py-1 text-[10px] font-heading font-bold transition-colors cursor-pointer"
                            >
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6.13 1L6 16a2 2 0 0 0 2 2h15" />
                                    <path d="M1 6.13L16 6a2 2 0 0 1 2 2v15" />
                                </svg>
                                <span>{{ $isEnEdit ? 'Re-crop Banner' : 'Crop Ulang Banner' }}</span>
                            </button>
                        </div>
                        <input
                            x-ref="bannerInput"
                            type="file"
                            name="banner"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            @change="
                                const bFile = $event.target.files[0];
                                if (bFile) {
                                    useDefaultBanner = false;
                                    const bReader = new FileReader();
                                    bReader.onload = (e) => { bannerPreviewUrl = e.target.result; };
                                    bReader.readAsDataURL(bFile);
                                    openCropFor(bFile, 'banner');
                                }
                            "
                            class="block w-full text-xs text-muted file:mr-3 file:rounded-full file:border-0 file:bg-ink file:px-3.5 file:py-1.5 file:text-xs file:font-bold file:text-lime hover:file:bg-sky hover:file:text-white file:cursor-pointer"
                        >
                        <span class="text-[10px] text-muted block mt-1">{{ __('ui.profile.upload_hint') }}</span>
                    </div>
                </div>

                <!-- upload foto profil dengan preview & crop -->
                <div class="flex items-center gap-4 rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4">
                    <div class="relative shrink-0">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Preview Avatar" class="w-16 h-16 rounded-full object-cover ring-2 ring-sky">
                        </template>
                        <template x-if="!previewUrl">
                            <x-user-avatar :user="$profileUser" size="xl" class="!w-16 !h-16 !text-xl ring-2 ring-white shadow-xs" />
                        </template>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                            <label class="block text-xs font-bold text-ink">{{ __('ui.profile.avatar_label') }}</label>
                            <button
                                type="button"
                                x-show="rawAvatarFile"
                                @click="reopenRawCrop('avatar')"
                                class="inline-flex items-center gap-1 rounded-full bg-sky/10 hover:bg-sky text-sky hover:text-white px-2.5 py-1 text-[10px] font-heading font-bold transition-colors cursor-pointer"
                            >
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6.13 1L6 16a2 2 0 0 0 2 2h15" />
                                    <path d="M1 6.13L16 6a2 2 0 0 1 2 2v15" />
                                </svg>
                                <span>{{ $isEnEdit ? 'Re-crop Photo' : 'Crop Ulang Foto' }}</span>
                            </button>
                        </div>
                        <input
                            x-ref="avatarInput"
                            type="file"
                            name="avatar"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => { previewUrl = e.target.result; };
                                    reader.readAsDataURL(file);
                                    openCropFor(file, 'avatar');
                                }
                            "
                            class="block w-full text-xs text-muted file:mr-3 file:rounded-full file:border-0 file:bg-sky file:px-3.5 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-sky-light file:cursor-pointer"
                        >
                        <span class="text-[10px] text-muted block mt-1">{{ __('ui.profile.upload_hint') }}</span>
                    </div>
                </div>

                <!-- nickname / nama lengkap -->
                <div>
                    <label class="block text-xs font-bold text-ink mb-1.5">{{ __('ui.profile.full_name') }}</label>
                    <input
                        type="text"
                        name="nama_lengkap"
                        value="{{ old('nama_lengkap', $profileUser->nama_lengkap ?: $profileUser->username) }}"
                        required
                        class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                    >
                </div>

                <!-- username disabled (hanya baca) -->
                <div>
                    <label class="block text-xs font-bold text-ink mb-1.5">{{ __('ui.profile.username') }}</label>
                    <input
                        type="text"
                        value="{{ $profileUser->username }}"
                        disabled
                        class="w-full rounded-xl bg-gray-100 border border-gray-200 px-4 py-2.5 text-xs text-muted cursor-not-allowed select-none"
                    >
                </div>

                <!-- select your role (variasi maksimal 4 pilihan kartu interaktif) -->
                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <label class="block text-xs font-bold text-ink">
                                {{ __('ui.profile.select_role_label') }}
                            </label>
                            <span class="text-[10px] text-muted block mt-0.5">
                                {{ $isEnEdit ? 'Choose 1 badge role displayed on your profile & member card' : 'Pilih 1 role yang tampil di profil & kartu komunitas Anda' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button
                                type="button"
                                x-show="specialtyRole"
                                @click="specialtyRole = ''"
                                class="rounded-full bg-white border border-gray-200 hover:border-rose-300 hover:text-rose-600 px-2.5 py-0.5 text-[10px] font-bold text-muted transition-colors cursor-pointer"
                            >
                                Reset
                            </button>
                            <span class="rounded-full bg-sky/10 text-sky px-2.5 py-0.5 text-[10px] font-extrabold">
                                4 Role
                            </span>
                        </div>
                    </div>

                    <input type="hidden" name="specialty_role" :value="specialtyRole">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach ($roleOptions as $opt)
                            @php
                                $optIconClass = match ($opt['icon']) {
                                    'shield' => 'bg-sky/15 text-sky',
                                    'book' => 'bg-lime/45 text-ink',
                                    'search' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-indigo-100 text-indigo-700',
                                };
                            @endphp
                            <button
                                type="button"
                                @click="specialtyRole = (specialtyRole === @js($opt['value']) ? '' : @js($opt['value']))"
                                :class="specialtyRole === @js($opt['value'])
                                    ? 'bg-[#EFF6FF] border-sky text-ink ring-2 ring-sky/25 shadow-2xs'
                                    : 'bg-white border-gray-200 text-muted hover:border-sky/50 hover:text-ink'"
                                class="rounded-2xl border p-3 text-left transition-all cursor-pointer flex items-center gap-3"
                            >
                                <span class="w-9 h-9 rounded-xl {{ $optIconClass }} flex items-center justify-center shrink-0">
                                    @if ($opt['icon'] === 'shield')
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                                    @elseif ($opt['icon'] === 'book')
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                    @elseif ($opt['icon'] === 'search')
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    @else
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <span class="font-heading font-bold text-xs text-ink block truncate">{{ $opt['label'] }}</span>
                                    <span class="text-[10px] text-muted block truncate mt-0.5">{{ $opt['desc'] }}</span>
                                </div>
                                <span
                                    x-show="specialtyRole === @js($opt['value'])"
                                    class="w-5 h-5 rounded-full bg-sky text-white flex items-center justify-center shrink-0"
                                >
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- update bio -->
                <div>
                    <label class="block text-xs font-bold text-ink mb-1.5">{{ __('ui.profile.bio_label') }}</label>
                    <textarea
                        name="bio"
                        rows="2"
                        placeholder="{{ __('ui.profile.bio_placeholder') }}"
                        class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky resize-none"
                    >{{ old('bio', $profileUser->bio) }}</textarea>
                </div>

                <!-- password baru opsional (wajib konfirmasi password lama untuk akun non-Google) -->
                <div class="space-y-3 rounded-2xl bg-gray-50/70 border border-gray-100 p-3.5">
                    @if (empty($user->oauth_provider))
                        <div>
                            <label class="block text-xs font-bold text-ink mb-1.5">{{ $isEnEdit ? 'Current Password (Required to change password)' : 'Password Lama (Wajib diisi jika ganti password)' }}</label>
                            <input
                                type="password"
                                name="current_password"
                                autocomplete="current-password"
                                placeholder="{{ $isEnEdit ? 'Enter your current password' : 'Masukkan password lama Anda' }}"
                                class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                            >
                        </div>
                    @endif
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">{{ $isEnEdit ? 'New Password (Optional)' : 'Password Baru (Opsional)' }}</label>
                        <input
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            placeholder="{{ $isEnEdit ? 'Leave blank if you do not want to change your password' : 'Biarkan kosong jika tidak ingin mengubah password' }}"
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-gray-100">
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-full border border-gray-200 bg-white px-5 py-2.5 text-xs font-semibold text-muted hover:text-ink cursor-pointer"
                    >
                        {{ __('ui.profile.cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-6 py-2.5 shadow-xs transition-colors cursor-pointer"
                    >
                        {{ __('ui.profile.save_changes') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Overlay Pop-up Cropper Interaktif (JPG/PNG/WebP & Animasi GIF) -->
        <div
            x-show="cropOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none; background: rgba(10, 15, 28, 0.72);"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 backdrop-blur-xs"
        >
            <div
                @click.stop
                class="w-full max-w-lg rounded-[26px] bg-white border border-gray-200/80 p-5 sm:p-6 shadow-2xl space-y-4"
            >
                <div class="flex items-center justify-between gap-3 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-xl bg-sky/10 text-sky flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6.13 1L6 16a2 2 0 0 0 2 2h15" />
                                <path d="M1 6.13L16 6a2 2 0 0 1 2 2v15" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4
                                    class="font-heading font-extrabold text-sm text-ink truncate"
                                    x-text="cropTarget === 'banner' ? @js($isEnEdit ? 'Crop Banner Image' : 'Crop Gambar Banner') : @js($isEnEdit ? 'Crop Profile Picture' : 'Crop Foto Profil')"
                                ></h4>
                                <span
                                    x-show="isGifFile"
                                    class="rounded-full bg-lime text-ink px-2 py-0.5 text-[9px] font-extrabold uppercase shrink-0"
                                >GIF Animasi</span>
                            </div>
                            <p class="text-[11px] text-muted truncate">
                                {{ $isEnEdit ? 'Drag to adjust position and zoom before saving.' : 'Geser gambar untuk mengatur posisi & perbesar sesuai selera.' }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="closeCropModal()"
                        :disabled="cropBusy"
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center cursor-pointer shrink-0"
                    >
                        &times;
                    </button>
                </div>

                <!-- Area Viewport Crop -->
                <div class="flex flex-col items-center justify-center rounded-2xl bg-[#0F172A] p-4 sm:p-5 select-none">
                    <div
                        x-ref="cropViewport"
                        @pointerdown="onCropPointerDown($event)"
                        @wheel="onWheelZoom($event)"
                        @dragstart.prevent
                        :class="[
                            cropTarget === 'banner' ? 'w-full max-w-[420px] aspect-[16/5] rounded-xl' : 'w-[240px] h-[240px] sm:w-[260px] sm:h-[260px] rounded-2xl',
                            dragging ? 'cursor-grabbing' : 'cursor-grab'
                        ]"
                        style="touch-action: none;"
                        class="relative overflow-hidden bg-slate-900 border-2 border-white/80 shadow-inner"
                    >
                        <template x-if="cropSrcUrl">
                            <img
                                :src="cropSrcUrl"
                                @load="onImageLoaded($event)"
                                :style="imgStyle"
                                alt="Crop Preview"
                                class="absolute left-1/2 top-1/2 max-w-none max-h-none pointer-events-none select-none"
                            >
                        </template>

                        <!-- Panduan Lingkaran untuk Avatar / Grid untuk Banner -->
                        <div
                            x-show="cropTarget === 'avatar'"
                            class="pointer-events-none absolute inset-0 rounded-full ring-[9999px] ring-black/45 border-2 border-dashed border-lime/90"
                        ></div>
                        <div
                            x-show="cropTarget === 'banner'"
                            class="pointer-events-none absolute inset-0 grid grid-cols-3 grid-rows-3"
                        >
                            <div class="border-r border-b border-white/20"></div>
                            <div class="border-r border-b border-white/20"></div>
                            <div class="border-b border-white/20"></div>
                            <div class="border-r border-b border-white/20"></div>
                            <div class="border-r border-b border-white/20"></div>
                            <div class="border-b border-white/20"></div>
                            <div class="border-r border-white/20"></div>
                            <div class="border-r border-white/20"></div>
                            <div></div>
                        </div>
                    </div>
                </div>

                <!-- Kontrol Zoom & Reset -->
                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 px-4 py-3 space-y-2">
                    <div class="flex items-center justify-between text-[11px] font-bold text-ink">
                        <span>{{ $isEnEdit ? 'Zoom Level' : 'Perbesaran (Zoom)' }}</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sky" x-text="Math.round(zoom * 100) + '%'"></span>
                            <button
                                type="button"
                                @click="resetCropView()"
                                class="rounded-full bg-white border border-gray-200 hover:border-sky px-2.5 py-0.5 text-[10px] font-bold text-muted hover:text-ink cursor-pointer"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="setZoom(zoom - 0.15)"
                            class="w-7 h-7 rounded-full bg-white border border-gray-200 text-ink font-bold text-sm flex items-center justify-center hover:border-sky cursor-pointer"
                        >-</button>
                        <input
                            type="range"
                            min="1"
                            max="3.5"
                            step="0.05"
                            :value="zoom"
                            @input="setZoom($event.target.value)"
                            class="flex-1 accent-[#1E88E5] cursor-pointer"
                        >
                        <button
                            type="button"
                            @click="setZoom(zoom + 0.15)"
                            class="w-7 h-7 rounded-full bg-white border border-gray-200 text-ink font-bold text-sm flex items-center justify-center hover:border-sky cursor-pointer"
                        >+</button>
                    </div>
                </div>

                <template x-if="cropError">
                    <p class="text-xs font-semibold text-red-600 text-center" x-text="cropError"></p>
                </template>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-100">
                    <button
                        type="button"
                        @click="useOriginalImage((target, dataUrl) => { if (target === 'banner') bannerPreviewUrl = dataUrl; else previewUrl = dataUrl; })"
                        :disabled="cropBusy"
                        class="rounded-full border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-muted hover:text-ink cursor-pointer"
                    >
                        {{ $isEnEdit ? 'Use Original' : 'Pakai Asli Tanpa Crop' }}
                    </button>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="closeCropModal()"
                            :disabled="cropBusy"
                            class="rounded-full bg-gray-100 hover:bg-gray-200 px-4 py-2 text-xs font-bold text-ink cursor-pointer"
                        >
                            {{ __('ui.profile.cancel') }}
                        </button>
                        <button
                            type="button"
                            @click="applyCrop((target, dataUrl) => { if (target === 'banner') bannerPreviewUrl = dataUrl; else previewUrl = dataUrl; })"
                            :disabled="cropBusy"
                            class="inline-flex items-center gap-2 rounded-full bg-ink hover:bg-sky text-white font-heading font-bold text-xs px-5 py-2 shadow-xs transition-colors cursor-pointer disabled:opacity-60"
                        >
                            <span x-show="cropBusy" class="w-3.5 h-3.5 border-2 border-lime border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="cropBusy ? (isGifFile ? @js($isEnEdit ? 'Processing GIF...' : 'Memproses GIF...') : @js($isEnEdit ? 'Cropping...' : 'Memproses...')) : @js($isEnEdit ? 'Apply Crop' : 'Terapkan Crop')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
