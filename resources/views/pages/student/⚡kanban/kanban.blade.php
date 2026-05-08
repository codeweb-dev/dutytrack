<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="relative flex w-full gap-3 overflow-x-auto pb-2">
            @foreach ($this->displayColumns() as $column)
                <article wire:key="student-kanban-column-{{ str($column['title'])->slug() }}"
                    class="w-80 shrink-0 rounded-lg bg-zinc-200 p-3 dark:bg-zinc-900">
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

                    <div class="flex flex-1 flex-col gap-3">
                        @forelse ($column['tasks'] as $task)
                            <div class="flex h-full w-full flex-col rounded-lg border border-neutral-300 bg-white dark:border-neutral-700 dark:bg-zinc-800"
                                wire:key="student-task-{{ $task->id }}">
                                <div class="p-4">
                                    <div class="mb-3 flex flex-wrap gap-2">
                                        <flux:badge :color="$this->priorityColor($task->priority)" size="sm">
                                            {{ ucfirst($task->priority) }}
                                        </flux:badge>

                                        <flux:badge :color="$this->statusColor($task->status)" size="sm">
                                            {{ \App\Models\StudentTask::statusLabels()[$task->status] ?? ucfirst($task->status) }}
                                        </flux:badge>
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
                                            <flux:avatar size="xs" name="{{ $task->assignedBy?->name ?? 'Professor' }}" />
                                        </flux:avatar.group>

                                        <div class="min-w-0 text-sm text-neutral-500">
                                            <span class="block truncate">{{ $task->assignedBy?->name ?? 'Professor' }}</span>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <flux:badge icon="calendar" rounded size="sm">
                                            {{ $task->due_date?->format('M d') ?? 'No due' }}
                                        </flux:badge>

                                        <flux:dropdown position="bottom" align="end">
                                            <flux:button size="sm" variant="subtle" icon="ellipsis-horizontal" />

                                            <flux:menu>
                                                @foreach (\App\Models\StudentTask::statusLabels() as $status => $label)
                                                    <flux:menu.item wire:click="updateTaskStatus({{ $task->id }}, '{{ $status }}')"
                                                        icon="check-circle">
                                                        {{ $label }}
                                                    </flux:menu.item>
                                                @endforeach
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="rounded-lg border border-dashed border-zinc-300 bg-white/60 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/40">
                                No tasks here.
                            </div>
                        @endforelse
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>
