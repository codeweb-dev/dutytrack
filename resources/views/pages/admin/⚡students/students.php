<?php

use App\Enums\Role as EnumsRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Students')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'User Management', 'href' => '#'],
        ['label' => 'Students'],
    ];

    public string $title = 'Students';
    public string $description = 'Manage student records, monitor progress, and generate reports.';

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $search = '';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
        unset($this->students);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    #[On('student-saved')]
    public function refreshStudents(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    #[Computed]
    public function students(): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['studentProfile', 'coordinator', 'professorProfile'])
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', EnumsRole::STUDENT->value);
            })
            ->leftJoin('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->select('users.*');

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('users.name', 'like', $search)
                    ->orWhere('users.first_name', 'like', $search)
                    ->orWhere('users.last_name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('users.contact_number', 'like', $search)
                    ->orWhereHas('coordinator', function (Builder $query) use ($search) {
                        $query->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    })
                    ->orWhere('student_profiles.student_id', 'like', $search)
                    ->orWhere('student_profiles.course', 'like', $search)
                    ->orWhere('student_profiles.year_level', 'like', $search)
                    ->orWhere('student_profiles.section', 'like', $search)
                    ->orWhere('student_profiles.ojt_required_hours', 'like', $search);
            });
        }

        $allowedSorts = [
            'name' => 'users.name',
            'student_id' => 'student_profiles.student_id',
            'course' => 'student_profiles.course',
            'ojt_required_hours' => 'student_profiles.ojt_required_hours',
        ];

        $sortColumn = $allowedSorts[$this->sortBy] ?? 'users.name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(10);
    }
};
