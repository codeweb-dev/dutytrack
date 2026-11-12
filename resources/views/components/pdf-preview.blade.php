@props(['source'])

{{-- Live PDF preview rendered with PDF.js; `source` is a URL or an async function (see resumePreview in resources/js/resume-builder.js). --}}
<section wire:ignore x-data="resumePreview({{ $source }})"
    {{ $attributes->class('flex h-[75dvh] min-h-0 flex-col overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm lg:h-auto dark:border-zinc-800 dark:bg-zinc-900') }}
    aria-label="Live PDF preview">
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 px-3 py-2 dark:border-zinc-800">
        <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Live PDF Preview</h2>
        <span class="text-xs text-zinc-500" x-text="pages ? `A4 · ${pages} ${pages === 1 ? 'page' : 'pages'}` : 'A4'">A4</span>
        <flux:icon name="arrow-path" class="size-4 animate-spin text-zinc-400" x-show="loading" aria-label="Updating preview" />

        <div class="ml-auto flex items-center gap-0.5">
            <flux:button size="xs" variant="subtle" icon="minus" x-on:click="setZoom(scale - 0.1)" aria-label="Zoom out" tooltip="Zoom out" />
            <span class="w-11 text-center text-xs tabular-nums text-zinc-600 dark:text-zinc-300" x-text="`${Math.round(scale * 100)}%`" aria-live="polite">100%</span>
            <flux:button size="xs" variant="subtle" icon="plus" x-on:click="setZoom(scale + 0.1)" aria-label="Zoom in" tooltip="Zoom in" />
            <flux:button size="xs" variant="subtle" icon="arrows-pointing-out" x-on:click="setZoom('fit')"
                x-bind:aria-pressed="zoom === 'fit'" x-bind:class="zoom === 'fit' && 'text-red-600! dark:text-red-400!'">
                Fit width
            </flux:button>
        </div>
    </div>

    <div x-ref="viewport" class="relative min-h-0 flex-1 overflow-auto bg-zinc-100 p-4 dark:bg-zinc-950/60">
        <div x-ref="pages" class="mx-auto flex w-max flex-col items-center gap-4"></div>

        <div x-show="!pages && loading" class="mx-auto aspect-[210/297] w-full max-w-2xl animate-pulse rounded bg-white/70 dark:bg-zinc-800"></div>

        <div x-show="error" x-cloak class="absolute inset-x-4 top-4 flex items-center justify-between gap-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 shadow dark:bg-red-500/10 dark:text-red-300" role="alert">
            The preview couldn't be updated.
            <flux:button size="xs" x-on:click="load()">Retry</flux:button>
        </div>
    </div>
</section>
