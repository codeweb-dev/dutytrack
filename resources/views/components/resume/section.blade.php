{{-- Collapsible editor section. wire:ignore.self keeps the student's open/closed choice across re-renders. --}}
@props(['title', 'icon', 'description' => null, 'open' => false, 'count' => null, 'invalid' => false])

<details wire:ignore.self @if ($open) open @endif
    {{ $attributes->class('group rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900') }}>
    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-lg px-4 py-3 select-none focus-visible:outline-2 focus-visible:outline-red-500 [&::-webkit-details-marker]:hidden">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300">
            <flux:icon :name="$icon" class="size-4" />
        </span>
        <span class="min-w-0 flex-1">
            <span class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                {{ $title }}
                @if ($invalid)
                    <span class="size-2 rounded-full bg-red-500" title="This section has fields to fix"></span>
                    <span class="sr-only">(has errors)</span>
                @endif
            </span>
            @if ($description)
                <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $description }}</span>
            @endif
        </span>
        @if (! is_null($count))
            <flux:badge size="sm">{{ $count }}</flux:badge>
        @endif
        <flux:icon name="chevron-down" class="size-4 shrink-0 text-zinc-400 transition-transform group-open:rotate-180" />
    </summary>

    <div class="space-y-4 border-t border-zinc-200 px-4 py-4 dark:border-zinc-800">
        {{ $slot }}
    </div>
</details>
