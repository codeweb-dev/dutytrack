<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <flux:modal name="create-supervisor-task" flyout variant="floating" class="md:w-xl" @close="closeTaskForm">
        <form wire:submit="saveTask" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editing_task_key ? 'Edit Task' : 'Assign Task' }}</flux:heading>
                <flux:text class="mt-2">
                    {{ $editing_task_key
                        ? 'Changes apply to every trainee on this task. Its progress stays the same.'
                        : 'Create a task for one or more of your trainees.' }}
                </flux:text>
            </div>

            @if ($editing_task_key)
                {{-- ponytail: trainees are fixed once assigned; assign a new task to add someone --}}
            @elseif ($this->assignableStudents->isEmpty())
                <flux:callout variant="secondary" icon="user-group" heading="No trainees assigned to you yet."
                    text="Tasks can be assigned once a coordinator assigns trainees to you." />
            @else
                <flux:checkbox.group wire:model="student_ids" label="Trainees" variant="cards" class="-m-1 max-h-80 flex-wrap overflow-y-auto p-1 *:grow-0 *:basis-full sm:*:basis-[calc(50%-0.375rem)]" required>
                    @foreach ($this->assignableStudents as $student)
                        <flux:checkbox value="{{ $student->id }}" :label="$student->display_name"
                            :description="$student->studentProfile?->student_id ?? 'No student ID'" />
                    @endforeach
                </flux:checkbox.group>
            @endif

            <flux:input wire:model="task_title" label="Task Title"
                placeholder="Prepare daily accomplishment update" required />

            <flux:textarea wire:model="task_description" label="Description" rows="4" badge="Optional"
                placeholder="Add instructions, expected output, links, or reminders." />

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:radio.group wire:model="priority" label="Priority" variant="segmented" required>
                    <flux:radio value="low" label="Low" />
                    <flux:radio value="normal" label="Normal" />
                    <flux:radio value="high" label="High" />
                </flux:radio.group>

                <flux:input type="date" wire:model="due_date" label="Due Date" badge="Optional"
                    :min="$editing_task_key ? null : today()->toDateString()" />
            </div>

            @if ($editing_task_key)
                <flux:button type="submit" variant="primary" icon="check" class="w-full">Save Changes</flux:button>
            @else
                <flux:button type="submit" variant="primary" icon="plus" class="w-full"
                    :disabled="$this->assignableStudents->isEmpty()">
                    Assign Task
                </flux:button>
            @endif
        </form>
    </flux:modal>

    <flux:modal name="request-revision" class="md:w-lg">
        <form wire:submit="requestRevision" class="space-y-6">
            <div>
                <flux:heading size="lg">Request Revision</flux:heading>
                <flux:text class="mt-2">Tell the trainee what to fix. The task moves to Revision so they can redo it.</flux:text>
            </div>

            <flux:textarea wire:model="revision_note" label="Comment" rows="4"
                placeholder="Explain what needs to be changed." required />

            <flux:button type="submit" variant="primary" icon="arrow-path" class="w-full">
                Request Revision
            </flux:button>
        </form>
    </flux:modal>

    @if ($this->summary['total'] === 0 && $this->summary['archived'] === 0)
        <flux:card class="flex flex-col items-center gap-4 py-16 sm:py-24">
            <flux:icon name="clipboard-document-list" class="size-12" />
            <div class="text-center">
                <flux:heading size="xl">No tasks assigned yet</flux:heading>
                <flux:text class="mt-2">Assign a task and track your trainees' progress on the board.</flux:text>
            </div>
            <flux:modal.trigger name="create-supervisor-task">
                <flux:button icon="plus" variant="primary">Assign Task</flux:button>
            </flux:modal.trigger>
        </flux:card>
    @else
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                placeholder="Search tasks or trainees..." clearable class="w-full sm:w-72" />

            <div class="flex gap-2">
                <flux:button wire:click="$toggle('show_archived')" :icon="$show_archived ? 'view-columns' : 'archive-box'"
                    class="flex-1 sm:flex-none">
                    {{ $show_archived ? 'Back to Board' : 'Archived ('.$this->summary['archived'].')' }}
                </flux:button>

                <flux:modal.trigger name="create-supervisor-task">
                    <flux:button icon="plus" variant="primary" class="flex-1 sm:flex-none">Assign Task</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        @if ($show_archived)
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($this->archivedTasks as $taskCard)
                    <flux:card wire:key="archived-task-{{ $taskCard['key'] }}" class="flex flex-col gap-3 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <flux:heading class="min-w-0 break-words">{{ $taskCard['task']->title }}</flux:heading>
                            <flux:badge size="sm" :color="$this->statusColor($taskCard['task']->status)">
                                {{ \App\Models\StudentTask::statusLabels()[$taskCard['task']->status] ?? $taskCard['task']->status }}
                            </flux:badge>
                        </div>

                        <flux:text size="sm" class="truncate">
                            {{ $taskCard['students']->pluck('display_name')->join(', ') ?: 'Trainee' }}
                        </flux:text>

                        <div class="mt-auto flex items-center justify-between gap-2">
                            <flux:text size="sm">Archived {{ $taskCard['task']->archived_at->diffForHumans() }}</flux:text>
                            <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreTask('{{ $taskCard['key'] }}')">
                                Restore
                            </flux:button>
                        </div>
                    </flux:card>
                @empty
                    <flux:text class="col-span-full py-12 text-center">
                        {{ $search !== '' ? 'No matching archived tasks' : 'No archived tasks' }}
                    </flux:text>
                @endforelse
            </div>
        @else
        <div class="flex snap-x gap-4 overflow-x-auto pb-4">
            @foreach ($this->displayColumns() as $column)
                <section wire:key="supervisor-task-column-{{ $column['status'] }}"
                    class="flex w-72 shrink-0 snap-start flex-col rounded-xl bg-zinc-100 p-2 sm:w-80 dark:bg-zinc-900">
                    <header class="flex items-center gap-2 px-2 pb-3 pt-2">
                        <flux:heading>{{ $column['title'] }}</flux:heading>
                        <flux:badge size="sm" :color="$this->statusColor($column['status'])">
                            {{ count($column['tasks']) }}
                        </flux:badge>
                    </header>

                    <div class="flex min-h-32 flex-1 flex-col gap-2" wire:sort="sortTaskCard"
                        wire:sort:group="supervisor-tasks" wire:sort:group-id="{{ $column['status'] }}">
                        @foreach ($column['tasks'] as $taskCard)
                            @php($students = $taskCard['students'])

                            <x-task-card :task="$taskCard['task']" wire:key="supervisor-task-{{ $taskCard['key'] }}"
                                wire:sort:item="{{ $taskCard['key'] }}">
                                <x-slot:actions>
                                    <div wire:sort:ignore>
                                        <flux:dropdown position="bottom" align="end">
                                            <flux:button size="xs" variant="subtle" icon="ellipsis-horizontal"
                                                aria-label="Task actions" />

                                            <flux:menu>
                                                <flux:menu.item icon="pencil-square"
                                                    wire:click="editTask('{{ $taskCard['key'] }}')">
                                                    Edit task
                                                </flux:menu.item>
                                                <flux:menu.item icon="arrow-path"
                                                    wire:click="openRevision('{{ $taskCard['key'] }}')">
                                                    Request revision
                                                </flux:menu.item>
                                                <flux:menu.separator />
                                                <flux:menu.item icon="archive-box"
                                                    wire:click="archiveTask('{{ $taskCard['key'] }}')">
                                                    Archive task
                                                </flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </x-slot:actions>

                                <div class="flex shrink-0 gap-0.5">
                                    @foreach ($taskCard['visible_students'] as $student)
                                        <flux:tooltip :content="$student->display_name">
                                            <flux:avatar size="xs" name="{{ $student->display_name }}" />
                                        </flux:tooltip>
                                    @endforeach

                                    @if ($taskCard['hidden_students'] > 0)
                                        <flux:avatar size="xs">+{{ $taskCard['hidden_students'] }}</flux:avatar>
                                    @endif
                                </div>

                                <div class="min-w-0 text-sm leading-tight">
                                    @if ($students->count() === 1)
                                        <span class="block truncate font-medium text-zinc-800 dark:text-white">
                                            {{ $students->first()?->display_name ?? 'Trainee' }}
                                        </span>
                                        <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $students->first()?->studentProfile?->student_id ?? 'No student ID' }}
                                        </span>
                                    @else
                                        <span class="block truncate font-medium text-zinc-800 dark:text-white">
                                            {{ $students->count() }} trainees
                                        </span>
                                        <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $students->pluck('display_name')->join(', ') }}
                                        </span>
                                    @endif
                                </div>
                            </x-task-card>
                        @endforeach

                        {{-- ponytail: last child + only:flex, so it hides itself the moment a card is dropped in --}}
                        <div wire:sort:ignore
                            class="hidden flex-1 items-center justify-center rounded-lg border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 only:flex dark:border-zinc-700 dark:text-zinc-400">
                            {{ $search !== '' ? 'No matching tasks' : 'No tasks here' }}
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
        @endif
    @endif
</div>
