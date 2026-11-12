@props(['attendance', 'description'])

<flux:card>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:heading size="lg">Weekly Attendance</flux:heading>
            <flux:text class="mt-2">{{ $description }}</flux:text>
        </div>

        <flux:badge color="zinc" inset="top bottom">Last 7 Days</flux:badge>
    </div>

    <div class="mt-8 flex gap-3" x-data="{ active: null, points: @js($attendance['points']) }">
        <div class="relative h-60 w-8 shrink-0 text-right text-xs text-zinc-500 dark:text-zinc-400">
            @foreach ($attendance['ticks'] as $tick)
                <span class="absolute right-0 translate-y-1/2" style="bottom: {{ $loop->index * 100 / ($loop->count - 1) }}%">{{ number_format($tick) }}</span>
            @endforeach
        </div>

        <div class="relative flex-1 pb-7">
            <div class="relative h-60"
                @mousemove="const r = $el.getBoundingClientRect(); active = Math.round(Math.min(Math.max(($event.clientX - r.left) / r.width, 0), 1) * (points.length - 1))"
                @mouseleave="active = null">
                @foreach ($attendance['ticks'] as $tick)
                    <div class="absolute inset-x-0 border-t {{ $loop->first ? 'border-zinc-300 dark:border-zinc-600' : 'border-zinc-100 dark:border-zinc-700/60' }}"
                        style="bottom: {{ $loop->index * 100 / ($loop->count - 1) }}%"></div>
                @endforeach

                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 size-full overflow-visible" role="img"
                    aria-label="Attendance logs: {{ collect($attendance['points'])->map(fn ($p) => $p['label'].' '.$p['value'])->join(', ') }}">
                    <defs>
                        <linearGradient id="attendance-fill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#ef4444" stop-opacity="0.35" />
                            <stop offset="100%" stop-color="#ef4444" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <path d="{{ $attendance['area'] }}" fill="url(#attendance-fill)" />
                    <path d="{{ $attendance['line'] }}" fill="none" class="stroke-red-500" stroke-width="2.5" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                </svg>

                <template x-if="active !== null">
                    <div>
                        <div class="pointer-events-none absolute inset-y-0 border-l border-dashed border-zinc-400 dark:border-zinc-500" :style="`left: ${points[active].x}%`"></div>
                        <div class="pointer-events-none absolute size-3 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-red-500 shadow dark:border-zinc-900"
                            :style="`left: ${points[active].x}%; top: ${points[active].y}%`"></div>
                        <div class="pointer-events-none absolute top-4 z-10 min-w-40 overflow-hidden rounded-lg border border-zinc-200 bg-white text-sm shadow-lg dark:border-zinc-600 dark:bg-zinc-800"
                            :style="points[active].x > 60 ? `right: calc(${100 - points[active].x}% + 12px)` : `left: calc(${points[active].x}% + 12px)`">
                            <div class="border-b border-zinc-200 px-3 py-2 font-medium text-zinc-900 dark:border-zinc-600 dark:text-white" x-text="points[active].date"></div>
                            <div class="flex items-center gap-2 px-3 py-2">
                                <span class="size-2.5 rounded-full bg-red-500"></span>
                                <span class="text-zinc-600 dark:text-zinc-300">Attendance</span>
                                <span class="ml-auto font-medium text-zinc-900 tabular-nums dark:text-white" x-text="points[active].value"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            @foreach ($attendance['points'] as $point)
                <span class="absolute bottom-0 -translate-x-1/2 whitespace-nowrap text-xs text-zinc-500 dark:text-zinc-400" style="left: {{ $point['x'] }}%">{{ $point['label'] }}</span>
            @endforeach
        </div>
    </div>
</flux:card>
