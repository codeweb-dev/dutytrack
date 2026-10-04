<footer class="border-t border-zinc-200 bg-surface dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mx-auto flex max-w-7xl flex-col justify-between gap-8 px-5 py-10 sm:flex-row lg:px-8">
        <div class="max-w-md"><div class="flex items-center gap-3 font-semibold text-zinc-900 dark:text-white"><img src="{{ asset('logo.png') }}" alt="" class="size-9 object-contain">DutyTrack</div><p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">One workspace for student practicum records, review, and progress.</p></div>
        <div class="flex flex-wrap items-start gap-6 text-sm"><a href="#home">Home</a><a href="#benefits">Benefits</a><a href="#faq">FAQ</a><a href="#teams">Team</a><a href="{{ route(auth()->check() ? 'dashboard' : 'login') }}" class="font-semibold text-forest dark:text-sage">{{ auth()->check() ? 'Dashboard' : 'Sign in' }}</a></div>
    </div>
    <div class="border-t border-zinc-200 px-5 py-4 text-center text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-300">© {{ date('Y') }} DutyTrack</div>
</footer>
