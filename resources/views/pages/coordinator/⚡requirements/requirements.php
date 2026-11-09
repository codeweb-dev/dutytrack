<?php

use App\Livewire\Concerns\WithSorting;
use App\Enums\Role as EnumsRole;
use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Requirement Monitoring')] class extends Component
{
    use WithPagination;
    use WithSorting;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Requirements'],
    ];

    public string $title = 'Requirement Monitoring';

    public string $description = 'Track OJT orientation, medical, and form requirements for your assigned students.';

    public string $search = '';

    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->students, $this->summary);
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
        unset($this->students);
    }

    #[On('student-saved')]
    public function refreshRequirements(): void
    {
        $this->resetPage();
        unset($this->students, $this->summary);
    }

    protected function managedStudentQuery(): Builder
    {
        return User::query()
            ->with(['studentProfile', 'studentRequirements'])
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', EnumsRole::STUDENT->value);
            })
            ->where('users.coordinator_id', Auth::id())
            ->leftJoin('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->select('users.*');
    }

    protected function ensureVisibleRequirements($students): void
    {
        $students->each(function (User $student): void {
            $student->setRelation('studentRequirements', StudentRequirement::ensureForStudent($student));
        });
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'sortBy', 'sortDirection');

        $this->resetPage();
        unset($this->students);
    }

    #[Computed]
    public function students(): LengthAwarePaginator
    {
        $query = $this->managedStudentQuery();

        if ($this->search !== '') {
            $search = '%'.$this->search.'%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('users.name', 'like', $search)
                    ->orWhere('users.first_name', 'like', $search)
                    ->orWhere('users.last_name', 'like', $search)
                    ->orWhere('users.email', 'like', $search)
                    ->orWhere('student_profiles.student_id', 'like', $search)
                    ->orWhere('student_profiles.course', 'like', $search);
            });
        }

        $active = fn (Builder $query) => $query->whereIn('key', StudentRequirement::activeKeys());
        $incomplete = fn (Builder $query) => $active($query)->where('is_completed', false);

        match ($this->status) {
            'complete' => $query->whereHas('studentRequirements', $active)->whereDoesntHave('studentRequirements', $incomplete),
            'pending' => $query->where(fn (Builder $query) => $query
                ->whereDoesntHave('studentRequirements', $active)
                ->orWhereHas('studentRequirements', $incomplete)),
            'for_checking' => $query->whereHas('studentRequirements', fn (Builder $query) => $incomplete($query)->whereNotNull('submitted_at')),
            default => null,
        };

        $students = $this->applySorting($query, [
            '' => 'users.last_name',
            'name' => 'users.last_name',
            'student_id' => 'student_profiles.student_id',
            'course' => 'student_profiles.course',
        ])->orderBy('users.name')->paginate(10);

        $this->ensureVisibleRequirements($students->getCollection());

        return $students;
    }

    #[Computed]
    public function summary(): array
    {
        $students = $this->managedStudentQuery()->get();

        $this->ensureVisibleRequirements($students);

        $totalStudents = $students->count();
        $completeStudents = $students->filter(function (User $student): bool {
            return $student->studentRequirements->isNotEmpty()
                && $student->studentRequirements->every(
                    fn (StudentRequirement $requirement): bool => $requirement->is_completed
                );
        })->count();

        // Counts students (not files) so the card matches the rows its filter shows.
        $forChecking = $students->filter(function (User $student): bool {
            return $student->studentRequirements
                ->where('is_completed', false)
                ->whereNotNull('submitted_at')
                ->isNotEmpty();
        })->count();

        return [
            'students' => $totalStudents,
            'complete' => $completeStudents,
            'pending' => max($totalStudents - $completeStudents, 0),
            'for_checking' => $forChecking,
        ];
    }
};
