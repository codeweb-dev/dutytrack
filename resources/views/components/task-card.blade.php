@props(['task'])

@php
    $open = $task->status !== 'done' && $task->due_date;
    $overdue = $open && $task->due_date->lt(today());
    $dueSoon = $open && ! $overdue && $task->due_date->lte(today()->addDay());

    $dueLabel = match (true) {
        ! $task->due_date => null,
        $overdue => 'Overdue · '.$task->due_date->format('M d'),
        $open && $task->due_date->isToday() => 'Due today',
        $open && $task->due_date->isTomorrow() => 'Due tomorrow',
        default => 'Due '.$task->due_date->format('M d'),
    };
@endphp

<div {{ $attributes->class('rounded-lg border border-zinc-200 bg-white p-3 shadow-xs transition-colors hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600') }}>
    <div class="flex items-start gap-1">
        <flux:heading class="min-w-0 flex-1 break-words pt-0.5 leading-snug">{{ $task->title }}</flux:heading>

        {{ $actions ?? '' }}

        <button type="button" wire:sort:handle aria-label="Drag to move task"
            class="-mr-1 shrink-0 cursor-grab touch-none rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 active:cursor-grabbing dark:hover:bg-zinc-700 dark:hover:text-zinc-200">
            <flux:icon name="grip-vertical" variant="micro" />
        </button>
    </div>

    @if ($task->description)
        <flux:text size="sm" class="mt-1 line-clamp-3 whitespace-pre-line">{{ $task->description }}</flux:text>
    @endif

    @if ($task->revision_note && $task->status !== 'done')
        <div class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:bg-amber-400/10 dark:text-amber-200">
            <p class="flex items-center gap-1.5 font-medium">
                <flux:icon name="arrow-path" variant="micro" />
                Revision requested
            </p>
            <p class="mt-1 whitespace-pre-line break-words">{{ $task->revision_note }}</p>
        </div>
    @endif

    @if ($task->priority !== 'normal' || $dueLabel)
        <div class="mt-3 flex flex-wrap items-center gap-1.5">
            @if ($task->priority === 'high')
                <flux:badge size="sm" :color="\App\Support\BadgeColor::for('high')" icon="arrow-up">High priority</flux:badge>
            @elseif ($task->priority === 'low')
                <flux:badge size="sm" color="zinc" icon="arrow-down">Low priority</flux:badge>
            @endif

            @if ($dueLabel)
                <flux:badge size="sm" icon="calendar" :color="\App\Support\BadgeColor::for($overdue ? 'overdue' : ($dueSoon ? 'due_soon' : null))">
                    {{ $dueLabel }}
                </flux:badge>
            @endif
        </div>
    @endif

    <div class="mt-3 flex min-w-0 items-center gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-700">
        {{ $slot }}
    </div>
</div>
