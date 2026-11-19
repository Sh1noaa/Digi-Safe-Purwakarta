@props([
    'name' => null,
    'value' => '',
    'model' => null,
    'options' => [],
    'placeholder' => 'Pilih opsi...',
    'autoSubmit' => false,
    'variant' => 'default',
    'align' => 'left',
    'widthClass' => 'w-full',
])

@php
    $normalized = collect($options)->map(function ($opt, $key) {
        if (is_array($opt)) {
            return [
                'value' => (string) ($opt['value'] ?? $key),
                'label' => (string) ($opt['label'] ?? $opt['value'] ?? $key),
                'dot' => $opt['dot'] ?? null,
                'desc' => $opt['desc'] ?? null,
            ];
        }

        return [
            'value' => (string) $key,
            'label' => (string) $opt,
            'dot' => null,
            'desc' => null,
        ];
    })->values()->all();

    $triggerStyle = match ($variant) {
        'lime' => 'bg-lime/30 border-lime hover:border-ink text-ink font-extrabold',
        'compact' => 'bg-white border-gray-200 hover:border-sky/60 text-ink font-semibold py-1.5 px-3',
        default => 'bg-white border-gray-200 hover:border-sky/60 text-ink font-semibold py-2.5 px-3.5',
    };
@endphp

<div
    x-data="{
        open: false,
        options: @js($normalized),
        localVal: @js((string) $value),
        get currentVal() {
            return {{ $model ? "String({$model} ?? '')" : 'this.localVal' }};
        },
        get selectedOption() {
            return this.options.find(o => o.value === this.currentVal) || null;
        },
        choose(val) {
            @if ($model)
                {{ $model }} = val;
            @else
                this.localVal = val;
            @endif
            this.open = false;
            @if ($autoSubmit)
                this.$nextTick(() => {
                    const form = this.$el.closest('form');
                    if (form) form.submit();
                });
            @endif
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative {{ $widthClass }}"
>
    @if ($name)
        <input type="hidden" name="{{ $name }}" :value="currentVal">
    @endif

    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open"
        :class="open ? 'border-sky ring-2 ring-sky/20' : ''"
        class="{{ $widthClass }} rounded-xl border {{ $triggerStyle }} text-xs flex items-center justify-between gap-2.5 shadow-2xs transition-all cursor-pointer text-left"
    >
        <span class="flex items-center gap-2 min-w-0 truncate">
            <template x-if="selectedOption && selectedOption.dot">
                <span class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-black/10" :style="'background-color:' + selectedOption.dot"></span>
            </template>
            <span class="truncate" x-text="selectedOption ? selectedOption.label : @js($placeholder)"></span>
        </span>
        <svg
            class="w-3.5 h-3.5 text-muted shrink-0 transition-transform duration-200"
            :class="open ? 'rotate-180 text-sky' : ''"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.4"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <polyline points="6 9 12 15 18 9" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-[0.98]"
        style="display: none;"
        class="absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} mt-1.5 min-w-full w-max max-w-[290px] max-h-64 overflow-y-auto rounded-2xl bg-white border border-gray-200/90 shadow-[0_18px_42px_rgba(15,23,42,0.14)] p-1.5 z-50 space-y-0.5"
    >
        <template x-for="opt in options" :key="opt.value">
            <button
                type="button"
                @click="choose(opt.value)"
                :class="opt.value === currentVal ? 'bg-[#EFF6FF] text-sky font-bold' : 'text-ink hover:bg-[#F8FAFC] font-medium'"
                class="w-full rounded-xl px-3 py-2 text-left text-xs flex items-center justify-between gap-2.5 transition-colors cursor-pointer"
            >
                <span class="flex items-center gap-2 min-w-0">
                    <template x-if="opt.dot">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-black/10" :style="'background-color:' + opt.dot"></span>
                    </template>
                    <span class="min-w-0">
                        <span class="block truncate" x-text="opt.label"></span>
                        <template x-if="opt.desc">
                            <span class="block text-[10px] font-normal text-muted truncate" x-text="opt.desc"></span>
                        </template>
                    </span>
                </span>
                <svg
                    x-show="opt.value === currentVal"
                    class="w-3.5 h-3.5 text-sky shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </button>
        </template>
    </div>
</div>
