<!-- modal overlay edit profil & banner -->
@if ($isOwnProfile)
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
        @keydown.escape.window="open = false"
    >
        <div
            @click.outside="open = false"
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

                <!-- upload banner profil (mendukung foto biasa maupun animasi gif) -->
                <div class="rounded-2xl bg-[#F8FAFC] border border-gray-200/80 p-4 space-y-3">
                    <div
                        class="relative h-28 w-full rounded-xl overflow-hidden border border-gray-200"
                        style="background: linear-gradient(118deg, #F59E0B 0%, #FB923C 28%, #FDA4AF 58%, #67E8F9 84%, #38BDF8 100%);"
                    >
                        <template x-if="bannerPreviewUrl">
                            <img :src="bannerPreviewUrl" alt="Preview Banner" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!bannerPreviewUrl">
                            @if ($profileUser->banner_url)
                                <img src="{{ $profileUser->banner_url }}" alt="Banner Saat Ini" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="rounded-full bg-white/85 px-3 py-1 text-[10px] font-bold text-ink">{{ app()->getLocale() === 'en' ? 'Default Gradient Banner' : 'Banner Default Abstrak' }}</span>
                                </div>
                            @endif
                        </template>
                    </div>

                        <div>
                            <label class="block text-xs font-bold text-ink mb-1">{{ __('ui.profile.banner_label') }}</label>
                            <input
                                type="file"
                                name="banner"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                @change="
                                    const bFile = $event.target.files[0];
                                    if (bFile) {
                                        const bReader = new FileReader();
                                        bReader.onload = (e) => { bannerPreviewUrl = e.target.result; };
                                        bReader.readAsDataURL(bFile);
                                    }
                                "
                                class="block w-full text-xs text-muted file:mr-3 file:rounded-full file:border-0 file:bg-ink file:px-3.5 file:py-1.5 file:text-xs file:font-bold file:text-lime hover:file:bg-sky hover:file:text-white file:cursor-pointer"
                            >
                            <span class="text-[10px] text-muted block mt-1">{{ __('ui.profile.upload_hint') }}</span>
                        </div>
                    </div>

                    <!-- upload foto profil dengan preview filereader -->
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
                            <label class="block text-xs font-bold text-ink mb-1">{{ __('ui.profile.avatar_label') }}</label>
                            <input
                                type="file"
                                name="avatar"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                @change="
                                    const file = $event.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = (e) => { previewUrl = e.target.result; };
                                        reader.readAsDataURL(file);
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

                    <!-- role / profesi / hobi pilihan atau kustom -->
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">
                            {{ app()->getLocale() === 'en' ? 'Role / Profession / Hobby' : 'Role / Profesi / Hobi' }}
                        </label>
                        <input
                            type="text"
                            name="specialty_role"
                            x-model="specialtyRole"
                            maxlength="60"
                            placeholder="{{ app()->getLocale() === 'en' ? 'e.g. Software Engineer, Gamer, Student...' : 'Contoh: Software Engineer, Pelajar, Hobi Desain...' }}"
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach (\App\Models\User::availableSpecialtyRoles() as $presetRole)
                                <button
                                    type="button"
                                    @click="specialtyRole = @js($presetRole)"
                                    :class="specialtyRole === @js($presetRole) ? 'bg-sky text-white border-sky' : 'bg-[#F8FAFC] text-muted border-gray-200 hover:border-sky hover:text-sky'"
                                    class="rounded-full border px-2.5 py-1 text-[10px] font-bold transition-colors cursor-pointer"
                                >
                                    {{ $presetRole }}
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

                    <!-- password baru opsional -->
                    <div>
                        <label class="block text-xs font-bold text-ink mb-1.5">{{ app()->getLocale() === 'en' ? 'New Password (Optional)' : 'Password Baru (Opsional)' }}</label>
                        <input
                            type="password"
                            name="password"
                            placeholder="{{ app()->getLocale() === 'en' ? 'Leave blank if you do not want to change your password' : 'Biarkan kosong jika tidak ingin mengubah password' }}"
                            class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
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
    </div>
@endif
