<nav class="sticky top-0 z-50" aria-label="Main navigation">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-3 lg:px-8">
        <a href="#home" class="flex items-center gap-3 text-lg font-semibold text-zinc-900 dark:text-white">
            <span class="flex size-10 items-center justify-center rounded-xl bg-forest"><img src="{{ asset('logo.png') }}" alt="" class="size-8 object-contain"></span>
            DutyTrack
        </a>
        <div class="hidden items-center gap-7 text-sm font-medium lg:flex">
            <a href="#benefits" class="hover:text-forest dark:hover:text-sage">Benefits</a>
            <a href="#comparison" class="hover:text-forest dark:hover:text-sage">Why DutyTrack</a>
            <a href="#faq" class="hover:text-forest dark:hover:text-sage">FAQ</a>
            <a href="#teams" class="hover:text-forest dark:hover:text-sage">Team</a>
        </div>
        <div class="flex items-center gap-2">
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle light and dark appearance" />
            <a href="{{ route(auth()->check() ? 'dashboard' : 'login') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-forest px-5 text-sm font-semibold text-white hover:bg-zinc-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest">
                {{ auth()->check() ? 'Dashboard' : 'Sign in' }}
            </a>
            <details class="relative lg:hidden">
                <summary class="flex min-h-11 min-w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-zinc-200" aria-label="Open site menu"><flux:icon name="bars-3" class="size-5" /></summary>
                <div class="absolute right-0 top-12 grid min-w-44 gap-1 rounded-xl border border-zinc-200 bg-white p-2 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <a class="rounded-lg px-3 py-2" href="#benefits">Benefits</a><a class="rounded-lg px-3 py-2" href="#comparison">Why DutyTrack</a><a class="rounded-lg px-3 py-2" href="#faq">FAQ</a><a class="rounded-lg px-3 py-2" href="#teams">Team</a>
                </div>
            </details>
        </div>
    </div>
</nav>
