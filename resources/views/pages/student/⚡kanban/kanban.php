<?php

use App\Models\StudentTask;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
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

    public string $description = 'Track tasks assigned by your professor and update their progress as you work.';

    public function priorityColor(?string $priority): string
    {
        return match ($priority) {
            'high' => 'red',
            'low' => 'sky',
            default => 'zinc',
        };
    }

    public function statusColor(?string $status): string
    {
        return match ($status) {
            'done' => 'green',
            'in_review' => 'blue',
            'in_progress' => 'amber',
            default => 'zinc',
        };
    }

    public function displayColumns(): array
    {
        $columns = collect($this->columns)->keyBy('status');

        return collect(['pending', 'in_progress', 'done', 'in_review'])
            ->map(fn (string $status): array => $columns->get($status))
            ->filter()
            ->values()
            ->all();
    }

    public function updateTaskStatus(int $taskId, string $status): void
    {
        abort_unless(array_key_exists($status, StudentTask::statusLabels()), 422);

        $task = StudentTask::query()
            ->where('student_id', Auth::id())
            ->findOrFail($taskId);

        $task->update([
            'status' => $status,
            'completed_at' => $status === 'done' ? now() : null,
        ]);

        unset($this->columns);

        Flux::toast(
            variant: 'success',
            heading: 'Task updated.',
            text: $task->title.' is now '.StudentTask::statusLabels()[$status].'.'
        );
    }

    #[Computed]
    public function columns(): array
    {
        $tasks = StudentTask::query()
            ->with('assignedBy')
            ->where('student_id', Auth::id())
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
