<div
    {{ $attributes->merge([
        'class' => 'rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900',
    ]) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $title }}
            </p>

            <h3 class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                {{ $value }}
            </h3>

            @if ($change)
                <p class="mt-3 text-sm font-medium {{ $changeColor }}">
                    {{ $change }}
                </p>
            @endif
        </div>

        <flux:icon name="{{ $icon }}" />
    </div>
</div>
