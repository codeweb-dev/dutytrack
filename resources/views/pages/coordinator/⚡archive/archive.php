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

    #[Computed]
    public function archivedUsers(): LengthAwarePaginator
    {
        return User::onlyTrashed()
            ->with(['roles', 'studentProfile', 'supervisorProfile'])
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                Role::OJTCOORDINATOR->value,
                Role::SUPERVISOR->value,
                Role::STUDENT->value,
            ]))
            ->where(fn (Builder $query) => $query
                ->where('coordinator_id', Auth::id())
                ->orWhereHas('roles', fn (Builder $roles) => $roles->where('name', Role::OJTCOORDINATOR->value)))
            ->latest('deleted_at')
            ->paginate(10, pageName: 'usersPage');
    }

    #[Computed]
    public function archivedAnnouncements(): LengthAwarePaginator
    {
        return Announcement::onlyTrashed()->latest('deleted_at')->paginate(10, pageName: 'announcementsPage');
    }

    #[Computed]
    public function archivedAssignments(): LengthAwarePaginator
    {
        return OjtAssignment::onlyTrashed()
            ->with([
                'student' => fn (Builder $query) => $query->withTrashed()->with('studentProfile'),
                'companySupervisor' => fn (Builder $query) => $query->withTrashed(),
            ])
            ->whereHas('student', fn (Builder $query) => $query->withTrashed()->where('coordinator_id', Auth::id()))
            ->latest('deleted_at')
            ->paginate(10, pageName: 'assignmentsPage');
    }

    #[Computed]
    public function archivedAuditLogs(): LengthAwarePaginator
    {
        return AuditLog::onlyTrashed()->latest('deleted_at')->paginate(10, pageName: 'logsPage');
    }

    public function restoreUser(int $userId): void
    {
        $user = User::onlyTrashed()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                Role::OJTCOORDINATOR->value,
                Role::SUPERVISOR->value,
                Role::STUDENT->value,
            ]))
            ->where(fn (Builder $query) => $query
                ->where('coordinator_id', Auth::id())
                ->orWhereHas('roles', fn (Builder $roles) => $roles->where('name', Role::OJTCOORDINATOR->value)))
            ->findOrFail($userId);

        $user->restore();
        unset($this->archivedUsers);

        Flux::toast(variant: 'success', heading: 'Account restored.', text: 'The account is active again.');
    }

    public function restoreAnnouncement(int $announcementId): void
    {
        Announcement::onlyTrashed()->findOrFail($announcementId)->restore();
        unset($this->archivedAnnouncements);

        Flux::toast(variant: 'success', text: 'Announcement restored.');
    }

    public function restoreAssignment(int $assignmentId): void
    {
        $assignment = OjtAssignment::onlyTrashed()
            ->whereHas('student', fn (Builder $query) => $query->withTrashed()->where('coordinator_id', Auth::id()))
            ->findOrFail($assignmentId);

        if (OjtAssignment::query()->where('student_id', $assignment->student_id)->exists()) {
            Flux::toast(variant: 'warning', heading: 'Student already assigned.', text: 'Archive the active assignment before restoring this one.');

            return;
        }

        $assignment->restore();
        unset($this->archivedAssignments);

        Flux::toast(variant: 'success', text: 'Assignment restored.');
    }

    public function restoreAuditLog(int $logId): void
    {
        AuditLog::onlyTrashed()->findOrFail($logId)->restore();
        unset($this->archivedAuditLogs);

        Flux::toast(variant: 'success', text: 'Audit log restored.');
    }
};
