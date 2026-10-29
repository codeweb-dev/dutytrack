<section class="py-24 px-6 bg-gray-100 dark:bg-zinc-950" id="benefits">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
            <div
                class="inline-block relative mb-7
                opacity-0 translate-y-6
                intersect:opacity-100 intersect:translate-y-0
                transition-all duration-500 ease-out
                intersect-once">
                <flux:badge rounded icon="sparkles" color="red">Benefits</flux:badge>
            </div>
            <h2
                class="text-4xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight mb-4
                opacity-0 translate-y-6
                intersect:opacity-100 intersect:translate-y-0
                transition-all duration-600 delay-100 ease-out
                intersect-once">
                Benefits That Improve<br>OJT Management
            </h2>
            <p
                class="text-lg text-gray-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed
                opacity-0 translate-y-6
                intersect:opacity-100 intersect:translate-y-0
                transition-all duration-600 delay-200 ease-out
                intersect-once">
                DutyTrack helps schools and companies monitor student training,
                manage tasks, and evaluate productivity in one centralized system.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
                $benefits = [
                    [
                        'title' => 'Secure Attendance Recording',
                        'desc' =>
                            'DutyTrack records student attendance with location verification to support accurate time-in and time-out entries without collecting attendance selfies.',
                        'icon' =>
                            '<rect x="5" y="11" width="14" height="11" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
                        'delay' => '0',
                    ],
                    [
                        'title' => 'Student Requirement Tracking',
                        'desc' => 'Keep track of submitted requirements and coordinator verification.',
                        'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                        'delay' => '75',
                    ],
                    [
                        'title' => 'Task and Productivity Monitoring',
                        'desc' =>
                            'Monitor completed tasks and measure student productivity during their internship period.',
                        'icon' =>
                            '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
                        'delay' => '150',
                    ],
                    [
                        'title' => 'Evaluation System',
                        'desc' =>
                            'Academic staffs can rate student performance and provide feedback to improve training outcomes.',
                        'icon' =>
                            '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                        'delay' => '0',
                    ],
                    [
                        'title' => 'Automated OJT Reports',
                        'desc' => 'Generate attendance summaries and performance evaluations automatically.',
                        'icon' =>
                            '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
                        'delay' => '75',
                    ],
                    [
                        'title' => 'Centralized Monitoring Dashboard',
                        'desc' =>
                            'Coordinators and supervisors can monitor student progress and internship performance in one dashboard.',
                        'icon' =>
                            '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
                        'delay' => '150',
                    ],
                ];
            @endphp

            @foreach ($benefits as $benefit)
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-gray-100 dark:border-zinc-800 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-200
                    opacity-0 translate-y-8
                    intersect:opacity-100 intersect:translate-y-0
                    intersect-once"
                    style="transition-duration: 500ms; transition-delay: {{ $benefit['delay'] }}ms;">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center">
                            <svg class="size-6 text-red-500 dark:text-red-400" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                {!! $benefit['icon'] !!}
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $benefit['title'] }}</h3>
                    </div>
                    <p class="text-md text-gray-500 dark:text-zinc-400 leading-relaxed">{{ $benefit['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
