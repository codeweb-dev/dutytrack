<div class="flex flex-col gap-4">
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
        <flux:heading size="xl">{{ $title }}</flux:heading>

        @if($description)
            <flux:text class="mt-1">
                {{ $description }}
            </flux:text>
        @endif
    </div>
</div>