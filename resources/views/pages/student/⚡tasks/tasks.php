<?php

use App\Models\StudentTask;
use App\Notifications\SystemNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Productivity', 'href' => '#'],
        ['label' => 'Tasks'],
    ];

    public string $title = 'Tasks';

    public string $description = 'Track tasks assigned by your supervisor and update their progress as you work.';

    public function statusColor(?string $status): string
    {
        return match ($status) {
            'done' => 'green',
            'in_review' => 'blue',
            'revision' => 'orange',
            'in_progress' => 'amber',
            default => 'zinc',
        };
    }

    public function displayColumns(): array
    {
        $columns = collect($this->columns)->keyBy('status');

        return collect(['pending', 'in_progress', 'in_review', 'revision', 'done'])
            ->map(fn (string $status): array => $columns->get($status))
            ->filter()
            ->values()
            ->all();
    }

    public function sortTask(string $taskId, int $position, string $status): void
    {
        abort_unless(array_key_exists($status, StudentTask::statusLabels()), 422);

        $task = StudentTask::query()
            ->where('student_id', Auth::id())
            ->findOrFail((int) $taskId);

        $previousStatus = $task->status;

        // Only the supervisor can send a task back for revision.
        abort_if($status === 'revision' && $previousStatus !== 'revision', 403);

        DB::transaction(function () use ($task, $position, $status, $previousStatus): void {
            $task->update([
                'status' => $status,
                'sort_order' => max(0, $position),
                'completed_at' => $status === 'done' ? now() : null,
            ]);

            $this->reorderStudentStatus($status, $task->id, $position);

            if ($previousStatus !== $status) {
                $this->reorderStudentStatus($previousStatus);
            }
        });

        if ($previousStatus !== $status) {
            $task->assignedBy?->notify(new SystemNotification(
                $status === 'in_review' ? 'Task submitted for review' : 'Task moved to '.StudentTask::statusLabels()[$status],
                Auth::user()->display_name.': '.$task->title, route('supervisor.tasks'), 'rectangle-stack',
            ));
        }

        unset($this->columns);
    }

    protected function reorderStudentStatus(string $status, ?int $movedTaskId = null, ?int $position = null): void
    {
        $tasks = StudentTask::query()
            ->where('student_id', Auth::id())
            ->where('status', $status)
            ->when($movedTaskId, fn ($query) => $query->whereKeyNot($movedTaskId))
            ->orderBy('sort_order')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('created_at')
            ->get();

        if ($movedTaskId !== null) {
            $task = StudentTask::query()
                ->where('student_id', Auth::id())
                ->where('status', $status)
                ->findOrFail($movedTaskId);

            $tasks->splice(min(max(0, $position ?? 0), $tasks->count()), 0, [$task]);
        }

        $this->saveTaskOrder($tasks);
    }

    protected function saveTaskOrder(Collection $tasks): void
    {
        $tasks->values()->each(function (StudentTask $task, int $index): void {
            $task->forceFill(['sort_order' => $index])->save();
        });
    }

    #[Computed]
    public function columns(): array
    {
        $tasks = StudentTask::query()
            ->with('assignedBy')
            ->where('student_id', Auth::id())
            ->orderBy('sort_order')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('created_at')
            ->get()
            ->groupBy('status');

        return collect(StudentTask::statusLabels())
            ->map(function (string $label, string $status) use ($tasks): array {
                return [
                    'status' => $status,
                    'title' => $label,
                    'tasks' => $tasks->get($status, collect()),
                ];
            })
            ->values()
            ->all();
    }
};
