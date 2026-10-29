<section class="relative text-center pt-16 pb-0 px-6 mt-8" id="home">
    <div class="absolute inset-x-0 top-16 pointer-events-none hidden lg:block">
        <div class="anim-icon anim-from-left-1 absolute left-[9%] top-8 w-20 h-20 bg-white dark:bg-zinc-800 rounded-2xl shadow-lg dark:shadow-zinc-900 flex items-center justify-center"
            style="--rot:-12deg;">
            <div class="bg-gray-100 dark:bg-zinc-700 w-16 h-16 rounded-md flex items-center justify-center">
                <flux:icon.user class="size-10 text-red-600 dark:text-red-400" />
            </div>
        </div>

        <div class="anim-icon anim-from-left-2 absolute left-[12%] top-70 w-20 h-20 bg-white dark:bg-zinc-800 rounded-2xl shadow-lg dark:shadow-zinc-900 flex items-center justify-center"
            style="--rot:-12deg;">
            <div class="bg-gray-100 dark:bg-zinc-700 w-16 h-16 rounded-md flex items-center justify-center">
                <flux:icon.computer-desktop class="size-10 text-red-600 dark:text-red-400" />
            </div>
        </div>

        <div class="anim-icon anim-from-right-1 absolute right-[9%] top-6 w-20 h-20 bg-white dark:bg-zinc-800 rounded-2xl shadow-lg dark:shadow-zinc-900 flex items-center justify-center"
            style="--rot:12deg;">
            <div class="bg-gray-100 dark:bg-zinc-700 w-16 h-16 rounded-md flex items-center justify-center">
                <flux:icon.calendar-date-range class="size-10 text-red-600 dark:text-red-400" />
            </div>
        </div>

        <div class="anim-icon anim-from-right-2 absolute right-[12%] top-70 w-20 h-20 bg-white dark:bg-zinc-800 rounded-2xl shadow-lg dark:shadow-zinc-900 flex items-center justify-center"
            style="--rot:12deg;">
            <div class="bg-gray-100 dark:bg-zinc-700 w-16 h-16 rounded-md flex items-center justify-center">
                <flux:icon.clock class="size-10 text-red-600 dark:text-red-400" />
            </div>
        </div>
    </div>

    <h1
        class="text-lg sm:text-xl lg:text-2xl font-semibold leading-snug max-w-2xl mx-auto mb-2 text-gray-700 dark:text-zinc-300 text-balance">
        <span class="anim-word !block text-5xl sm:text-7xl lg:text-8xl font-extrabold tracking-tight leading-none mb-4 sm:mb-5 text-gray-900 dark:text-white">Duty Track</span>
        <span class="anim-word">A web-based OJT Tracking and</span>&#32;<span
            class="anim-word">Duty Hours,</span>&#32;<span class="anim-word">Monitoring System,</span>&#32;<span
            class="anim-word">and</span>&#32;<span
            class="anim-word relative inline-block">
            Productivity
            <svg class="absolute -bottom-2 left-0 w-full overflow-visible" height="10" viewBox="0 0 200 10"
                preserveAspectRatio="none">
                <path class="anim-underline" d="M0 6 Q50 1 100 6 Q150 11 200 6" fill="none" stroke="#dc2626"
                    stroke-width="3" stroke-linecap="round" />
            </svg>
        </span>
    </h1>

    <p class="anim-sub text-base sm:text-lg text-balance text-gray-500 dark:text-zinc-400 max-w-2xl mx-auto mb-9 mt-6 leading-relaxed">
        A centralized dashboard that allows academic staffs to monitor attendance,
        track duty hours, manage tasks, and evaluate
        internship performance.
    </p>

    <div class="anim-cta flex flex-col sm:flex-row items-center justify-center gap-3 mb-16">
        <a href="{{ route('register') }}"
            class="w-full sm:w-auto text-center bg-red-600 hover:bg-red-700 text-white px-8 py-3.5 rounded-xl text-base font-semibold transition-all shadow-lg shadow-red-200 dark:shadow-red-900/50 hover:shadow-red-300 hover:-translate-y-0.5 no-underline">
            Sign up as a Student
        </a>
        <a href="{{ route('login') }}"
            class="w-full sm:w-auto text-center border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:bg-gray-50 dark:hover:bg-zinc-800 text-gray-900 dark:text-white px-8 py-3.5 rounded-xl text-base font-semibold transition-all hover:-translate-y-0.5 no-underline">
            Log in
        </a>
    </div>
</section>
