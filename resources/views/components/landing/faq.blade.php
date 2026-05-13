<section class="py-24 px-6 bg-gray-100 dark:bg-zinc-950" id="faq">
    <div class="max-w-3xl mx-auto">
        <div
            class="text-center mb-14
            opacity-0 translate-y-6
            intersect:opacity-100 intersect:translate-y-0
            transition-all duration-500 ease-out intersect-once">
            <div class="inline-block mb-5">
                <flux:badge rounded icon="sparkles" color="red">FAQ's</flux:badge>
            </div>
            <h2
                class="text-4xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight mb-4">
                Common Questions<br>With Clear Answers
            </h2>
            <p class="text-lg text-gray-500 dark:text-zinc-400 leading-relaxed">
                Here are answers to the most common<br>
                things people ask before getting started.
            </p>
        </div>

        <div class="space-y-3
            opacity-0 translate-y-6
            intersect:opacity-100 intersect:translate-y-0
            transition-all duration-600 delay-150 ease-out intersect-once"
            x-data="{ open: 0 }">

            @php
                $faqs = [
                    [
                        'q' => 'How does DutyTrack record student attendance?',
                        'a' =>
                            'Students time in and out through the platform. DutyTrack automatically logs timestamps, calculates hours, and flags any discrepancies — no manual entry needed from supervisors.',
                    ],
                    [
                        'q' => 'Do academic staffs need to install anything?',
                        'a' =>
                            'No. DutyTrack is fully web-based. Academic staffs can access everything from any browser without installing any software.',
                    ],
                    [
                        'q' => 'How are OJT duty hours calculated?',
                        'a' =>
                            'DutyTrack automatically tallies each student\'s time-in and time-out entries. Completed and remaining hours are updated in real time on the dashboard.',
                    ],
                    [
                        'q' => 'Can multiple supervisors monitor the same student?',
                        'a' =>
                            'Yes. Multiple supervisors or coordinators can be assigned to a student and each will have their own view into that student\'s attendance, hours, and task progress.',
                    ],
                    [
                        'q' => 'Is student data kept secure?',
                        'a' =>
                            'Absolutely. All data is stored securely with role-based access controls, ensuring only authorized administrators, supervisors, and the students themselves can view relevant records.',
                    ],
                ];
            @endphp

            @foreach ($faqs as $i => $faq)
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 overflow-hidden"
                    x-data="{
                        get isOpen() { return open === {{ $i }} },
                        toggle() { open = open === {{ $i }} ? null : {{ $i }} }
                    }">
                    <button class="w-full flex items-center justify-between px-6 py-5 text-left cursor-pointer"
                        x-on:click="toggle()">
                        <span
                            class="text-lg font-semibold text-gray-900 dark:text-white pr-4">{{ $faq['q'] }}</span>
                        <span
                            class="shrink-0 w-7 h-7 rounded-full border flex items-center justify-center transition-all duration-300"
                            :class="isOpen ? 'bg-red-600 border-red-600' :
                                'bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-700'">
                            <svg class="size-3.5 transition-transform duration-300"
                                :class="isOpen ? 'text-white rotate-45' : 'text-gray-400 dark:text-zinc-500'"
                                viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M7 1v12M1 7h12" />
                            </svg>
                        </span>
                    </button>

                    <div x-show="isOpen"
                        x-transition:enter="transition-all ease-[cubic-bezier(0.4,0,0.2,1)] duration-400"
                        x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96"
                        x-transition:leave="transition-all ease-[cubic-bezier(0.4,0,0.2,1)] duration-300"
                        x-transition:leave-start="opacity-100 max-h-96" x-transition:leave-end="opacity-0 max-h-0"
                        style="overflow: hidden;" class="border-t border-gray-100 dark:border-zinc-800">
                        <p class="px-6 pt-4 pb-5 text-md text-gray-500 dark:text-zinc-400 leading-relaxed">
                            {{ $faq['a'] }}</p>
                    </div>

                </div>
            @endforeach

        </div>
    </div>
</section>
