<div class="flex flex-col gap-6">
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
        <h1 class="text-2xl font-bold">{{ $title }}</h1>

        @if($description)
            <flux:text class="mt-1 text-sm">
                {{ $description }}
            </flux:text>
        @endif
    </div>
</div>