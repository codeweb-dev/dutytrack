<section class="px-5 py-20 lg:px-8" id="faq">
    <div class="mx-auto max-w-3xl">
        <div class="mb-10 text-center"><span class="rounded-full bg-sage px-4 py-2 text-xs font-semibold uppercase tracking-widest text-zinc-900">Questions</span><h2 class="mt-5 text-4xl font-semibold tracking-tight text-zinc-950 dark:text-white sm:text-5xl">Good to know</h2><p class="mt-3 text-zinc-600 dark:text-zinc-300">Answers about recording and reviewing OJT progress.</p></div>
        @foreach ([
            ['How is attendance recorded?', 'Students record morning and afternoon time events. A location can be attached to time in, and the records remain visible for review.'],
            ['Who can review my progress?', 'Access follows each account’s role and OJT assignment. Coordinators and assigned supervisors see the records relevant to their work.'],
            ['Can I download reports and evaluations?', 'Students can download an accomplishment report and available evaluation documents from their workspace.'],
        ] as [$question, $answer])
            <details class="group mb-3 rounded-2xl border border-zinc-200 bg-surface p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-zinc-900 dark:text-white">{{ $question }}<span aria-hidden="true" class="text-forest group-open:rotate-45 dark:text-sage">+</span></summary>
                <p class="mt-4 border-t border-zinc-200 pt-4 text-sm leading-7 text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ $answer }}</p>
            </details>
        @endforeach
    </div>
</section>
