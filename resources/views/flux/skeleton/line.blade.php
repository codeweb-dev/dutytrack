@blaze(fold: true)

@aware(['animate' => null])

@props([
    'size' => 'base',
    'animate' => null,
])

@php
$classes = Flux::classes()
    ->add('[:where(&)]:w-full')
    ->add(match ($size) {
        'base' => '[:where(&)]:h-5 py-[3px]',
        'lg' => 'h-6 py-[2px]',
    })
    ->add(match ($animate) {
        'shimmer' => 'animate-pulse',
        'pulse' => 'animate-pulse',
        default => '',
    })
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-skeleton-line>
    <div class="h-full [:where(&)]:rounded [:where(&)]:bg-zinc-400/20">{{ $slot }}</div>
</div>
