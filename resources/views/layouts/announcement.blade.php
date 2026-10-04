<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="duty-main">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
