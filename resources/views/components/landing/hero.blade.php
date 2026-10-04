<section class="px-5 pb-12 pt-16 sm:pt-24 lg:px-8" id="home">
    <div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
        <div>
            <span class="inline-flex rounded-full bg-sage px-4 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-zinc-900">College practicum, clearly managed</span>
            <h1 class="mt-7 max-w-4xl text-5xl font-semibold leading-[1.1] tracking-tight text-zinc-950 dark:text-white sm:text-6xl xl:text-7xl">Every OJT milestone, <span class="text-forest dark:text-sage">in view.</span></h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-600 dark:text-zinc-300">Track placement, attendance, approved duty hours, requirements, reports, and evaluations in one shared workspace for students and their mentors.</p>
            <div class="mt-9 flex flex-wrap items-center gap-4">
                <a href="{{ route(auth()->check() ? 'dashboard' : 'login') }}" class="inline-flex min-h-12 items-center rounded-xl bg-forest px-7 font-semibold text-white hover:bg-zinc-800">{{ auth()->check() ? 'Open dashboard' : 'Sign in to DutyTrack' }}</a>
                <a href="#benefits" class="inline-flex min-h-12 items-center font-semibold text-forest dark:text-sage">Explore the workflow <span aria-hidden="true" class="ml-2">→</span></a>
            </div>
        </div>
        <div class="grid gap-3 rounded-[24px] border border-zinc-200 bg-surface p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-7" aria-label="DutyTrack workflow preview">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-200 pb-4 dark:border-zinc-700"><span class="text-sm font-semibold uppercase tracking-widest text-zinc-500">Your practicum path</span><span class="rounded-full bg-sage px-3 py-1 text-xs font-semibold text-zinc-900">Stay on track</span></div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl bg-powder p-5 text-zinc-900"><flux:icon name="briefcase" class="size-6" /><h2 class="mt-5 font-semibold">Placement</h2><p class="mt-1 text-sm">Know your assignment and contacts.</p></div>
                <div class="rounded-2xl bg-butter p-5 text-zinc-900"><flux:icon name="clock" class="size-6" /><h2 class="mt-5 font-semibold">Duty hours</h2><p class="mt-1 text-sm">Follow rendered and approved time.</p></div>
                <div class="rounded-2xl bg-lavender p-5 text-zinc-900"><flux:icon name="clipboard-document-check" class="size-6" /><h2 class="mt-5 font-semibold">Requirements</h2><p class="mt-1 text-sm">See what needs submission or review.</p></div>
                <div class="rounded-2xl bg-sage p-5 text-zinc-900"><flux:icon name="check-badge" class="size-6" /><h2 class="mt-5 font-semibold">Evaluation</h2><p class="mt-1 text-sm">Keep feedback close to progress.</p></div>
            </div>
        </div>
    </div>
</section>
