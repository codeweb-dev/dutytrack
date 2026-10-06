<header>
    <div>
        <flux:heading level="1" size="xl" class="text-4xl! font-semibold! tracking-tight sm:text-[2.75rem]!">{{ $title }}</flux:heading>

        @if($description)
            <flux:text class="mt-2 max-w-3xl text-xl!">
                {{ $description }}
            </flux:text>
        @endif
    </div>
</header>
