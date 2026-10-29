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
        class="text-5xl lg:text-5xl font-extrabold tracking-tight leading-[1.15] max-w-4xl mx-auto mb-2 text-gray-900 dark:text-white">
        <span class="anim-word text-7xl mb-3">Duty Track : </span>&#32;<span class="anim-word">A web-based OJT Tracking and</span>&#32;<span
            class="anim-word">Duty Hours,</span>
        <br>
        <span class="anim-word">Monitoring System,</span>&#32;<span class="anim-word">and</span>&#32;<span
            class="anim-word relative inline-block">
            Productivity
            <svg class="absolute -bottom-2 left-0 w-full overflow-visible" height="10" viewBox="0 0 200 10"
                preserveAspectRatio="none">
                <path class="anim-underline" d="M0 6 Q50 1 100 6 Q150 11 200 6" fill="none" stroke="#dc2626"
                    stroke-width="3" stroke-linecap="round" />
            </svg>
        </span>
    </h1>

    <p class="anim-sub text-lg text-gray-500 dark:text-zinc-400 max-w-2xl mx-auto mb-9 mt-6 leading-relaxed">
        A centralized dashboard that allows academic staffs to monitor attendance,
        track duty hours, manage tasks, and evaluate
        internship performance.
    </p>

    <div class="anim-cta flex flex-col items-center gap-3 mb-16">
        <a href="{{ route('login') }}"
            class="bg-red-600 hover:bg-red-700 text-white px-8 py-3.5 rounded-xl text-base font-semibold transition-all shadow-lg shadow-red-200 dark:shadow-red-900/50 hover:shadow-red-300 hover:-translate-y-0.5 no-underline">
            Start Using DutyTrack
        </a>
    </div>
</section>
