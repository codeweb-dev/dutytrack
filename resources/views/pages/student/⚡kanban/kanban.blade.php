<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="relative flex w-full gap-5 overflow-x-auto pb-2">
            @foreach ($this->columns as $column)
                <article wire:key="student-kanban-column-{{ str($column['title'])->slug() }}"
                    class="dark:bg-zinc-900 bg-zinc-200 p-3 rounded-lg">
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
                                <flux:menu.item icon="plus">New task</flux:menu.item>
                                <flux:menu.item icon="pencil">Rename column</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="archive-box">Archive</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </header>

                    <div class="flex flex-1 flex-col gap-3">
                        @foreach ($column['tasks'] as $task)
                            <div class="flex h-full w-full flex-col rounded-lg border border-neutral-300 bg-white dark:border-neutral-700 dark:bg-zinc-800"
                                wire:key="student-kanban-task-{{ str($column['title'] . '-' . $task['title'])->slug() }}">
                                <div class="p-4">
                                    @if (filled($task['labels']))
                                        <div>
                                            <div class="mb-3 flex flex-wrap gap-2">
                                                @foreach ($task['labels'] as $label)
                                                    <flux:badge color="{{ $this->labelClasses($label) }}"2>
                                                        {{ $label }}
                                                    </flux:badge>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <flux:heading>
                                        {{ $task['title'] }}
                                    </flux:heading>

                                    <flux:subheading>
                                        Task description or details can go here. This is a placeholder for the task
                                        content.
                                    </flux:subheading>
                                </div>

                                <flux:separator />

                                <div class="flex items-center gap-2 pb-2 pt-3 px-4">
                                    <flux:badge icon="clock" rounded size="sm">Log: 4h</flux:badge>
                                    <flux:badge icon="calendar" rounded size="sm">Apr 30</flux:badge>
                                    {{-- <flux:avatar name="Jane Doe" size="sm" />

                                    <flux:text class="text-sm text-neutral-500">
                                        Assigned to Jane Doe
                                    </flux:text> --}}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>