<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div
    x-data="petStreakApp()"
    x-init="init()"
    class="flex h-full w-full flex-1 flex-col gap-5"
>
    <div class="space-y-1">
        <flux:heading size="xl">
            Pet Streak
        </flux:heading>

        <flux:subheading size="lg">
            Keep your momentum alive with a virtual companion that grows as your study streak stays strong.
        </flux:subheading>
    </div>

    <div class="grid flex-1 gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.95fr)]">
        <section
            class="relative overflow-hidden rounded-[2rem] border border-zinc-200/70 bg-white p-5 shadow-[0_30px_80px_-45px_rgba(15,23,42,0.45)] dark:border-white/10 dark:bg-zinc-900"
        >
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.16),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(96,165,250,0.16),_transparent_30%),radial-gradient(circle_at_bottom,_rgba(52,211,153,0.18),_transparent_38%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.14),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(96,165,250,0.14),_transparent_30%),radial-gradient(circle_at_bottom,_rgba(52,211,153,0.16),_transparent_38%)]"
            ></div>

            <div class="relative flex h-full flex-col gap-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/50 bg-amber-100/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
                            <span class="size-2 rounded-full bg-amber-500"></span>
                            Study Companion Active
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white sm:text-4xl">
                                <span x-text="pet.name"></span>
                                is
                                <span x-text="petMood"></span>
                            </h2>

                            <p class="max-w-2xl text-sm leading-7 text-zinc-600 dark:text-zinc-300">
                                Complete your focus sessions and daily work to keep your pet happy, energized, and on a
                                growing streak.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-2xl border border-zinc-200/70 bg-white/75 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Current</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white" x-text="pet.streak"></p>
                        </div>

                        <div class="rounded-2xl border border-zinc-200/70 bg-white/75 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Longest</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white" x-text="pet.longestStreak"></p>
                        </div>

                        <div class="rounded-2xl border border-zinc-200/70 bg-white/75 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Level</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white" x-text="pet.level"></p>
                        </div>

                        <div class="rounded-2xl border border-zinc-200/70 bg-white/75 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Stars</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white" x-text="pet.stars"></p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[minmax(18rem,24rem)_minmax(0,1fr)]">
                    <div class="mx-auto flex w-full max-w-[24rem] flex-col items-center">
                        <div class="relative flex h-[24rem] w-full items-end justify-center overflow-hidden rounded-[2rem] border border-zinc-200/70 bg-[linear-gradient(180deg,rgba(255,255,255,0.95)_0%,rgba(244,244,245,0.95)_100%)] px-6 pb-6 pt-10 dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(39,39,42,0.95)_0%,rgba(24,24,27,0.98)_100%)]">
                            <div class="absolute inset-x-0 bottom-0 h-20 bg-[radial-gradient(circle_at_center,_rgba(52,211,153,0.22),_transparent_68%)] dark:bg-[radial-gradient(circle_at_center,_rgba(52,211,153,0.18),_transparent_68%)]"></div>
                            <div class="absolute right-5 top-5 rounded-full border border-white/70 bg-white/70 px-3 py-1 text-xs font-medium text-zinc-600 backdrop-blur dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                                <span x-text="pet.stage"></span>
                            </div>

                            <div
                                class="relative transition duration-300"
                                x-bind:class="petAnimation"
                            >
                                <div class="absolute left-1/2 top-8 h-8 w-28 -translate-x-1/2 rounded-full bg-black/10 blur-xl dark:bg-emerald-500/10"></div>

                                <svg viewBox="0 0 280 260" class="h-[18rem] w-[18rem] drop-shadow-[0_20px_40px_rgba(15,23,42,0.18)]">
                                    <defs>
                                        <linearGradient id="petBody" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#fbbf24"></stop>
                                            <stop offset="55%" stop-color="#fb7185"></stop>
                                            <stop offset="100%" stop-color="#60a5fa"></stop>
                                        </linearGradient>
                                        <linearGradient id="petBelly" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#fef3c7"></stop>
                                            <stop offset="100%" stop-color="#fde68a"></stop>
                                        </linearGradient>
                                    </defs>

                                    <path d="M89 76 L114 36 L136 86 Z" fill="#f59e0b"></path>
                                    <path d="M191 76 L166 36 L144 86 Z" fill="#f472b6"></path>

                                    <ellipse cx="140" cy="154" rx="88" ry="78" fill="url(#petBody)"></ellipse>
                                    <ellipse cx="140" cy="172" rx="46" ry="38" fill="url(#petBelly)"></ellipse>

                                    <circle cx="108" cy="142" r="10" fill="#18181b"></circle>
                                    <circle cx="172" cy="142" r="10" fill="#18181b"></circle>
                                    <circle cx="105" cy="138" r="3" fill="#fff"></circle>
                                    <circle cx="169" cy="138" r="3" fill="#fff"></circle>

                                    <path d="M140 155 C132 166, 148 166, 140 155" fill="#18181b"></path>
                                    <path
                                        x-show="pet.happiness >= 55"
                                        d="M118 176 Q140 194 162 176"
                                        stroke="#18181b"
                                        stroke-width="8"
                                        fill="none"
                                        stroke-linecap="round"
                                    ></path>
                                    <path
                                        x-show="pet.happiness < 55"
                                        d="M122 188 Q140 172 158 188"
                                        stroke="#18181b"
                                        stroke-width="8"
                                        fill="none"
                                        stroke-linecap="round"
                                    ></path>

                                    <circle cx="84" cy="166" r="10" fill="#fda4af" opacity="0.7"></circle>
                                    <circle cx="196" cy="166" r="10" fill="#fda4af" opacity="0.7"></circle>

                                    <ellipse cx="104" cy="222" rx="22" ry="12" fill="#f59e0b"></ellipse>
                                    <ellipse cx="176" cy="222" rx="22" ry="12" fill="#60a5fa"></ellipse>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-4 grid w-full grid-cols-2 gap-3">
                            <template x-for="stat in meters" :key="stat.key">
                                <div class="rounded-2xl border border-zinc-200/70 bg-white/80 p-4 dark:border-white/10 dark:bg-white/5">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200" x-text="stat.label"></p>
                                        <span class="text-sm font-semibold text-zinc-950 dark:text-white" x-text="pet[stat.key] + '%'"></span>
                                    </div>
                                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-zinc-200/80 dark:bg-white/10">
                                        <div
                                            class="h-full rounded-full transition-all duration-500"
                                            x-bind:class="stat.bar"
                                            x-bind:style="`width: ${pet[stat.key]}%`"
                                        ></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-[1.75rem] border border-zinc-200/70 bg-white/80 p-5 backdrop-blur dark:border-white/10 dark:bg-white/5">
                            <div class="flex flex-wrap gap-3">
                                <flux:button x-on:click="completeToday()" color="amber" variant="primary" icon="check">
                                    Complete Today
                                </flux:button>

                                <flux:button x-on:click="care('fullness', 12, 3)" variant="outline" icon="gift">
                                    Feed
                                </flux:button>

                                <flux:button x-on:click="care('happiness', 14, 4)" variant="outline" icon="sparkles">
                                    Play
                                </flux:button>

                                <flux:button x-on:click="care('cleanliness', 16, 2)" variant="ghost" icon="sun">
                                    Clean
                                </flux:button>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/80 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Daily Goal</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white" x-text="todayDone ? 'Completed' : 'Pending'"></p>
                                </div>

                                <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/80 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Next Reward</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white" x-text="nextReward"></p>
                                </div>

                                <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/80 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">Pet Mood</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white" x-text="petMood"></p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.75rem] border border-zinc-200/70 bg-white/80 p-5 backdrop-blur dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-base font-semibold text-zinc-950 dark:text-white">Weekly Consistency</p>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">A quick look at how your streak has been moving this week.</p>
                                </div>
                                <div class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-medium text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                                    7 days
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-7 gap-3">
                                <template x-for="day in week" :key="day.label">
                                    <div class="space-y-2 text-center">
                                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-zinc-400 dark:text-zinc-500" x-text="day.label"></p>
                                        <div
                                            class="flex h-16 items-center justify-center rounded-2xl border text-sm font-semibold transition"
                                            x-bind:class="day.done
                                                ? 'border-emerald-300 bg-emerald-100 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200'
                                                : 'border-zinc-200 bg-zinc-50 text-zinc-400 dark:border-white/10 dark:bg-white/5 dark:text-zinc-500'"
                                        >
                                            <span x-text="day.done ? 'Done' : 'Rest'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="grid gap-5">
            <flux:card class="rounded-[1.75rem] border-zinc-200/70 bg-white/90 p-0 dark:border-white/10 dark:bg-zinc-900/90">
                <div class="border-b border-zinc-200/70 px-5 py-4 dark:border-white/10">
                    <flux:heading size="lg">Rewards Path</flux:heading>
                    <flux:subheading size="sm">Little milestones to keep the streak feeling alive.</flux:subheading>
                </div>

                <div class="space-y-3 p-5">
                    <template x-for="reward in rewards" :key="reward.days">
                        <div
                            class="rounded-2xl border p-4 transition"
                            x-bind:class="pet.streak >= reward.days
                                ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-400/20 dark:bg-emerald-400/10'
                                : 'border-zinc-200/70 bg-zinc-50/80 dark:border-white/10 dark:bg-white/5'"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-950 dark:text-white" x-text="reward.title"></p>
                                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300" x-text="reward.description"></p>
                                </div>
                                <div
                                    class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em]"
                                    x-bind:class="pet.streak >= reward.days
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-200'
                                        : 'bg-zinc-200 text-zinc-600 dark:bg-white/10 dark:text-zinc-300'"
                                    x-text="reward.days + 'd'"
                                ></div>
                            </div>
                        </div>
                    </template>
                </div>
            </flux:card>

            <flux:card class="rounded-[1.75rem] border-zinc-200/70 bg-white/90 p-0 dark:border-white/10 dark:bg-zinc-900/90">
                <div class="border-b border-zinc-200/70 px-5 py-4 dark:border-white/10">
                    <flux:heading size="lg">Care Log</flux:heading>
                    <flux:subheading size="sm">Recent interactions with your streak pet.</flux:subheading>
                </div>

                <div class="space-y-3 p-5">
                    <template x-for="entry in log" :key="entry.id">
                        <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-zinc-950 dark:text-white" x-text="entry.title"></p>
                                <span class="text-xs uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500" x-text="entry.time"></span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300" x-text="entry.description"></p>
                        </div>
                    </template>
                </div>
            </flux:card>
        </aside>
    </div>

    <script>
        function petStreakApp() {
            return {
                pet: {
                    name: 'Pixel',
                    streak: 12,
                    longestStreak: 19,
                    level: 4,
                    stars: 128,
                    happiness: 82,
                    fullness: 74,
                    cleanliness: 88,
                    energy: 79,
                    stage: 'Glow Fox'
                },
                todayDone: false,
                week: [
                    { label: 'Mon', done: true },
                    { label: 'Tue', done: true },
                    { label: 'Wed', done: false },
                    { label: 'Thu', done: true },
                    { label: 'Fri', done: true },
                    { label: 'Sat', done: true },
                    { label: 'Sun', done: false }
                ],
                rewards: [
                    {
                        days: 7,
                        title: 'Mini Habitat Unlock',
                        description: 'A cozy corner appears for your pet after one strong week of consistency.'
                    },
                    {
                        days: 14,
                        title: 'Shiny Collar',
                        description: 'Your streak pet earns a bright new accessory after two steady weeks.'
                    },
                    {
                        days: 21,
                        title: 'Mood Aura',
                        description: 'A colorful aura unlocks when your focus rhythm becomes a real habit.'
                    },
                    {
                        days: 30,
                        title: 'Evolution Stage',
                        description: 'Your pet evolves into a rarer form at the 30-day streak mark.'
                    }
                ],
                log: [
                    {
                        id: 1,
                        title: 'Daily streak completed',
                        description: 'You finished your planned study block and kept the streak alive.',
                        time: 'Today'
                    },
                    {
                        id: 2,
                        title: 'Played with Pixel',
                        description: 'A small boost to happiness made your pet more cheerful.',
                        time: '2h ago'
                    },
                    {
                        id: 3,
                        title: 'Fed your pet',
                        description: 'Fullness went up and Pixel gained a few extra stars.',
                        time: 'Yesterday'
                    }
                ],
                meters: [
                    { key: 'happiness', label: 'Happiness', bar: 'bg-pink-400' },
                    { key: 'fullness', label: 'Fullness', bar: 'bg-amber-400' },
                    { key: 'cleanliness', label: 'Cleanliness', bar: 'bg-sky-400' },
                    { key: 'energy', label: 'Energy', bar: 'bg-emerald-400' }
                ],

                init() {
                    this.loadState();
                    this.passiveDecay();
                },

                get petMood() {
                    const total = this.pet.happiness + this.pet.fullness + this.pet.cleanliness + this.pet.energy;
                    const average = total / 4;

                    if (average >= 85) return 'thriving';
                    if (average >= 70) return 'happy';
                    if (average >= 55) return 'calm';
                    if (average >= 40) return 'sleepy';
                    return 'needy';
                },

                get petAnimation() {
                    if (this.petMood === 'thriving') return 'translate-y-0 scale-[1.03]';
                    if (this.petMood === 'happy') return '-translate-y-1';
                    if (this.petMood === 'sleepy') return 'translate-y-2 scale-[0.98] opacity-95';
                    return '';
                },

                get nextReward() {
                    const upcoming = this.rewards.find((reward) => reward.days > this.pet.streak);
                    return upcoming ? `${upcoming.days} days` : 'All unlocked';
                },

                completeToday() {
                    if (this.todayDone) {
                        this.toast('Already completed', 'Today is already counted for your streak.');
                        return;
                    }

                    this.todayDone = true;
                    this.pet.streak += 1;
                    this.pet.longestStreak = Math.max(this.pet.longestStreak, this.pet.streak);
                    this.pet.stars += 18;
                    this.pet.energy = this.bump(this.pet.energy, 10);
                    this.pet.happiness = this.bump(this.pet.happiness, 12);

                    if (this.pet.streak % 5 === 0) {
                        this.pet.level += 1;
                        this.pet.stage = this.pet.level >= 6 ? 'Nova Spirit' : (this.pet.level >= 4 ? 'Glow Fox' : 'Bright Cub');
                    }

                    this.week.shift();
                    this.week.push({ label: this.dayLabel(new Date()), done: true });

                    this.addLog('Daily streak completed', 'You locked in another productive day and Pixel looks more energized.');
                    this.toast('Streak extended', `${this.pet.name} is proud of your ${this.pet.streak}-day streak.`);
                    this.persistState();
                },

                care(stat, amount, stars) {
                    this.pet[stat] = this.bump(this.pet[stat], amount);
                    this.pet.stars += stars;

                    const labels = {
                        happiness: 'Played with your pet',
                        fullness: 'Fed your pet',
                        cleanliness: 'Cleaned your pet'
                    };

                    this.addLog(labels[stat], `${this.pet.name} feels better and gains a little extra shine.`);
                    this.persistState();
                },

                bump(value, amount) {
                    return Math.min(100, value + amount);
                },

                passiveDecay() {
                    setInterval(() => {
                        this.pet.happiness = Math.max(28, this.pet.happiness - 1);
                        this.pet.fullness = Math.max(22, this.pet.fullness - 1);
                        this.pet.cleanliness = Math.max(26, this.pet.cleanliness - 1);
                        this.pet.energy = Math.max(24, this.pet.energy - 1);
                        this.persistState();
                    }, 25000);
                },

                addLog(title, description) {
                    this.log.unshift({
                        id: Date.now(),
                        title,
                        description,
                        time: 'Just now'
                    });

                    this.log = this.log.slice(0, 5);
                },

                dayLabel(date) {
                    return date.toLocaleDateString('en-US', { weekday: 'short' });
                },

                toast(heading, text) {
                    if (window.$flux?.toast) {
                        window.$flux.toast({ heading, text });
                    }
                },

                persistState() {
                    localStorage.setItem('student-pet-streak-state', JSON.stringify({
                        pet: this.pet,
                        todayDone: this.todayDone,
                        week: this.week,
                        log: this.log
                    }));
                },

                loadState() {
                    const stored = localStorage.getItem('student-pet-streak-state');

                    if (!stored) {
                        return;
                    }

                    try {
                        const parsed = JSON.parse(stored);
                        this.pet = parsed.pet ?? this.pet;
                        this.todayDone = parsed.todayDone ?? this.todayDone;
                        this.week = parsed.week ?? this.week;
                        this.log = parsed.log ?? this.log;
                    } catch (error) {
                        localStorage.removeItem('student-pet-streak-state');
                    }
                }
            };
        }
    </script>
</div>
