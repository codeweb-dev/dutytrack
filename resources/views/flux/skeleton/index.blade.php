@blaze(fold: true)

@aware(['animate' => null])

@props([
    'animate' => null,
])

@php
    $classes = Flux::classes()
        ->add('[:where(&)]:h-4 [:where(&)]:rounded-md [:where(&)]:bg-zinc-400/20')
        ->add(match ($animate) {
            'shimmer' => 'animate-pulse',
            'pulse' => 'animate-pulse',
            default => '',
        })
        ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-skeleton>
    {{ $slot }}
</div>
