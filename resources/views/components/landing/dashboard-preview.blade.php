<section class="anim-dashboard max-w-5xl mx-auto pb-20 px-6">
    <div
        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-[0_0_45px_rgba(239,68,68,0.5)] border border-gray-200 dark:border-zinc-700/60 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-2 font-bold text-sm text-gray-900 dark:text-white">
                <div class="w-6 h-6 bg-red-600 rounded-md flex items-center justify-center">
                    <flux:icon.clock class="size-3.5 text-white" />
                </div>
                Duty Track
            </div>
            <div
                class="flex items-center gap-2 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg px-3.5 py-1.5 text-xs text-gray-400 dark:text-zinc-500 w-48">
                <svg width="12" height="12" viewBox="0 0 13 13" fill="none">
                    <circle cx="5.5" cy="5.5" r="4" stroke="currentColor" stroke-width="1.3" />
                    <path d="M9 9l2.5 2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                </svg>
                Search
            </div>
        </div>

        <div class="flex min-h-[420px]">
            <div
                class="w-44 flex-shrink-0 border-r border-gray-100 dark:border-zinc-800 py-4 bg-white dark:bg-zinc-900">
                @php
                    $navItems = [
                        [
                            'label' => 'Dashboard',
                            'active' => true,
                            'icon' =>
                                '<rect x="1" y="1" width="5" height="5" rx="1"/><rect x="9" y="1" width="5" height="5" rx="1"/><rect x="1" y="9" width="5" height="5" rx="1"/><rect x="9" y="9" width="5" height="5" rx="1"/>',
                        ],
                        [
                            'label' => 'Students',
                            'active' => false,
                            'icon' => '<circle cx="7.5" cy="5" r="3"/><path d="M1 14c0-3 3-5 6.5-5s6.5 2 6.5 5"/>',
                        ],
                        [
                            'label' => 'Attendance',
                            'active' => false,
                            'icon' => '<path d="M2 4h11M2 7.5h11M2 11h11"/>',
                        ],
                        [
                            'label' => 'Duty Hours',
                            'active' => false,
                            'icon' => '<circle cx="7.5" cy="7.5" r="6"/><path d="M7.5 3v4.5l2.5 2"/>',
                        ],
                        [
                            'label' => 'Tasks',
                            'active' => false,
                            'icon' => '<rect x="1" y="1" width="13" height="10" rx="2"/><path d="M4 14l3-3"/>',
                        ],
                        ['label' => 'Reports', 'active' => false, 'icon' => '<path d="M2 11l4-4 3 3 4-6"/>'],
                        [
                            'label' => 'Export',
                            'active' => false,
                            'icon' => '<path d="M7.5 2v8m0 0l-3-3m3 3l3-3"/><path d="M2 13h11"/>',
                        ],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    <div
                        class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-medium cursor-pointer transition-colors
                            {{ $item['active']
                                ? 'text-gray-900 dark:text-white bg-red-50 dark:bg-red-950/40'
                                : 'text-gray-400 dark:text-zinc-500 hover:text-gray-700 dark:hover:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 15 15" fill="none" stroke="currentColor"
                            stroke-width="1.4">
                            {!! $item['icon'] !!}
                        </svg>
                        {{ $item['label'] }}
                    </div>
                @endforeach
            </div>

            <div class="flex-1 p-5 bg-gray-50 dark:bg-zinc-950">
                <div class="grid grid-cols-3 gap-3.5 mb-3.5">
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-gray-100 dark:border-zinc-800">
                        <div class="text-xs text-gray-400 dark:text-zinc-500 font-medium mb-2">Total Duty Hours
                        </div>
                        <div class="flex items-center gap-2 text-3xl font-bold text-gray-900 dark:text-white mb-1">
                            486
                            <span class="flex items-center text-emerald-500 text-sm font-semibold">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path d="M2 9l4-6 4 6" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 dark:text-zinc-500 mb-3">hrs logged this semester</div>
                        <a href="#" class="text-xs text-red-600 dark:text-red-400 font-medium no-underline">View
                            logs →</a>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-gray-100 dark:border-zinc-800">
                        <div class="text-xs text-gray-400 dark:text-zinc-500 font-medium mb-2">Absences</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">3</div>
                        <div class="text-xs text-gray-400 dark:text-zinc-500 mb-3">days missed out of 120</div>
                        <a href="#" class="text-xs text-red-600 dark:text-red-400 font-medium no-underline">All
                            records
                            →</a>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-gray-100 dark:border-zinc-800">
                        <div class="text-xs text-gray-400 dark:text-zinc-500 font-medium mb-2">OJT Completion</div>
                        <div class="flex flex-col items-center my-1">
                            <svg width="90" height="55" viewBox="0 0 90 55" class="overflow-visible">
                                <path d="M 10 50 A 35 35 0 0 1 80 50" fill="none" stroke="#3F3F46" stroke-width="7"
                                    stroke-linecap="round" class="dark:block hidden" />
                                <path d="M 10 50 A 35 35 0 0 1 80 50" fill="none" stroke="#E5E7EB" stroke-width="7"
                                    stroke-linecap="round" class="dark:hidden" />
                                <path d="M 10 50 A 35 35 0 0 1 80 50" fill="none" stroke="#DC2626" stroke-width="7"
                                    stroke-linecap="round" stroke-dasharray="110" stroke-dashoffset="16" />
                            </svg>
                            <div class="-mt-1 text-2xl font-bold text-gray-900 dark:text-white">84%</div>
                        </div>
                        <a href="#"
                            class="text-xs text-red-600 dark:text-red-400 font-medium no-underline mt-2 block">All
                            goals →</a>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3.5">
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                        <div class="flex items-center justify-between px-4 pt-3.5">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Students</span>
                            <span class="text-xs text-gray-400 dark:text-zinc-500">Sort by Newest ▾</span>
                        </div>
                        <div class="py-2">
                            <div
                                class="flex items-center gap-2.5 px-4 py-2 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                                <div
                                    class="w-7 h-7 rounded-full bg-orange-400 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    CF</div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-zinc-100">Chris
                                        Friedkly</div>
                                    <div class="text-xs text-gray-400 dark:text-zinc-500">Supermarket Villanova
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 px-4 py-2 bg-red-50 dark:bg-red-950/30">
                                <div
                                    class="w-7 h-7 rounded-full bg-violet-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    MJ</div>
                                <div class="flex-1">
                                    <div class="text-xs font-semibold text-gray-900 dark:text-zinc-100">Maggie
                                        Johnson</div>
                                    <div class="text-xs text-gray-400 dark:text-zinc-500">Oasis Organic Inc.</div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div
                                        class="w-6 h-6 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-md flex items-center justify-center cursor-pointer">
                                        <svg width="10" height="10" viewBox="0 0 11 11" fill="none"
                                            stroke="#9CA3AF" stroke-width="1.3">
                                            <path d="M1 10l3-1 6-6-2-2-6 6-1 3z" />
                                        </svg>
                                    </div>
                                    <div
                                        class="w-6 h-6 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-md flex items-center justify-center cursor-pointer">
                                        <svg width="10" height="10" viewBox="0 0 11 11" fill="none"
                                            stroke="#9CA3AF" stroke-width="1.3">
                                            <path
                                                d="M5.5 1l1.2 2.4 2.6.38-1.9 1.84.45 2.6L5.5 7.1l-2.35 1.22.45-2.6L1.7 3.78l2.6-.38L5.5 1z" />
                                        </svg>
                                    </div>
                                    <div
                                        class="w-6 h-6 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-md flex items-center justify-center cursor-pointer">
                                        <svg width="10" height="10" viewBox="0 0 11 11" fill="none">
                                            <circle cx="2" cy="5.5" r=".9" fill="#9CA3AF" />
                                            <circle cx="5.5" cy="5.5" r=".9" fill="#9CA3AF" />
                                            <circle cx="9" cy="5.5" r=".9" fill="#9CA3AF" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-2.5 px-4 py-2 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                                <div
                                    class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    GH</div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-900 dark:text-zinc-100">Gael Harry
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-zinc-500">Tech Ventures LLC</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-100 dark:border-zinc-800 overflow-hidden">
                        <div class="px-4 pt-3.5 pb-3">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Attendance</span>
                                <span class="text-xs text-gray-400 dark:text-zinc-500">Yearly ▾</span>
                            </div>
                            <div class="h-28">
                                <svg viewBox="0 0 300 110" class="w-full h-full" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="growthGradLight" x1="0" y1="0"
                                            x2="0" y2="1">
                                            <stop offset="0%" stop-color="#DC2626" stop-opacity="0.18" />
                                            <stop offset="100%" stop-color="#DC2626" stop-opacity="0" />
                                        </linearGradient>
                                        <linearGradient id="growthGradDark" x1="0" y1="0"
                                            x2="0" y2="1">
                                            <stop offset="0%" stop-color="#DC2626" stop-opacity="0.25" />
                                            <stop offset="100%" stop-color="#DC2626" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <text x="0" y="12" font-size="8" fill="#9CA3AF">100%</text>
                                    <text x="0" y="37" font-size="8" fill="#9CA3AF">75%</text>
                                    <text x="0" y="62" font-size="8" fill="#9CA3AF">50%</text>
                                    <text x="0" y="87" font-size="8" fill="#9CA3AF">25%</text>
                                    <line x1="28" y1="8" x2="300" y2="8"
                                        stroke="#F3F4F6" stroke-width="1" class="dark:stroke-zinc-800" />
                                    <line x1="28" y1="33" x2="300" y2="33"
                                        stroke="#F3F4F6" stroke-width="1" class="dark:stroke-zinc-800" />
                                    <line x1="28" y1="58" x2="300" y2="58"
                                        stroke="#F3F4F6" stroke-width="1" class="dark:stroke-zinc-800" />
                                    <line x1="28" y1="83" x2="300" y2="83"
                                        stroke="#F3F4F6" stroke-width="1" class="dark:stroke-zinc-800" />
                                    <path
                                        d="M 30 90 C 60 88 85 80 115 70 C 145 60 165 55 190 45 C 215 35 240 20 275 10 L 275 100 L 30 100 Z"
                                        fill="url(#growthGradLight)" />
                                    <path d="M 30 90 C 60 88 85 80 115 70 C 145 60 165 55 190 45 C 215 35 240 20 275 10"
                                        fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="flex justify-between pt-1">
                                @foreach (['2016', '2017', '2018', '2019', '2020', '2021', '2022', '2023'] as $yr)
                                    <span class="text-gray-400 dark:text-zinc-600"
                                        style="font-size:0.6rem;">{{ $yr }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
