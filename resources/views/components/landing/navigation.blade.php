<nav class="anim-nav max-w-6xl mx-auto w-full sticky top-0 z-50 backdrop-blur-xs">
    <div class="flex items-center justify-between px-12 py-2 w-full rounded-b-xl">
        <div class="flex items-center gap-2 font-bold text-lg text-gray-900 dark:text-white">
            <div
                class="bg-red-600 hover:bg-red-700 text-white p-1 rounded-lg text-sm font-semibold transition-all shadow-md shadow-red-200 dark:shadow-red-900/40">
                <flux:icon.clock />
            </div>
            Duty Track
        </div>

        <div class="hidden md:flex items-center text-sm gap-6">
            <a href="#home"
                class="text-gray-700 dark:text-zinc-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                Home
            </a>
            <a href="#benefits"
                class="text-gray-700 dark:text-zinc-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                Benefits
            </a>
            <a href="#comparison"
                class="text-gray-700 dark:text-zinc-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                Comparison
            </a>
            <a href="#faq"
                class="text-gray-700 dark:text-zinc-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                FAQ
            </a>
            <a href="#teams"
                class="text-gray-700 dark:text-zinc-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                Teams
            </a>
        </div>

        <div class="flex items-center gap-3">
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />

            @if (Route::has('login'))
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors no-underline shadow-md shadow-red-200 dark:shadow-red-900/40">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-all shadow-md shadow-red-200 dark:shadow-red-900/40 no-underline">
                            Log in
                        </a>
                    @endauth
                </div>
            @else
                <a href="#"
                    class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors no-underline">
                    Get Template
                </a>
            @endif
        </div>
    </div>
</nav>
