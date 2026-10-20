<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="duty-board relative flex w-full gap-4 overflow-x-auto pb-4">
            @foreach ($this->displayColumns() as $column)
                <article wire:key="student-kanban-column-{{ str($column['title'])->slug() }}"
                    class="duty-board-column w-80 shrink-0 rounded-2xl p-3">
                    <header class="flex items-start justify-between gap-3 px-1 pb-4 pt-2">
                        <div class="space-y-2">
                            <flux:heading size="lg">
                                {{ $column['title'] }}
                            </flux:heading>

                            <flux:subheading size="sm">
                                {{ count($column['tasks']) }} {{ count($column['tasks']) === 1 ? 'task' : 'tasks' }}
                            </flux:subheading>
                        </div>

                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="subtle" size="sm" icon="ellipsis-horizontal" />

                            <flux:menu class="min-w-40">
                                <flux:menu.item icon="eye">View column</flux:menu.item>
                                <flux:menu.item icon="archive-box">Archive completed</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </header>

                    <div class="flex min-h-24 flex-1 flex-col gap-3" wire:sort="sortTask"
                        wire:sort:group="student-tasks" wire:sort:group-id="{{ $column['status'] }}">
                        @foreach ($column['tasks'] as $task)
                            <div class="duty-task-card flex h-full w-full flex-col rounded-xl border p-0"
                                wire:key="student-task-{{ $task->id }}" wire:sort:item="{{ $task->id }}">
                                <div class="p-4">
                                    <div class="mb-3 flex items-start justify-between gap-3">
                                        <div class="flex flex-wrap gap-2">
                                            <flux:badge :color="$this->priorityColor($task->priority)" size="sm">
                                                {{ ucfirst($task->priority) }}
                                            </flux:badge>

                                            <flux:badge :color="$this->statusColor($task->status)" size="sm">
                                                {{ \App\Models\StudentTask::statusLabels()[$task->status] ?? ucfirst($task->status) }}
                                            </flux:badge>
                                        </div>

                                        <button type="button" wire:sort:handle
                                            class="shrink-0 cursor-grab rounded-md p-1 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600 active:cursor-grabbing dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                                            aria-label="Drag task">
                                            <flux:icon name="grip-vertical" variant="micro" />
                                        </button>
                                    </div>

                                    <flux:heading>
                                        {{ $task->title }}
                                    </flux:heading>

                                    <flux:subheading>
                                        {{ $task->description ?: 'No description provided.' }}
                                    </flux:subheading>
                                </div>

                                <flux:separator />

                                <div class="flex items-center justify-between gap-2 px-4 pb-2 pt-3">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <flux:avatar.group>
                                            <flux:avatar size="xs" name="{{ $task->assignedBy?->name ?? 'Supervisor' }}" />
                                        </flux:avatar.group>

                                        <div class="min-w-0 text-sm text-neutral-500">
                                            <span class="block truncate">{{ $task->assignedBy?->name ?? 'Supervisor' }}</span>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <flux:badge icon="calendar" rounded size="sm">
                                            {{ $task->due_date?->format('M d') ?? 'No due' }}
                                        </flux:badge>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>
