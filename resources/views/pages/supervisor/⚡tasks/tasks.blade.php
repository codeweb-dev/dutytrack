<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="space-y-4">
        <div class="flex justify-end">
            <flux:modal.trigger name="create-supervisor-task">
                <flux:button icon="plus" variant="primary">Assign Task</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="create-supervisor-task" flyout variant="floating" class="md:w-xl">
            <form wire:submit="saveTask" class="space-y-6">
                <div>
                    <flux:heading size="lg">Assign Task</flux:heading>
                    <flux:text class="mt-2">Create a task for one or more assigned trainees.</flux:text>
                </div>

                <flux:checkbox.group wire:model="student_ids" label="Trainees" variant="cards" class="max-sm:flex-col" required>
                    @foreach ($this->assignableStudents as $student)
                        <flux:checkbox value="{{ $student->id }}" :label="$student->display_name"
                            :description="$student->studentProfile?->student_id ?? 'No student ID'" />
                    @endforeach
                </flux:checkbox.group>

                <flux:input wire:model="task_title" label="Task Title"
                    placeholder="Prepare daily accomplishment update" required />

                <flux:textarea wire:model="task_description" label="Description" rows="4" badge="Optional"
                    placeholder="Add instructions, expected output, links, or reminders." />

                <flux:radio.group wire:model="priority" label="Priority" variant="pills" required>
                    <flux:radio value="low" label="Low" />
                    <flux:radio value="normal" label="Normal" />
                    <flux:radio value="high" label="High" />
                </flux:radio.group>

                <flux:input type="date" wire:model="due_date" label="Due Date" badge="Optional" />

                <flux:button type="submit" variant="primary" icon="plus" class="w-full">
                    Assign Task
                </flux:button>
            </form>
        </flux:modal>

        <div class="relative flex w-full gap-3 overflow-x-auto pb-2">
            @foreach ($this->displayColumns() as $column)
                <article wire:key="supervisor-task-column-{{ str($column['title'])->slug() }}"
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

                    <div class="flex min-h-24 flex-1 flex-col gap-3" wire:sort="sortTaskCard"
                        wire:sort:group="supervisor-tasks" wire:sort:group-id="{{ $column['status'] }}">
                        @foreach ($column['tasks'] as $taskCard)
                            @php($task = $taskCard['task'])

                            <div class="flex h-full w-full flex-col rounded-lg border border-neutral-300 bg-white dark:border-neutral-700 dark:bg-zinc-800"
                                wire:key="supervisor-task-{{ $taskCard['key'] }}" wire:sort:item="{{ $taskCard['key'] }}">
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
                                            @foreach ($taskCard['visible_students'] as $student)
                                                <flux:tooltip :content="$student->display_name">
                                                    <flux:avatar size="xs" name="{{ $student->display_name }}" />
                                                </flux:tooltip>
                                            @endforeach

                                            @if ($taskCard['hidden_students'] > 0)
                                                <flux:avatar size="xs">{{ $taskCard['hidden_students'] }}+
                                                </flux:avatar>
                                            @endif
                                        </flux:avatar.group>

                                        <div class="min-w-0 text-sm text-neutral-500">
                                            @if ($taskCard['students']->count() === 1)
                                                <span
                                                    class="block truncate">{{ $taskCard['students']->first()?->display_name ?? 'Trainee' }}</span>
                                                <span class="block truncate text-xs">
                                                    {{ $taskCard['students']->first()?->studentProfile?->student_id ?? 'No student ID' }}
                                                </span>
                                            @else
                                                <span class="block truncate">{{ $taskCard['students']->count() }}
                                                    trainees</span>
                                                <span class="block truncate text-xs">
                                                    {{ $taskCard['students']->pluck('name')->take(2)->join(', ') }}{{ $taskCard['students']->count() > 2 ? ', +' . ($taskCard['students']->count() - 2) : '' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <flux:badge icon="calendar" rounded size="sm">
                                            {{ $task->due_date?->format('M d') ?? 'No due' }}
                                        </flux:badge>

                                        <div wire:sort:ignore>
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button size="sm" variant="subtle" icon="ellipsis-horizontal" />

                                                <flux:menu>
                                                    <flux:menu.item icon="eye">
                                                        {{ $taskCard['students']->count() === 1 ? $taskCard['students']->first()?->display_name ?? 'Trainee' : $taskCard['students']->count() . ' trainees' }}
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="calendar">
                                                        {{ $task->due_date?->format('M d, Y') ?? 'No due date' }}
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
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
