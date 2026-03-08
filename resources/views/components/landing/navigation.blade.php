<nav class="anim-nav max-w-6xl mx-auto w-full sticky top-0 z-50 backdrop-blur-xs">
    <div class="flex items-center justify-between px-12 py-4 w-full rounded-b-xl">
        <div class="flex items-center gap-2 font-bold text-lg text-gray-900 dark:text-white">
            <div
                class="bg-red-600 hover:bg-red-700 text-white p-1 rounded-lg text-sm font-semibold transition-all shadow-md shadow-red-200 dark:shadow-red-900/40">
                <flux:icon.clock />
            </div>
            Duty Track
        </div>

        <div class="flex items-center gap-3">
            <flux:dropdown x-data align="end">
                <flux:button variant="subtle" square class="group" aria-label="Preferred color scheme">
                    <flux:icon.sun x-show="$flux.appearance === 'light'" variant="mini"
                        class="text-zinc-500 dark:text-white" />
                    <flux:icon.moon x-show="$flux.appearance === 'dark'" variant="mini"
                        class="text-zinc-500 dark:text-white" />
                    <flux:icon.moon x-show="$flux.appearance === 'system' && $flux.dark" variant="mini" />
                    <flux:icon.sun x-show="$flux.appearance === 'system' && ! $flux.dark" variant="mini" />
                </flux:button>
                <flux:menu>
                    <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Light</flux:menu.item>
                    <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Dark</flux:menu.item>
                    <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">System
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>

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
    </nav>
</nav>
