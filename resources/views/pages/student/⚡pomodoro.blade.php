<?php

use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Productivity', 'href' => '#'], ['label' => 'Pomodoro Timer']];

    public string $title = 'Pomodoro Timer';
    public string $description = 'Stay focused with timed work sessions and short recovery breaks.';
};
?>

<div x-data="pomodoroPage()" class="flex flex-col gap-8">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid items-start gap-6 lg:grid-cols-5">
        <div class="flex min-w-0 flex-col gap-6 lg:col-span-3">
            <flux:card>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg" x-text="$store.pomodoro.activeLabel"></flux:heading>
                        <flux:text class="mt-2">
                            Cycle <span x-text="$store.pomodoro.cycleCount"></span> of 4
                        </flux:text>
                    </div>

                    <flux:radio.group x-model="$store.pomodoro.selectedMode" variant="segmented" size="sm" aria-label="Timer mode">
                        <flux:radio value="focus" label="Focus" />
                        <flux:radio value="short_break" label="Short Break" />
                        <flux:radio value="long_break" label="Long Break" />
                    </flux:radio.group>
                </div>

                <div class="mt-8 flex justify-center">
                    <div class="relative size-64 sm:size-72">
                        <svg class="size-full -rotate-90" viewBox="0 0 240 240" aria-hidden="true">
                            <circle cx="120" cy="120" r="96" class="fill-none stroke-zinc-200 dark:stroke-zinc-600" stroke-width="10" />
                            <circle
                                cx="120"
                                cy="120"
                                r="96"
                                class="fill-none transition-all duration-500"
                                x-bind:class="$store.pomodoro.activePreset.stroke"
                                stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="603.19"
                                x-bind:stroke-dashoffset="603.19 - (603.19 * $store.pomodoro.progress)"
                            />
                        </svg>

                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                <span class="size-2 rounded-full" x-bind:class="$store.pomodoro.activePreset.dot"></span>
                                <span x-text="$store.pomodoro.isRunning ? 'In session' : 'Paused'"></span>
                            </div>

                            <p class="mt-2 text-6xl font-semibold tracking-tight tabular-nums text-zinc-950 dark:text-white" x-text="$store.pomodoro.formattedTime"></p>

                            <flux:text class="mt-2">
                                <span x-text="Math.ceil($store.pomodoro.timeLeft / 60)"></span>
                                <span x-text="Math.ceil($store.pomodoro.timeLeft / 60) === 1 ? 'minute left' : 'minutes left'"></span>
                            </flux:text>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-center gap-3">
                    <flux:button x-on:click="$store.pomodoro.adjustTime(-60)" variant="ghost" icon="minus" aria-label="Remove one minute" />

                    <flux:button x-on:click="$store.pomodoro.toggleTimer()" variant="primary" class="min-w-36">
                        <flux:icon name="play" x-show="!$store.pomodoro.isRunning" class="size-4" />
                        <flux:icon name="pause" x-show="$store.pomodoro.isRunning" class="size-4" />
                        <span x-text="$store.pomodoro.isRunning ? 'Pause' : 'Start'"></span>
                    </flux:button>

                    <flux:button x-on:click="$store.pomodoro.adjustTime(60)" variant="ghost" icon="plus" aria-label="Add one minute" />
                </div>

                <div class="mt-3 flex justify-center gap-2">
                    <flux:button x-on:click="$store.pomodoro.resetTimer()" variant="subtle" size="sm" icon="arrow-path">Reset</flux:button>
                    <flux:button x-on:click="$store.pomodoro.skipSession()" variant="subtle" size="sm" icon="forward">Skip</flux:button>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg">Focus Tips</flux:heading>
                <flux:text class="mt-2">Small habits that make the timer more effective.</flux:text>

                <div class="mt-6 grid gap-6 sm:grid-cols-3">
                    @foreach ([
                        ['icon' => 'check-circle', 'title' => 'Pick one concrete task', 'text' => 'Decide what “done” looks like before you press start so the session stays intentional.'],
                        ['icon' => 'heart', 'title' => 'Use the break properly', 'text' => 'Stand up, stretch, drink water, then come back with a clear head for the next block.'],
                        ['icon' => 'bell-slash', 'title' => 'Protect your attention', 'text' => 'Silence notifications and keep only the tabs or files you need for the current session.'],
                    ] as $tip)
                        <div>
                            <div class="inline-flex rounded-2xl bg-zinc-50 p-3 text-zinc-900 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700">
                                <flux:icon :name="$tip['icon']" class="size-5" />
                            </div>
                            <div class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ $tip['title'] }}</div>
                            <flux:text class="mt-1">{{ $tip['text'] }}</flux:text>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        </div>

        <section class="grid gap-4 lg:col-span-2">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:card>
                    <div class="flex items-start justify-between gap-4">
                        <flux:heading size="lg">Sessions</flux:heading>
                        <div class="rounded-2xl bg-zinc-50 p-3 text-zinc-900 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700">
                            <flux:icon name="check-badge" class="size-5" />
                        </div>
                    </div>
                    <div class="mt-6 text-5xl font-semibold tracking-tight text-zinc-950 dark:text-white" x-text="$store.pomodoro.completedSessions"></div>
                    <flux:text class="mt-6">Focus blocks completed</flux:text>
                </flux:card>

                <flux:card>
                    <div class="flex items-start justify-between gap-4">
                        <flux:heading size="lg">Focus Time</flux:heading>
                        <div class="rounded-2xl bg-zinc-50 p-3 text-zinc-900 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700">
                            <flux:icon name="clock" class="size-5" />
                        </div>
                    </div>
                    <div class="mt-6 text-5xl font-semibold tracking-tight text-zinc-950 dark:text-white" x-text="$store.pomodoro.focusMinutes + 'm'"></div>
                    <flux:text class="mt-6">Total minutes focused</flux:text>
                </flux:card>
            </div>

            <flux:card>
                <flux:heading size="lg">Session Plan</flux:heading>
                <flux:text class="mt-2">A simple rhythm for focused student work.</flux:text>

                <ol class="mt-6 space-y-5">
                    <template x-for="(item, index) in timeline" :key="item.title">
                        <li class="flex gap-4">
                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold ring-2"
                                x-bind:class="index === activeTimelineIndex
                                    ? 'bg-red-600 text-white ring-red-600'
                                    : 'bg-white text-zinc-500 ring-zinc-300 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700'"
                                x-text="index + 1"
                            ></div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white" x-text="item.title"></div>
                                    <flux:text size="sm" x-text="item.length"></flux:text>
                                </div>
                                <flux:text class="mt-1" x-text="item.description"></flux:text>
                            </div>
                        </li>
                    </template>
                </ol>
            </flux:card>
        </section>
    </div>

    <script>
        function pomodoroPage() {
            return {
                timeline: [
                    {
                        title: 'Focus Sprint',
                        length: '25 min',
                        description: 'Work on a single assignment, report, or documentation task without switching context.'
                    },
                    {
                        title: 'Short Reset',
                        length: '5 min',
                        description: 'Step away for a moment, breathe, and let your attention recover.'
                    },
                    {
                        title: 'Repeat 4 Times',
                        length: 'Cycle',
                        description: 'After four focused sessions, reward yourself with a proper longer break.'
                    },
                    {
                        title: 'Long Break',
                        length: '15 min',
                        description: 'Reset fully before the next round of concentrated work.'
                    }
                ],
                get activeTimelineIndex() {
                    return { short_break: 1, long_break: 3 }[this.$store.pomodoro.mode] ?? 0;
                }
            };
        }
    </script>
</div>
