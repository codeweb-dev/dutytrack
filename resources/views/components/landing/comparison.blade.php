<section class="py-24 px-6 bg-canvas dark:bg-zinc-950" id="comparison">
    <div class="max-w-6xl mx-auto">
        <div
            class="text-center mb-14
            opacity-0 translate-y-6
            intersect:opacity-100 intersect:translate-y-0
            transition-all duration-500 ease-out intersect-once">
            <div class="inline-block mb-5">
                <flux:badge rounded icon="sparkles" color="green">Why DutyTrack</flux:badge>
            </div>
            <h2
                class="text-4xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight mb-4">
                A Smarter Way To<br>Manage OJT
            </h2>
            <p class="text-lg text-gray-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
                Turn scattered attendance sheets and manual logs into clear,
                organized records that work for everyone.
            </p>
        </div>

        <div
            class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch
            opacity-0 translate-y-8
            intersect:opacity-100 intersect:translate-y-0
            transition-all duration-600 delay-150 ease-out intersect-once">
            <div class="bg-surface dark:bg-zinc-900 rounded-2xl p-8 border border-gray-200 dark:border-zinc-800">
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-7">Other Tools</h3>
                <ul class="space-y-4">
                    @foreach (['Attendance tracked on paper or spreadsheets', 'Tasks tracked manually', 'Reports take hours to compile', 'Hard to see who is falling behind'] as $item)
                        <li class="flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-full border-2 border-gray-300 dark:border-zinc-600 flex items-center justify-center flex-shrink-0">
                                <svg class="size-3 text-gray-400 dark:text-zinc-500" viewBox="0 0 12 12" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <path d="M2 2l8 8M10 2l-8 8" />
                                </svg>
                            </span>
                            <span class="text-md text-gray-500 dark:text-zinc-400">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div
                class="bg-surface dark:bg-zinc-900 rounded-2xl p-8 border-3 border-forest shadow-sm shadow-sage dark:shadow-zinc-900/30">
                <div class="flex items-center gap-3 mb-7">
                    <div class="flex items-center gap-2 font-bold text-lg text-gray-900 dark:text-white">
                        <img src="{{ asset('logo.png') }}" alt="Duty Track Logo" width="32" height="32" />
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white">DutyTrack</h3>
                    </div>
                </div>
                <ul class="space-y-4">
                    @foreach (['All your OJT data in one platform', 'Tasks tracked in one place', 'Reports generated instantly', 'Clear view of every student\'s progress'] as $item)
                        <li class="flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-full bg-forest flex items-center justify-center flex-shrink-0">
                                <svg class="size-3 text-white" viewBox="0 0 12 12" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 6l3 3 5-5" />
                                </svg>
                            </span>
                            <span
                                class="text-md font-medium text-gray-900 dark:text-zinc-100">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
