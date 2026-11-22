@extends('admin.dashboard')

@section('admin_content')
    @php
        $isEn = app()->getLocale() === 'en';
        $statusFilterOptions = [
            ['value' => 'semua', 'label' => $isEn ? 'All Status' : 'Semua Status'],
            ['value' => 'aktif', 'label' => $isEn ? 'Active' : 'Aktif', 'dot' => '#10B981'],
            ['value' => 'nonaktif', 'label' => $isEn ? 'Inactive' : 'Nonaktif', 'dot' => '#94A3B8'],
        ];
    @endphp

    <div
        x-data="{
            formOpen: {{ $errors->any() ? 'true' : 'false' }},
            isEdit: false,
            showEn: {{ old('prompt_en') || old('category_en') || old('explanation_en') ? 'true' : 'false' }},
            formAction: '{{ route('admin.streak.store') }}',
            form: {
                category_id: @js(old('category_id', '')),
                category_en: @js(old('category_en', '')),
                difficulty_id: @js(old('difficulty_id', 'Lanjutan')),
                difficulty_en: @js(old('difficulty_en', 'Advanced')),
                prompt_id: @js(old('prompt_id', '')),
                prompt_en: @js(old('prompt_en', '')),
                option_a: @js(old('option_a', '')),
                option_b: @js(old('option_b', '')),
                option_c: @js(old('option_c', '')),
                option_d: @js(old('option_d', '')),
                option_e: @js(old('option_e', '')),
                option_a_en: @js(old('option_a_en', '')),
                option_b_en: @js(old('option_b_en', '')),
                option_c_en: @js(old('option_c_en', '')),
                option_d_en: @js(old('option_d_en', '')),
                option_e_en: @js(old('option_e_en', '')),
                answer: @js(old('answer', '')),
                explanation_id: @js(old('explanation_id', '')),
                explanation_en: @js(old('explanation_en', '')),
                is_active: {{ old('is_active', '1') ? 'true' : 'false' }}
            },
            openCreate() {
                this.isEdit = false;
                this.showEn = false;
                this.formAction = '{{ route('admin.streak.store') }}';
                this.form = {
                    category_id: '',
                    category_en: '',
                    difficulty_id: 'Lanjutan',
                    difficulty_en: 'Advanced',
                    prompt_id: '',
                    prompt_en: '',
                    option_a: '',
                    option_b: '',
                    option_c: '',
                    option_d: '',
                    option_e: '',
                    option_a_en: '',
                    option_b_en: '',
                    option_c_en: '',
                    option_d_en: '',
                    option_e_en: '',
                    answer: '',
                    explanation_id: '',
                    explanation_en: '',
                    is_active: true
                };
                this.formOpen = true;
                this.$nextTick(() => {
                    this.$refs.streakFormCard?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },
            openEdit(item, updateUrl) {
                this.isEdit = true;
                this.formAction = updateUrl;
                this.form = {
                    category_id: item.category_id || '',
                    category_en: item.category_en || '',
                    difficulty_id: item.difficulty_id || 'Lanjutan',
                    difficulty_en: item.difficulty_en || 'Advanced',
                    prompt_id: item.prompt_id || '',
                    prompt_en: item.prompt_en || '',
                    option_a: item.option_a || '',
                    option_b: item.option_b || '',
                    option_c: item.option_c || '',
                    option_d: item.option_d || '',
                    option_e: item.option_e || '',
                    option_a_en: item.option_a_en || '',
                    option_b_en: item.option_b_en || '',
                    option_c_en: item.option_c_en || '',
                    option_d_en: item.option_d_en || '',
                    option_e_en: item.option_e_en || '',
                    answer: item.answer || '',
                    explanation_id: item.explanation_id || '',
                    explanation_en: item.explanation_en || '',
                    is_active: !!item.is_active
                };
                this.showEn = !!(item.prompt_en || item.category_en || item.explanation_en);
                this.formOpen = true;
                this.$nextTick(() => {
                    this.$refs.streakFormCard?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        }"
    >
        <!-- Header Utama -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h1 class="font-heading font-extrabold text-xl sm:text-2xl text-ink">
                    {{ $isEn ? 'Daily Streak Question Bank' : 'Bank Soal Tantangan Harian (Daily Streak)' }}
                </h1>
                <p class="mt-1 text-xs text-muted">
                    {{ $isEn
                        ? 'Create, edit, translate (ID/EN), and toggle A–E cybersecurity questions for daily streaks and freeze recovery without coding.'
                        : 'Tambah, edit, terjemahkan (ID/EN), dan atur soal keamanan siber pilihan A–E untuk rotasi harian tanpa mengubah kode.' }}
                </p>
            </div>

            <button
                type="button"
                @click="openCreate()"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-ink hover:bg-orange-600 text-white font-heading font-bold text-xs px-5 py-2.5 min-h-[40px] transition-colors cursor-pointer shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span>{{ $isEn ? 'Add Streak Question' : 'Tambah Soal Streak' }}</span>
            </button>
        </div>

        <!-- Ringkasan Statistik & Filter -->
        <div class="mt-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2.5 text-xs">
                <span class="inline-flex items-center gap-2 rounded-xl bg-[#F8FAFC] border border-gray-200/80 px-3.5 py-2 font-heading font-bold text-ink">
                    <span class="text-muted font-semibold">{{ $isEn ? 'Total:' : 'Total:' }}</span>
                    <span>{{ $stats['total'] }} {{ $isEn ? 'questions' : 'soal' }}</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200/80 px-3.5 py-2 font-heading font-bold text-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ $stats['active'] }} {{ $isEn ? 'Active in Rotation' : 'Aktif di Rotasi' }}</span>
                </span>
                @if ($stats['inactive'] > 0)
                    <span class="inline-flex items-center gap-2 rounded-xl bg-gray-100 border border-gray-200 px-3.5 py-2 font-heading font-bold text-muted">
                        <span>{{ $stats['inactive'] }} {{ $isEn ? 'Inactive' : 'Nonaktif' }}</span>
                    </span>
                @endif
            </div>

            <form method="GET" action="{{ route('admin.streak.index') }}" class="flex flex-wrap items-center gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="{{ $isEn ? 'Search prompt or category...' : 'Cari pertanyaan atau kategori...' }}"
                    class="w-full sm:w-56 rounded-xl bg-[#F8FAFC] border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-sky"
                >
                <div class="w-36">
                    <x-custom-select
                        name="status"
                        :value="$filterStatus"
                        :options="$statusFilterOptions"
                        :auto-submit="true"
                        variant="compact"
                    />
                </div>
                <button
                    type="submit"
                    class="rounded-xl bg-white border border-gray-200 hover:border-sky hover:text-sky px-3.5 py-2 text-xs font-heading font-bold text-ink transition-colors cursor-pointer"
                >
                    {{ $isEn ? 'Search' : 'Cari' }}
                </button>
                @if ($search !== '' || $filterStatus !== 'semua')
                    <a
                        href="{{ route('admin.streak.index') }}"
                        class="rounded-xl px-3 py-2 text-xs font-semibold text-muted hover:text-ink"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Form Tambah / Edit Soal Daily Streak -->
        <div
            x-ref="streakFormCard"
            x-show="formOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="display: none;"
            class="mt-6 rounded-2xl bg-[#F8FAFC] border border-orange-200/90 p-5 sm:p-6"
        >
            <div class="flex items-center justify-between pb-4 border-b border-gray-200/80 mb-5">
                <div>
                    <h2
                        class="font-heading font-bold text-base text-ink"
                        x-text="isEdit ? @js($isEn ? 'Edit Daily Streak Question' : 'Edit Soal Daily Streak') : @js($isEn ? 'Add New Daily Streak Question' : 'Tambah Soal Daily Streak Baru')"
                    ></h2>
                    <p class="text-[11px] text-muted mt-0.5">
                        {{ $isEn
                            ? 'Active questions automatically join the daily challenge rotation. Choose any option A–E as the correct answer.'
                            : 'Soal aktif otomatis masuk ke rotasi Tantangan Harian. Pilih bebas opsi A, B, C, D, atau E sebagai kunci jawaban benar.' }}
                    </p>
                </div>
                <button
                    type="button"
                    @click="formOpen = false"
                    aria-label="{{ $isEn ? 'Close form' : 'Tutup form' }}"
                    class="w-8 h-8 rounded-full bg-white border border-gray-200 hover:bg-gray-100 text-ink flex items-center justify-center cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="formAction" method="POST" class="space-y-5">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="answer" :value="form.answer">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="streak_category_id" class="block text-xs font-bold text-ink mb-1.5">
                            {{ $isEn ? 'Topic Category (ID)' : 'Kategori Materi' }}
                        </label>
                        <input
                            id="streak_category_id"
                            type="text"
                            name="category_id"
                            x-model="form.category_id"
                            list="streak-category-suggestions"
                            required
                            maxlength="100"
                            placeholder="{{ $isEn ? 'e.g. Cloud & Web Security' : 'Contoh: Keamanan Cloud & Web' }}"
                            class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
                        <datalist id="streak-category-suggestions">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label for="streak_difficulty_id" class="block text-xs font-bold text-ink mb-1.5">
                            {{ $isEn ? 'Difficulty Label (ID)' : 'Tingkat Kesulitan' }}
                        </label>
                        <input
                            id="streak_difficulty_id"
                            type="text"
                            name="difficulty_id"
                            x-model="form.difficulty_id"
                            required
                            maxlength="40"
                            placeholder="Lanjutan / Menengah / Pakar"
                            class="w-full rounded-xl bg-white border border-gray-200 px-3.5 py-2.5 text-xs text-ink focus:outline-none focus:border-sky"
                        >
                    </div>

                    <div class="flex items-end pb-0.5">
                        <label class="inline-flex items-center gap-2.5 rounded-xl bg-white border border-gray-200 px-3.5 py-2.5 w-full cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                x-model="form.is_active"
                                class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                            >
                            <span class="text-xs font-bold text-ink">{{ $isEn ? 'Active in Daily Rotation' : 'Aktif di Rotasi Harian' }}</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="streak_prompt_id" class="block text-xs font-bold text-ink mb-1.5">
                        {{ $isEn ? 'Question / Cyber Scenario (ID)' : 'Pertanyaan / Skenario Siber (ID)' }}
                    </label>
                    <textarea
                        id="streak_prompt_id"
                        name="prompt_id"
                        x-model="form.prompt_id"
                        rows="3"
                        required
                        maxlength="2000"
                        placeholder="{{ $isEn ? 'Write the daily challenge scenario here...' : 'Tuliskan skenario pertanyaan tingkat lanjut untuk tantangan harian...' }}"
                        class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink leading-relaxed focus:outline-none focus:border-sky resize-y"
                    ></textarea>
                </div>

                <!-- Pilihan Kunci Jawaban A-E (Bisa pilih bebas, tidak harus A) -->
                <div class="space-y-2.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-bold text-ink">
                                {{ $isEn ? 'Answer Options (A–E) & Correct Answer Key' : 'Pilihan Jawaban (A–E) & Pilih Kunci Benar' }}
                            </p>
                            <p class="text-[11px] text-muted">
                                {{ $isEn ? 'Click any letter below or click the button on an option card to set the correct answer (A, B, C, D, or E).' : 'Klik huruf di samping atau tombol pada kartu opsi untuk menentukan kunci jawaban benar (tidak harus opsi A).' }}
                            </p>
                        </div>

                        <div class="inline-flex items-center gap-1 rounded-xl bg-white border border-gray-200 p-1" role="radiogroup" aria-label="{{ $isEn ? 'Correct answer key' : 'Kunci jawaban benar' }}">
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $letter)
                                <button
                                    type="button"
                                    @click="form.answer = '{{ $letter }}'"
                                    :aria-checked="form.answer === '{{ $letter }}'"
                                    role="radio"
                                    :class="form.answer === '{{ $letter }}' ? 'bg-ink text-lime font-extrabold shadow-2xs' : 'text-muted hover:text-ink hover:bg-gray-100 font-bold'"
                                    class="w-8 h-8 rounded-lg text-xs font-heading transition-colors cursor-pointer"
                                >
                                    {{ $letter }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <p x-show="!form.answer" class="text-[11px] font-semibold text-amber-700">
                        {{ $isEn ? '* Please select one correct answer key (A, B, C, D, or E) before saving.' : '* Pilih salah satu kunci jawaban benar (A, B, C, D, atau E) sebelum menyimpan.' }}
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $letter => $field)
                            <div
                                class="rounded-xl border p-3 transition-colors"
                                :class="form.answer === '{{ $letter }}' ? 'bg-lime/20 border-lime' : 'bg-white border-gray-200'"
                            >
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <label for="streak_{{ $field }}" class="text-[11px] font-heading font-bold text-ink">
                                        {{ $isEn ? "Option {$letter}" : "Opsi {$letter}" }}
                                    </label>
                                    <button
                                        type="button"
                                        @click="form.answer = '{{ $letter }}'"
                                        :class="form.answer === '{{ $letter }}'
                                            ? 'bg-ink text-lime border-ink'
                                            : 'bg-[#F8FAFC] text-muted border-gray-200 hover:border-ink hover:text-ink'"
                                        class="rounded-full border px-2.5 py-0.5 text-[10px] font-heading font-extrabold transition-colors cursor-pointer"
                                    >
                                        <span x-text="form.answer === '{{ $letter }}' ? @js($isEn ? '✓ Correct Key' : '✓ Kunci Benar') : @js($isEn ? 'Set as Key' : 'Jadikan Kunci')"></span>
                                    </button>
                                </div>
                                <input
                                    id="streak_{{ $field }}"
                                    type="text"
                                    name="{{ $field }}"
                                    x-model="form.{{ $field }}"
                                    required
                                    maxlength="500"
                                    placeholder="{{ $isEn ? "Answer text for option {$letter}..." : "Isi jawaban opsi {$letter}..." }}"
                                    class="w-full rounded-lg bg-white border border-gray-200 px-3 py-2 text-xs text-ink focus:outline-none focus:border-sky"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="streak_explanation_id" class="block text-xs font-bold text-ink mb-1.5">
                        {{ $isEn ? 'Technical Explanation (ID — shown after answering)' : 'Penjelasan Teknis (Muncul Setelah User Menjawab)' }}
                    </label>
                    <textarea
                        id="streak_explanation_id"
                        name="explanation_id"
                        x-model="form.explanation_id"
                        rows="2"
                        required
                        maxlength="2000"
                        placeholder="{{ $isEn ? 'Explain why the selected answer is technically correct...' : 'Jelaskan alasan teknis mengapa kunci jawaban tersebut benar...' }}"
                        class="w-full rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-xs text-ink leading-relaxed focus:outline-none focus:border-sky resize-y"
                    ></textarea>
                </div>

                <!-- Bagian Terjemahan Bahasa Inggris (Bilingual EN) -->
                <div class="rounded-xl border border-gray-200/80 bg-white overflow-hidden">
                    <button
                        type="button"
                        @click="showEn = !showEn"
                        class="w-full px-4 py-3 flex items-center justify-between text-left text-xs font-heading font-bold text-ink hover:bg-gray-50 cursor-pointer"
                    >
                        <span class="flex items-center gap-2">
                            <span class="rounded-md bg-sky/10 text-sky px-2 py-0.5 text-[10px] font-extrabold uppercase">EN</span>
                            <span>{{ $isEn ? 'English Translation (Recommended for EN mode)' : 'Terjemahan Bahasa Inggris / EN (Disarankan untuk mode bahasa EN)' }}</span>
                        </span>
                        <svg
                            class="w-4 h-4 text-muted transition-transform shrink-0"
                            :class="showEn ? 'rotate-180' : ''"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>

                    <div x-show="showEn" style="display: none;" class="p-4 pt-2 border-t border-gray-100 space-y-3.5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[11px] font-bold text-ink mb-1">Category (EN)</label>
                                <input
                                    type="text"
                                    name="category_en"
                                    x-model="form.category_en"
                                    maxlength="100"
                                    placeholder="e.g. Cloud & Web Security"
                                    class="w-full rounded-xl bg-[#F8FAFC] border border-gray-200 px-3 py-2 text-xs text-ink focus:outline-none focus:border-sky"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-ink mb-1">Difficulty (EN)</label>
                                <input
                                    type="text"
                                    name="difficulty_en"
                                    x-model="form.difficulty_en"
                                    maxlength="40"
                                    placeholder="Advanced"
                                    class="w-full rounded-xl bg-[#F8FAFC] border border-gray-200 px-3 py-2 text-xs text-ink focus:outline-none focus:border-sky"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-ink mb-1">Prompt (EN)</label>
                            <textarea
                                name="prompt_en"
                                x-model="form.prompt_en"
                                rows="2"
                                maxlength="2000"
                                placeholder="English version of the question..."
                                class="w-full rounded-xl bg-[#F8FAFC] border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-sky resize-y"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach (['A' => 'option_a_en', 'B' => 'option_b_en', 'C' => 'option_c_en', 'D' => 'option_d_en', 'E' => 'option_e_en'] as $letter => $field)
                                <div>
                                    <label class="block text-[11px] font-bold text-ink mb-1">Option {{ $letter }} (EN)</label>
                                    <input
                                        type="text"
                                        name="{{ $field }}"
                                        x-model="form.{{ $field }}"
                                        maxlength="500"
                                        placeholder="English option {{ $letter }}..."
                                        class="w-full rounded-xl bg-[#F8FAFC] border border-gray-200 px-3 py-2 text-xs text-ink focus:outline-none focus:border-sky"
                                    >
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-ink mb-1">Technical Explanation (EN)</label>
                            <textarea
                                name="explanation_en"
                                x-model="form.explanation_en"
                                rows="2"
                                maxlength="2000"
                                placeholder="English technical explanation..."
                                class="w-full rounded-xl bg-[#F8FAFC] border border-gray-200 px-3.5 py-2 text-xs text-ink focus:outline-none focus:border-sky resize-y"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        @click="formOpen = false"
                        class="rounded-full border border-gray-200 bg-white px-5 py-2 text-xs font-semibold text-muted hover:text-ink cursor-pointer"
                    >
                        {{ $isEn ? 'Cancel' : 'Batal' }}
                    </button>
                    <button
                        type="submit"
                        :disabled="!form.answer"
                        class="rounded-full bg-ink hover:bg-orange-600 text-white font-heading font-bold text-xs px-6 py-2.5 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-text="isEdit ? @js($isEn ? 'Save Changes' : 'Simpan Perubahan') : @js($isEn ? 'Save Streak Question' : 'Simpan Soal Streak')"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Soal Daily Streak -->
        <div class="mt-6 overflow-x-auto rounded-2xl border border-gray-200/80">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-gray-200/80 text-[11px] font-heading font-bold uppercase tracking-wider text-muted">
                        <th class="py-3.5 px-4">{{ $isEn ? 'Prompt, Options A–E & Explanation' : 'Pertanyaan, Opsi A–E & Pembahasan' }}</th>
                        <th class="py-3.5 px-4 w-44">{{ $isEn ? 'Category & Status' : 'Kategori & Status' }}</th>
                        <th class="py-3.5 px-4 w-20 text-center">{{ $isEn ? 'Key' : 'Kunci' }}</th>
                        <th class="py-3.5 px-4 w-40 text-right">{{ $isEn ? 'Actions' : 'Aksi' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse ($questions as $item)
                        @php
                            $optsId = $item->options_id;
                            $optsEn = $item->options_en;
                            $displayItem = $item->toBankItem($isEn);
                            $hasEnTranslation = filled($item->prompt_en);
                            $editPayload = [
                                'category_id' => $item->category_id,
                                'category_en' => $item->category_en,
                                'difficulty_id' => $item->difficulty_id,
                                'difficulty_en' => $item->difficulty_en,
                                'prompt_id' => $item->prompt_id,
                                'prompt_en' => $item->prompt_en,
                                'option_a' => $optsId['A'],
                                'option_b' => $optsId['B'],
                                'option_c' => $optsId['C'],
                                'option_d' => $optsId['D'],
                                'option_e' => $optsId['E'],
                                'option_a_en' => $optsEn['A'],
                                'option_b_en' => $optsEn['B'],
                                'option_c_en' => $optsEn['C'],
                                'option_d_en' => $optsEn['D'],
                                'option_e_en' => $optsEn['E'],
                                'answer' => strtoupper($item->answer),
                                'explanation_id' => $item->explanation_id,
                                'explanation_en' => $item->explanation_en,
                                'is_active' => $item->is_active,
                            ];
                        @endphp
                        <tr class="hover:bg-[#F8FAFC]/80 transition-colors align-top {{ $item->is_active ? '' : 'opacity-65' }}">
                            <td class="py-4 px-4">
                                <p class="font-heading font-bold text-ink leading-snug">
                                    {{ $displayItem['prompt'] }}
                                </p>

                                <div class="mt-2.5 grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-[11px] text-muted">
                                    @foreach (['A', 'B', 'C', 'D', 'E'] as $letter)
                                        <div class="rounded-lg px-2.5 py-1.5 {{ strtoupper($item->answer) === $letter ? 'bg-lime/30 text-ink font-semibold border border-lime' : 'bg-gray-50' }}">
                                            <span class="font-bold">{{ $letter }}.</span> {{ $displayItem['options'][$letter] ?? '' }}
                                        </div>
                                    @endforeach
                                </div>

                                <p class="mt-2.5 rounded-lg bg-[#F8FAFC] border border-gray-200/70 px-3 py-2 text-[11px] text-muted leading-relaxed">
                                    <span class="font-bold text-ink">{{ $isEn ? 'Explanation:' : 'Pembahasan:' }}</span> {{ $displayItem['explanation'] }}
                                </p>
                            </td>

                            <td class="py-4 px-4 space-y-2">
                                <span class="font-heading font-bold text-ink block leading-snug">
                                    {{ $displayItem['category'] }}
                                </span>

                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="inline-block rounded-full bg-ink text-lime px-2.5 py-0.5 text-[10px] font-heading font-extrabold uppercase tracking-wider">
                                        {{ $displayItem['difficulty'] }}
                                    </span>
                                    <span class="inline-block rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $hasEnTranslation ? 'bg-sky/10 text-sky border-sky/25' : 'bg-gray-100 text-muted border-gray-200' }}">
                                        {{ $hasEnTranslation ? 'ID + EN' : 'ID' }}
                                    </span>
                                </div>

                                <form action="{{ route('admin.streak.toggle', $item->id) }}" method="POST" class="pt-1">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-heading font-bold transition-colors cursor-pointer {{ $item->is_active ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' : 'bg-gray-100 border-gray-200 text-muted hover:bg-gray-200' }}"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        <span>{{ $item->is_active ? ($isEn ? 'Active' : 'Aktif') : ($isEn ? 'Inactive' : 'Nonaktif') }}</span>
                                    </button>
                                </form>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex w-8 h-8 rounded-xl bg-ink text-lime font-heading font-extrabold text-xs items-center justify-center">
                                    {{ strtoupper($item->answer) }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        @click="openEdit(@js($editPayload), '{{ route('admin.streak.update', $item->id) }}')"
                                        class="inline-flex items-center gap-1 rounded-lg bg-white border border-gray-200 hover:border-sky hover:text-sky px-2.5 py-1.5 text-xs font-semibold text-ink transition-colors cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                        <span>Edit</span>
                                    </button>

                                    <form
                                        action="{{ route('admin.streak.destroy', $item->id) }}"
                                        method="POST"
                                        @submit.prevent="$dispatch('admin-confirm', { form: $el, title: @js($isEn ? 'Delete Daily Streak Question' : 'Hapus Soal Daily Streak'), message: @js($isEn ? 'Are you sure you want to delete this question from the daily streak bank?' : 'Yakin ingin menghapus soal tantangan harian ini dari bank soal?') })"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1 rounded-lg bg-white border border-gray-200 hover:border-red-300 hover:bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition-colors cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                            <span>{{ $isEn ? 'Delete' : 'Hapus' }}</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-muted">
                                {{ $isEn ? 'No questions match your search filter.' : 'Belum ada soal yang cocok dengan filter pencarian.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
