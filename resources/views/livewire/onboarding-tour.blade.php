<div @if ($mode === 'tour' && request()->boolean('tour')) data-onboarding-replay @endif>
    @if ($mode === 'welcome')
        <div class="duty-tour-backdrop" aria-hidden="true"></div>
        <section class="duty-tour-dialog" role="dialog" aria-modal="true" aria-labelledby="onboarding-title">
            <div class="mb-4 flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-forest dark:bg-emerald-950">
                <flux:icon name="sparkles" class="size-6" />
            </div>
            <h2 id="onboarding-title" class="text-xl font-semibold text-zinc-950 dark:text-white">Welcome to DutyTrack!</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Let's take a quick tour to help you understand the dashboard and discover the tools available to you.</p>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <flux:button wire:click="dismiss">Maybe Later</flux:button>
                <flux:button variant="primary" data-tour-primary wire:click="start">Start Tour</flux:button>
            </div>
        </section>
    @elseif ($mode === 'tour')
        @php($current = $steps[$step] ?? $steps[0])
        <div class="duty-tour-backdrop" aria-hidden="true"></div>
        <section
            class="duty-tour-tooltip"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tour-title"
            data-onboarding-tour
            data-tour-target="{{ $current['target'] }}"
            data-tour-fallback="{{ $current['fallback'] ?? '' }}"
            wire:key="tour-step-{{ $step }}"
        >
            <p class="text-xs font-semibold uppercase tracking-wider text-forest">Step {{ $step + 1 }} of {{ count($steps) }}</p>
            <h2 id="tour-title" class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $current['title'] }}</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $current['text'] }}</p>
            <div class="mt-5 flex items-center justify-between gap-3">
                <button type="button" wire:click="dismiss" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Skip Tour</button>
                <div class="flex gap-2">
                    @if ($step > 0)
                        <flux:button size="sm" wire:click="previous">Back</flux:button>
                    @endif
                    <flux:button size="sm" variant="primary" data-tour-primary wire:click="next">{{ $step === count($steps) - 1 ? 'Finish' : 'Next' }}</flux:button>
                </div>
            </div>
        </section>
    @endif
</div>
