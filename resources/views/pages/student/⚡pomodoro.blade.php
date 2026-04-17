<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div
    x-data="pomodoroTimer()"
    x-init="init()"
    class="flex h-full w-full flex-1 flex-col gap-5"
>
    <div class="space-y-1">
        <flux:heading size="xl">
            Pomodoro Timer
        </flux:heading>

        <flux:subheading size="lg">
            Stay focused with a clean study timer built in the Flux style.
        </flux:subheading>
    </div>

    <div class="grid flex-1 gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(20rem,0.95fr)]">
        <section
            class="relative overflow-hidden rounded-[2rem] border border-zinc-200/70 bg-white p-5 shadow-[0_30px_80px_-45px_rgba(15,23,42,0.45)] dark:border-white/10 dark:bg-zinc-900"
        >
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(248,113,113,0.12),_transparent_32%),radial-gradient(circle_at_top_right,_rgba(59,130,246,0.12),_transparent_30%),radial-gradient(circle_at_bottom,_rgba(16,185,129,0.10),_transparent_38%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(248,113,113,0.16),_transparent_32%),radial-gradient(circle_at_top_right,_rgba(59,130,246,0.14),_transparent_30%),radial-gradient(circle_at_bottom,_rgba(16,185,129,0.14),_transparent_38%)]"
            ></div>

            <div class="relative flex h-full flex-col gap-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="preset in presets" :key="preset.key">
                                <button
                                    type="button"
                                    x-on:click="switchMode(preset.key)"
                                    x-bind:class="mode === preset.key
                                        ? 'border-transparent bg-zinc-950 text-white shadow-lg shadow-zinc-950/15 dark:bg-white dark:text-zinc-950'
                                        : 'border-zinc-200/80 bg-white/80 text-zinc-600 hover:border-zinc-300 hover:text-zinc-950 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white'"
                                    class="inline-flex items-center gap-2 rounded-2xl border px-4 py-2 text-sm font-medium transition"
                                >
                                    <span
                                        class="size-2.5 rounded-full"
                                        x-bind:class="preset.dot"
                                    ></span>
                                    <span x-text="preset.label"></span>
                                </button>
                            </template>
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm font-medium uppercase tracking-[0.24em] text-zinc-400 dark:text-zinc-500">
                                Current Focus
                            </p>

                            <h2 class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white sm:text-4xl">
                                <span x-text="activeLabel"></span>
                            </h2>

                            <p class="max-w-2xl text-sm leading-7 text-zinc-600 dark:text-zinc-300">
                                Use focused work sessions and short recovery breaks to keep momentum through classwork,
                                reports, or OJT documentation.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-zinc-200/70 bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Sessions</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white" x-text="completedSessions"></p>
                        </div>

                        <div class="rounded-2xl border border-zinc-200/70 bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Focus Time</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white" x-text="focusMinutes + 'm'"></p>
                        </div>

                        <div class="rounded-2xl border border-zinc-200/70 bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-white/5 col-span-2 sm:col-span-1">
                            <p class="text-xs uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Progress</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white" x-text="Math.round(progress * 100) + '%'"></p>
                        </div>
                    </div>
                </div>

                <div class="grid items-center gap-6 lg:grid-cols-[minmax(18rem,24rem)_minmax(0,1fr)]">
                    <div class="mx-auto flex w-full max-w-[24rem] justify-center">
                        <div class="relative">
                            <svg class="h-[21rem] w-[21rem] -rotate-90 sm:h-[24rem] sm:w-[24rem]" viewBox="0 0 240 240" aria-hidden="true">
                                <circle
                                    cx="120"
                                    cy="120"
                                    r="96"
                                    class="fill-none stroke-zinc-200/90 dark:stroke-white/10"
                                    stroke-width="18"
                                />
                                <circle
                                    cx="120"
                                    cy="120"
                                    r="96"
                                    class="fill-none stroke-[url(#timerGradient)] transition-all duration-500"
                                    stroke-width="18"
                                    stroke-linecap="round"
                                    stroke-dasharray="603.19"
                                    x-bind:stroke-dashoffset="603.19 - (603.19 * progress)"
                                />
                                <defs>
                                    <linearGradient id="timerGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#f87171"></stop>
                                        <stop offset="55%" stop-color="#60a5fa"></stop>
                                        <stop offset="100%" stop-color="#34d399"></stop>
                                    </linearGradient>
                                </defs>
                            </svg>

                            <div class="absolute inset-0 flex flex-col items-center justify-center rounded-full">
                                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-zinc-200/70 bg-white/75 px-3 py-1 text-xs font-medium uppercase tracking-[0.22em] text-zinc-500 backdrop-blur dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                                    <span class="size-2.5 rounded-full" x-bind:class="activeDot"></span>
                                    <span x-text="isRunning ? 'In session' : 'Ready to focus'"></span>
                                </div>

                                <p class="text-[4rem] font-semibold leading-none tracking-[-0.06em] text-zinc-950 dark:text-white sm:text-[4.75rem]" x-text="formattedTime"></p>

                                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    <span x-text="Math.ceil(timeLeft / 60)"></span>
                                    <span x-text="Math.ceil(timeLeft / 60) === 1 ? 'minute left' : 'minutes left'"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-[1.75rem] border border-zinc-200/70 bg-white/75 p-5 backdrop-blur dark:border-white/10 dark:bg-white/5">
                            <div class="flex flex-wrap gap-3">
                                <flux:button x-on:click="toggleTimer()" color="red" variant="primary">
                                    <flux:icon icon="play" x-show="!isRunning" class="size-4" />
                                    <flux:icon icon="pause" x-show="isRunning" class="size-4" />
                                    <span x-text="isRunning ? 'Pause Session' : 'Start Session'"></span>
                                </flux:button>

                                <flux:button x-on:click="resetTimer()" variant="outline" icon="arrow-path">
                                    Reset
                                </flux:button>

                                <flux:button x-on:click="skipSession()" variant="ghost" icon="forward">
                                    Skip
                                </flux:button>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <button
                                    type="button"
                                    x-on:click="adjustTime(-60)"
                                    class="rounded-2xl border border-zinc-200/80 bg-white px-4 py-3 text-left transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10"
                                >
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Adjust</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">-1 min</p>
                                </button>

                                <button
                                    type="button"
                                    x-on:click="adjustTime(60)"
                                    class="rounded-2xl border border-zinc-200/80 bg-white px-4 py-3 text-left transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10"
                                >
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Adjust</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">+1 min</p>
                                </button>

                                <div class="rounded-2xl border border-zinc-200/80 bg-white px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Cycle</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">
                                        <span x-text="cycleCount"></span> / 4
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <template x-for="preset in presets" :key="preset.key + '-panel'">
                                <button
                                    type="button"
                                    x-on:click="switchMode(preset.key)"
                                    x-bind:class="mode === preset.key
                                        ? 'border-transparent bg-zinc-950 text-white shadow-lg shadow-zinc-950/15 dark:bg-white dark:text-zinc-950'
                                        : 'border-zinc-200/80 bg-white/80 text-zinc-700 hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20'"
                                    class="rounded-[1.35rem] border p-4 text-left transition"
                                >
                                    <p class="text-xs uppercase tracking-[0.22em] opacity-60" x-text="preset.short"></p>
                                    <p class="mt-3 text-2xl font-semibold" x-text="preset.minutes + ' min'"></p>
                                    <p class="mt-2 text-sm opacity-75" x-text="preset.description"></p>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="grid gap-5">
            <flux:card class="rounded-[1.75rem] border-zinc-200/70 bg-white/90 p-0 dark:border-white/10 dark:bg-zinc-900/90">
                <div class="border-b border-zinc-200/70 px-5 py-4 dark:border-white/10">
                    <flux:heading size="lg">Session Plan</flux:heading>
                    <flux:subheading size="sm">A simple rhythm for focused student work.</flux:subheading>
                </div>

                <div class="space-y-3 p-5">
                    <template x-for="(item, index) in timeline" :key="item.title">
                        <div class="flex gap-3 rounded-2xl border border-zinc-200/70 bg-zinc-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                            <div class="flex flex-col items-center pt-1">
                                <div
                                    class="flex size-8 items-center justify-center rounded-full text-xs font-semibold"
                                    x-bind:class="index === activeTimelineIndex
                                        ? 'bg-red-500 text-white'
                                        : 'bg-zinc-200 text-zinc-700 dark:bg-white/10 dark:text-zinc-300'"
                                    x-text="index + 1"
                                ></div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-zinc-950 dark:text-white" x-text="item.title"></p>
                                    <span class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500" x-text="item.length"></span>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300" x-text="item.description"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </flux:card>

            <flux:card class="rounded-[1.75rem] border-zinc-200/70 bg-white/90 p-0 dark:border-white/10 dark:bg-zinc-900/90">
                <div class="border-b border-zinc-200/70 px-5 py-4 dark:border-white/10">
                    <flux:heading size="lg">Focus Tips</flux:heading>
                    <flux:subheading size="sm">Small habits that make the timer more effective.</flux:subheading>
                </div>

                <div class="space-y-3 p-5">
                    <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-semibold text-zinc-950 dark:text-white">Pick one concrete task</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                            Decide what “done” looks like before you press start so the session stays intentional.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-semibold text-zinc-950 dark:text-white">Use the break properly</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                            Stand up, stretch, drink water, then come back with a clear head for the next block.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-semibold text-zinc-950 dark:text-white">Protect your attention</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                            Silence notifications and keep only the tabs or files you need for the current session.
                        </p>
                    </div>
                </div>
            </flux:card>
        </aside>
    </div>

    <script>
        function pomodoroTimer() {
            return {
                presets: [
                    {
                        key: 'focus',
                        label: 'Focus',
                        short: 'Work',
                        minutes: 25,
                        seconds: 1500,
                        description: 'Deep work for your current priority.',
                        dot: 'bg-red-400'
                    },
                    {
                        key: 'short_break',
                        label: 'Short Break',
                        short: 'Break',
                        minutes: 5,
                        seconds: 300,
                        description: 'Quick reset between work sprints.',
                        dot: 'bg-sky-400'
                    },
                    {
                        key: 'long_break',
                        label: 'Long Break',
                        short: 'Recover',
                        minutes: 15,
                        seconds: 900,
                        description: 'Recharge after a set of focus sessions.',
                        dot: 'bg-emerald-400'
                    }
                ],
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
                mode: 'focus',
                duration: 1500,
                timeLeft: 1500,
                isRunning: false,
                timer: null,
                completedSessions: 0,
                focusMinutes: 0,
                cycleCount: 1,

                init() {
                    this.loadState();

                    window.addEventListener('beforeunload', () => this.persistState());
                },

                get formattedTime() {
                    const minutes = String(Math.floor(this.timeLeft / 60)).padStart(2, '0');
                    const seconds = String(this.timeLeft % 60).padStart(2, '0');

                    return `${minutes}:${seconds}`;
                },

                get progress() {
                    if (this.duration <= 0) {
                        return 0;
                    }

                    return Math.min(1, Math.max(0, (this.duration - this.timeLeft) / this.duration));
                },

                get activeLabel() {
                    const preset = this.presets.find((item) => item.key === this.mode);
                    return preset ? preset.label : 'Focus';
                },

                get activeDot() {
                    const preset = this.presets.find((item) => item.key === this.mode);
                    return preset ? preset.dot : 'bg-red-400';
                },

                get activeTimelineIndex() {
                    if (this.mode === 'short_break') {
                        return 1;
                    }

                    if (this.mode === 'long_break') {
                        return 3;
                    }

                    return 0;
                },

                toggleTimer() {
                    if (this.isRunning) {
                        this.pauseTimer();
                        return;
                    }

                    this.startTimer();
                },

                startTimer() {
                    if (this.isRunning) {
                        return;
                    }

                    this.isRunning = true;

                    this.timer = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft -= 1;
                            this.persistState();
                            return;
                        }

                        this.completeSession();
                    }, 1000);
                },

                pauseTimer() {
                    this.isRunning = false;
                    clearInterval(this.timer);
                    this.timer = null;
                    this.persistState();
                },

                resetTimer() {
                    this.pauseTimer();
                    this.timeLeft = this.duration;
                    this.persistState();
                },

                skipSession() {
                    this.pauseTimer();
                    this.completeSession();
                },

                adjustTime(amount) {
                    this.pauseTimer();

                    this.duration = Math.max(60, this.duration + amount);
                    this.timeLeft = Math.max(0, Math.min(this.timeLeft + amount, this.duration));

                    if (this.timeLeft === 0) {
                        this.timeLeft = this.duration;
                    }

                    this.persistState();
                },

                switchMode(key) {
                    const preset = this.presets.find((item) => item.key === key);

                    if (!preset) {
                        return;
                    }

                    this.pauseTimer();
                    this.mode = preset.key;
                    this.duration = preset.seconds;
                    this.timeLeft = preset.seconds;
                    this.persistState();
                },

                completeSession() {
                    const completedMode = this.mode;

                    this.pauseTimer();

                    if (completedMode === 'focus') {
                        this.completedSessions += 1;
                        this.focusMinutes += Math.round(this.duration / 60);

                        if (this.cycleCount >= 4) {
                            this.cycleCount = 1;
                            this.switchMode('long_break');
                        } else {
                            this.cycleCount += 1;
                            this.switchMode('short_break');
                        }
                    } else {
                        this.switchMode('focus');
                    }

                    if (window.$flux?.toast) {
                        window.$flux.toast({
                            heading: 'Session complete',
                            text: completedMode === 'focus'
                                ? 'Nice work. Time for a well-earned break.'
                                : 'Break finished. Ready for another focus block.'
                        });
                    }

                    this.persistState();
                },

                persistState() {
                    const state = {
                        mode: this.mode,
                        duration: this.duration,
                        timeLeft: this.timeLeft,
                        completedSessions: this.completedSessions,
                        focusMinutes: this.focusMinutes,
                        cycleCount: this.cycleCount
                    };

                    localStorage.setItem('student-pomodoro-state', JSON.stringify(state));
                },

                loadState() {
                    const stored = localStorage.getItem('student-pomodoro-state');

                    if (!stored) {
                        return;
                    }

                    try {
                        const state = JSON.parse(stored);

                        this.mode = state.mode ?? this.mode;
                        this.duration = state.duration ?? this.duration;
                        this.timeLeft = state.timeLeft ?? this.timeLeft;
                        this.completedSessions = state.completedSessions ?? this.completedSessions;
                        this.focusMinutes = state.focusMinutes ?? this.focusMinutes;
                        this.cycleCount = state.cycleCount ?? this.cycleCount;
                    } catch (error) {
                        localStorage.removeItem('student-pomodoro-state');
                    }
                }
            };
        }
    </script>
</div>
