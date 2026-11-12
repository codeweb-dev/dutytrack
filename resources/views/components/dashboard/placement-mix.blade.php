@props(['mix', 'description', 'title' => 'Placement Mix', 'centerLabel' => 'Placed students', 'emptyLabel' => 'No placements yet'])

<flux:card class="flex flex-col">
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            <flux:text class="mt-2">{{ $description }}</flux:text>
        </div>

        <flux:badge color="zinc" inset="top bottom">Overview</flux:badge>
    </div>

    <div class="relative mx-auto mt-6 size-56" x-data="{ active: null, tip: { x: 0, y: 0 }, segments: @js($mix['segments']) }"
        @mousemove="const r = $el.getBoundingClientRect(); tip = { x: $event.clientX - r.left, y: $event.clientY - r.top }">
        <svg viewBox="0 0 42 42" class="size-full -rotate-90" role="img"
            aria-label="{{ $title }}: {{ collect($mix['segments'])->map(fn ($s) => $s['label'].' '.$s['value'])->join(', ') }}">
            <circle cx="21" cy="21" r="15.915" fill="none" stroke-width="7" class="stroke-zinc-100 dark:stroke-zinc-800" />
            @if ($mix['total'] > 0)
                @foreach ($mix['segments'] as $segment)
                    <circle cx="21" cy="21" r="15.915" fill="none" stroke-width="7" class="{{ $segment['stroke'] }} cursor-pointer transition-opacity"
                        stroke-dasharray="{{ $segment['dash'] }}" stroke-dashoffset="{{ $segment['offset'] }}"
                        :class="active !== null && active !== {{ $loop->index }} && 'opacity-40'"
                        @mouseenter="active = {{ $loop->index }}" @mouseleave="active = null" />
                @endforeach
            @endif
        </svg>

        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
            <div class="text-3xl font-semibold tracking-tight text-zinc-900 tabular-nums dark:text-white">{{ number_format($mix['total']) }}</div>
            <flux:text>{{ $mix['total'] > 0 ? $centerLabel : $emptyLabel }}</flux:text>
        </div>

        <template x-if="active !== null">
            <div class="pointer-events-none absolute z-10 flex items-center gap-2 whitespace-nowrap rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-lg dark:border-zinc-600 dark:bg-zinc-800"
                :style="`left: ${tip.x + 12}px; top: ${tip.y + 12}px`">
                <span class="size-2.5 rounded-full" :class="segments[active].dot"></span>
                <span class="text-zinc-600 dark:text-zinc-300" x-text="segments[active].label"></span>
                <span class="ml-2 font-medium text-zinc-900 tabular-nums dark:text-white" x-text="`${segments[active].value} (${segments[active].percentage}%)`"></span>
            </div>
        </template>
    </div>

    <div class="mt-auto flex justify-center gap-6 pt-6">
        @foreach ($mix['segments'] as $segment)
            <div class="flex items-center gap-2 text-sm">
                <span class="size-2.5 rounded-full {{ $segment['dot'] }}"></span>
                <span class="text-zinc-600 dark:text-zinc-300">{{ $segment['label'] }}</span>
                <span class="font-medium text-zinc-900 tabular-nums dark:text-white">{{ number_format($segment['value']) }}</span>
            </div>
        @endforeach
    </div>
</flux:card>
