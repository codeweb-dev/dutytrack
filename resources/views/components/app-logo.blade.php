@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Duty Track" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-24 items-center justify-center rounded-md">
            <x-app-logo-icon class="size-24 rounded-2xl border-2 border-white/60 bg-white object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Duty Track" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
