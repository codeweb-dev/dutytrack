{{-- Repeatable one-line list (technical skills, personal skills). Enter adds a new item below. --}}
@props(['field', 'items', 'label', 'placeholder' => '', 'addLabel'])

<div class="space-y-2">
    @forelse ($items as $i => $item)
        <div wire:key="{{ $field }}-{{ $i }}" class="flex items-start gap-2">
            <span class="mt-4 size-1.5 shrink-0 rounded-full bg-zinc-400" aria-hidden="true"></span>
            <div class="min-w-0 flex-1">
                <flux:input wire:model="data.{{ $field }}.{{ $i }}" id="{{ $field }}-{{ $i }}" maxlength="150"
                    :placeholder="$i === 0 ? $placeholder : ''" aria-label="{{ $label }} {{ $i + 1 }}"
                    x-on:keydown.enter.prevent="$wire.addItem('{{ $field }}', {{ $i + 1 }}).then(() => $nextTick(() => document.getElementById('{{ $field }}-{{ $i + 1 }}')?.focus()))" />
                <flux:error name="data.{{ $field }}.{{ $i }}" />
            </div>
            <x-resume.item-controls :field="$field" :index="$i" :count="count($items)" :label="$label" />
        </div>
    @empty
        <p class="rounded-md bg-zinc-50 px-3 py-4 text-center text-sm text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
            Nothing here yet. This section is hidden on your resume until you add one.
        </p>
    @endforelse

    <div class="flex items-center justify-between gap-2 pt-1">
        <flux:button size="sm" variant="ghost" icon="plus" wire:click="addItem('{{ $field }}')">{{ $addLabel }}</flux:button>
        <span class="hidden text-xs text-zinc-400 sm:inline">Press Enter to add another</span>
    </div>
</div>
