<?php

use App\Models\AttendanceLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Reports / Submissions')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Professor Workspace', 'href' => '#'],
        ['label' => 'Reports / Submissions'],
    ];

    public string $title = 'Reports / Submissions';

    public string $description = 'Read completed student accomplishment submissions and photos.';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->submissions);
    }

    #[Computed]
    public function submissions(): LengthAwarePaginator
    {
        return AttendanceLog::query()
            ->with(['student.studentProfile', 'submission'])
            ->whereHas('assignment', fn (Builder $query) => $query->where('professor_id', Auth::id()))
            ->whereNotNull('afternoon_time_out')
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->whereHas('student', function (Builder $student) use ($search) {
                    $student->where('name', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhereHas('studentProfile', fn (Builder $profile) => $profile->where('student_id', 'like', $search));
                });
            })
            ->latest('attendance_date')
            ->paginate(10);
    }
};
