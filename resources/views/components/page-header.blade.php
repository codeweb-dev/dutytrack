<header class="flex flex-col gap-4 border-b border-zinc-200 pb-5 dark:border-zinc-700">
    @if(count($breadcrumbs))
        <flux:breadcrumbs>
            @foreach($breadcrumbs as $breadcrumb)
                @if(!empty($breadcrumb['logo']))
                    <flux:breadcrumbs.item href="{{ $breadcrumb['href'] ?? '#' }}">
                        <img src="{{ asset($breadcrumb['logo']) }}" alt="logo" class="h-4 w-4 object-cover" />
                    </flux:breadcrumbs.item>
                @else
                    <flux:breadcrumbs.item href="{{ $breadcrumb['href'] ?? '#' }}">
                        {{ $breadcrumb['label'] ?? '' }}
                    </flux:breadcrumbs.item>
                @endif
            @endforeach
        </flux:breadcrumbs>
    @endif

    <div>
        <flux:heading level="1" size="xl" class="text-3xl! font-semibold! tracking-tight sm:text-4xl!">{{ $title }}</flux:heading>

        @if($description)
            <flux:text class="mt-1">
                {{ $description }}
            </flux:text>
        @endif
    </div>
</header>
