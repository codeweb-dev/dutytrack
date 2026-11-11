<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    @if (collect($this->columns)->every(fn($column) => $column['tasks']->isEmpty()))
        <flux:card class="flex flex-col items-center gap-4 py-16 sm:py-24">
            <flux:icon name="clipboard-document-list" class="size-12" />
            <div class="text-center">
                <flux:heading size="xl">No tasks yet</flux:heading>
                <flux:text class="mt-2">Tasks your supervisor assigns will show up here.</flux:text>
            </div>
        </flux:card>
    @else
        <flux:text class="flex items-center gap-2">
            <flux:icon name="hand-raised" variant="micro" />
            Drag a card by its handle to update your progress. Your supervisor reviews tasks in In Review.
        </flux:text>

        <div class="flex snap-x gap-4 overflow-x-auto pb-4">
            @foreach ($this->displayColumns() as $column)
                <section wire:key="student-kanban-column-{{ $column['status'] }}"
                    class="flex w-72 shrink-0 snap-start flex-col rounded-xl bg-zinc-100 p-2 sm:w-80 dark:bg-zinc-900">
                    <header class="flex items-center gap-2 px-2 pb-3 pt-2">
                        <flux:heading>{{ $column['title'] }}</flux:heading>
                        <flux:badge size="sm" :color="$this->statusColor($column['status'])">
                            {{ count($column['tasks']) }}
                        </flux:badge>
                    </header>

                    <div class="flex min-h-32 flex-1 flex-col gap-2" wire:sort="sortTask"
                        wire:sort:group="student-tasks" wire:sort:group-id="{{ $column['status'] }}"
                        @if ($column['status'] === 'revision') {{-- ponytail: must sit after wire:sort:group, Livewire stops scanning wire:sort* attrs there --}}
                            wire:sort:config="{ group: { name: 'student-tasks', put: false } }" @endif>
                        @foreach ($column['tasks'] as $task)
                            <x-task-card :task="$task" wire:key="student-task-{{ $task->id }}"
                                wire:sort:item="{{ $task->id }}">
                                <flux:avatar size="xs"
                                    name="{{ $task->assignedBy?->display_name ?? 'Supervisor' }}" />
                                <flux:text size="sm" class="truncate">
                                    Assigned by {{ $task->assignedBy?->display_name ?? 'Supervisor' }}
                                </flux:text>
                            </x-task-card>
                        @endforeach

                        {{-- ponytail: last child + only:flex, so it hides itself the moment a card is dropped in --}}
                        <div wire:sort:ignore
                            class="hidden flex-1 items-center justify-center rounded-lg border border-dashed border-zinc-300 p-6 text-sm text-zinc-500 only:flex dark:border-zinc-700 dark:text-zinc-400">
                            No tasks here
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</div>
