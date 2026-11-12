<?php

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\OjtAssignment;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Archive')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'System Management', 'href' => '#'],
        ['label' => 'Archive'],
    ];

    public string $title = 'Archive';

    public string $description = 'Restore archived accounts and records.';

    public string $type = 'users';

    public string $search = '';

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    protected function archivedUsersQuery(): Builder
    {
        return User::onlyTrashed()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                Role::OJTCOORDINATOR->value,
                Role::SUPERVISOR->value,
                Role::STUDENT->value,
            ]))
            ->where('coordinator_id', Auth::id());
    }

    protected function archivedAssignmentsQuery(): Builder
    {
        return OjtAssignment::onlyTrashed()
            ->whereHas('student', fn (Builder $query) => $query->withTrashed()->where('coordinator_id', Auth::id()));
    }

    protected function searchTerm(): string
    {
        return '%'.$this->search.'%';
    }

    #[Computed]
    public function summary(): array
    {
        return [
            'users' => $this->archivedUsersQuery()->count(),
            'assignments' => $this->archivedAssignmentsQuery()->count(),
            'announcements' => Announcement::onlyTrashed()->count(),
            'logs' => AuditLog::onlyTrashed()->visibleTo(Auth::id())->count(),
        ];
    }

    #[Computed]
    public function archivedUsers(): LengthAwarePaginator
    {
        return $this->archivedUsersQuery()
            ->with(['roles', 'studentProfile', 'supervisorProfile'])
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', $this->searchTerm())
                ->orWhere('first_name', 'like', $this->searchTerm())
                ->orWhere('last_name', 'like', $this->searchTerm())
                ->orWhere('email', 'like', $this->searchTerm())))
            ->orderByRaw("coalesce(nullif(last_name, ''), name)")
            ->orderBy('first_name')
            ->paginate(10);
    }

    #[Computed]
    public function archivedAnnouncements(): LengthAwarePaginator
    {
        return Announcement::onlyTrashed()
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title', 'like', $this->searchTerm())
                ->orWhere('message', 'like', $this->searchTerm())))
            ->latest('deleted_at')
            ->paginate(10);
    }

    #[Computed]
    public function archivedAssignments(): LengthAwarePaginator
    {
        $matchesName = fn (Builder $query) => $query->withTrashed()->where(fn (Builder $query) => $query
            ->where('name', 'like', $this->searchTerm())
            ->orWhere('first_name', 'like', $this->searchTerm())
            ->orWhere('last_name', 'like', $this->searchTerm()));

        return $this->archivedAssignmentsQuery()
            ->with([
                'student' => fn ($query) => $query->withTrashed()->with('studentProfile'),
                'companySupervisor' => fn ($query) => $query->withTrashed(),
            ])
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereHas('student', $matchesName)
                ->orWhereHas('companySupervisor', $matchesName)))
            ->orderBy(User::withTrashed()->selectRaw("coalesce(nullif(last_name, ''), name)")->whereColumn('users.id', 'ojt_assignments.student_id'))
            ->orderBy(User::withTrashed()->select('first_name')->whereColumn('users.id', 'ojt_assignments.student_id'))
            ->paginate(10);
    }

    #[Computed]
    public function archivedAuditLogs(): LengthAwarePaginator
    {
        return AuditLog::onlyTrashed()->visibleTo(Auth::id())
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('actor_name', 'like', $this->searchTerm())
                ->orWhere('module', 'like', $this->searchTerm())
                ->orWhere('action', 'like', $this->searchTerm())
                ->orWhere('target_label', 'like', $this->searchTerm())))
            ->latest('deleted_at')
            ->paginate(10);
    }

    public function restoreUser(int $userId): void
    {
        $user = $this->archivedUsersQuery()->findOrFail($userId);

        $user->forceFill(['rejection_reason' => null])->restore();
        unset($this->archivedUsers, $this->summary);

        Flux::toast(variant: 'success', heading: 'Account restored.', text: 'The account is active again.');
    }

    public function restoreAnnouncement(int $announcementId): void
    {
        Announcement::onlyTrashed()->findOrFail($announcementId)->restore();
        unset($this->archivedAnnouncements, $this->summary);

        Flux::toast(variant: 'success', text: 'Announcement restored.');
    }

    public function restoreAssignment(int $assignmentId): void
    {
        $assignment = $this->archivedAssignmentsQuery()->findOrFail($assignmentId);

        if (OjtAssignment::query()->where('student_id', $assignment->student_id)->exists()) {
            Flux::toast(variant: 'warning', heading: 'Student already assigned.', text: 'Archive the active assignment before restoring this one.');

            return;
        }

        // An archived student or supervisor would bring back a trainee nobody can work with.
        if (User::query()->active()->whereKey([$assignment->student_id, $assignment->company_supervisor_id])->count() !== 2) {
            Flux::toast(variant: 'warning', heading: 'Cannot restore yet.', text: 'The student or supervisor is archived or inactive. Restore them first, or create a new assignment.');

            return;
        }

        $assignment->restore();
        unset($this->archivedAssignments, $this->summary);

        Flux::toast(variant: 'success', text: 'Assignment restored.');
    }

    public function restoreAuditLog(int $logId): void
    {
        AuditLog::onlyTrashed()->visibleTo(Auth::id())->findOrFail($logId)->restore();
        unset($this->archivedAuditLogs, $this->summary);

        Flux::toast(variant: 'success', text: 'Audit log restored.');
    }
};
