<?php

use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('OJT Assignment Monitoring')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'OJT Assignment Monitoring'],
    ];

    public string $title = 'OJT Assignment Monitoring';

    public string $description = 'Monitor student OJT assignments, assigned supervisors, deployment type, and assignment status.';

    public string $search = '';

    public string $ojt_type = '';

    public string $status = '';

    public string $supervisor_id = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->assignments);
    }

    public function updatedOjtType(): void
    {
        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function updatedSupervisorId(): void
    {
        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->ojt_type = '';
        $this->status = '';
        $this->supervisor_id = '';

        $this->resetPage();
        unset($this->assignments, $this->summary);
    }

    public function badgeColor(string $status): string
    {
        return match ($status) {
            'active' => 'lime',
            'pending' => 'amber',
            'completed' => 'sky',
            'cancelled' => 'red',
            default => 'zinc',
        };
    }

    #[Computed]
    public function assignments(): LengthAwarePaginator
    {
        return OjtAssignment::query()
            ->with([
                'student.studentProfile',
                'companySupervisor',
            ])
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where(function ($nested) use ($search) {
                        $nested->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    })->orWhereHas('studentProfile', function ($profileQuery) use ($search) {
                        $profileQuery->where('student_id', 'like', $search);
                    });
                });
            })
            ->when($this->ojt_type !== '', fn ($query) => $query->where('ojt_type', $this->ojt_type))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->supervisor_id !== '', function ($query) {
                $query->where('company_supervisor_id', $this->supervisor_id);
            })
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function summary(): array
    {
        $query = OjtAssignment::query()
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where(function ($nested) use ($search) {
                        $nested->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    })->orWhereHas('studentProfile', function ($profileQuery) use ($search) {
                        $profileQuery->where('student_id', 'like', $search);
                    });
                });
            })
            ->when($this->ojt_type !== '', fn ($query) => $query->where('ojt_type', $this->ojt_type))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->supervisor_id !== '', function ($query) {
                $query->where(function ($nested) {
                    $nested->where('company_supervisor_id', $this->supervisor_id);
                });
            });

        return [
            'total' => (clone $query)->count(),
            'internal' => (clone $query)->where('ojt_type', 'internal')->count(),
            'external' => (clone $query)->where('ojt_type', 'external')->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];
    }

    #[Computed]
    public function supervisors()
    {
        return User::query()
            ->role('supervisor')
            ->orderBy('first_name')
            ->get();
    }
};
