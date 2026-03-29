<footer class="bg-white dark:bg-zinc-900 border-t border-gray-100 dark:border-zinc-800">
    <div class="max-w-5xl mx-auto px-6 py-14">
        <div class="flex flex-col lg:flex-row gap-12 lg:gap-0 justify-between">
            <div
                class="max-w-xs
                opacity-0 -translate-x-6
                intersect:opacity-100 intersect:translate-x-0
                transition-all duration-600 ease-out
                intersect-once">
                <div class="flex items-center gap-2 font-bold text-lg text-gray-900 dark:text-white mb-4">
                    <img src="{{ asset('logo.png') }}" alt="Duty Track Logo" width="32" height="32" />
                    <div>Duty Track</div>
                </div>
                <p class="text-sm text-gray-500 dark:text-zinc-400 leading-relaxed mb-6">
                    A centralized platform for administrators, supervisors, and students to monitor OJT attendance, duty
                    hours, and internship performance.
                </p>
                <a href="{{ route('login') }}"
                    class="inline-block bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors no-underline">
                    Get Started
                </a>
            </div>

            <div class="flex flex-wrap gap-12">
                <div
                    class="opacity-0 translate-y-6
                    intersect:opacity-100 intersect:translate-y-0
                    transition-all duration-500 delay-100 ease-out
                    intersect-once">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Sections</h4>
                    <ul class="space-y-2.5">
                        @foreach (['Home', 'Benefits', 'Comparison', 'FAQ', 'Teams'] as $item)
                            <li>
                                <a href="#{{ strtolower($item) }}"
                                    class="text-sm text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white transition-colors no-underline">
                                    {{ $item }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div
                    class="opacity-0 translate-y-6
                    intersect:opacity-100 intersect:translate-y-0
                    transition-all duration-500 delay-200 ease-out
                    intersect-once">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Socials</h4>
                    <ul class="space-y-2.5">
                        @foreach (['Facebook', 'Twitter/X', 'LinkedIn', 'Instagram'] as $item)
                            <li>
                                <a href="#"
                                    class="text-sm text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white transition-colors no-underline">
                                    {{ $item }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div
                    class="opacity-0 translate-y-6
                    intersect:opacity-100 intersect:translate-y-0
                    transition-all duration-500 delay-300 ease-out
                    intersect-once">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Pages</h4>
                    <ul class="space-y-2.5">
                        @foreach (['Home', 'Dashboard', 'Students', 'Tasks', '404'] as $item)
                            <li>
                                <a href="#"
                                    class="text-sm text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white transition-colors no-underline">
                                    {{ $item }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <div class="border-t border-gray-100 dark:border-zinc-800">
        <div
            class="max-w-5xl mx-auto px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2
            opacity-0 translate-y-4
            intersect:opacity-100 intersect:translate-y-0
            transition-all duration-500 delay-100 ease-out
            intersect-once">
            <span class="text-xs flex items-center text-gray-400 dark:text-zinc-500">
                Built by Allen Labrague with
                <flux:icon.heart variant="micro" class="ml-1" />
            </span>
            <span class="text-xs text-gray-400 dark:text-zinc-500">
                &copy; {{ date('Y') }} DutyTrack. All rights reserved.
            </span>
        </div>
    </div>
</footer>
