<?php

use App\Enums\Role as EnumsRole;
use App\Models\Announcement;
use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\DutyHourLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function studentsCount(): int
    {
        return User::query()
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', EnumsRole::STUDENT->value);
            })
            ->count();
    }

    #[Computed]
    public function coordinatorsCount(): int
    {
        return User::query()
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', EnumsRole::OJTCOORDINATOR->value);
            })
            ->count();
    }

    #[Computed]
    public function professorsCount(): int
    {
        return User::query()
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', EnumsRole::PROFESSOR->value);
            })
            ->count();
    }

    #[Computed]
    public function supervisorsCount(): int
    {
        return User::query()
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', EnumsRole::COMPANYSUPERVISOR->value);
            })
            ->count();
    }

    #[Computed]
    public function announcementsCount(): int
    {
        return Announcement::query()->count();
    }

    #[Computed]
    public function auditLogsCount(): int
    {
        return AuditLog::query()->count();
    }

    #[Computed]
    public function attendanceCount(): int
    {
        return AttendanceLog::query()->count();
    }

    #[Computed]
    public function dutyHoursCount(): int
    {
        return DutyHourLog::query()->count();
    }

    #[Computed]
    public function ojtAssignmentsCount(): int
    {
        return OjtAssignment::query()->count();
    }

    #[Computed]
    public function evaluationsCount(): int
    {
        return Evaluation::query()->count();
    }
};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="user-group" heading="User Management">
        <flux:sidebar.item icon="academic-cap" href="{{ route('admin.students') }}" :badge="$this->studentsCount"
            :current="request()->routeIs('admin.students')" wire:navigate>
            Students
        </flux:sidebar.item>

        <flux:sidebar.item icon="users" href="{{ route('admin.coordinators') }}" :badge="$this->coordinatorsCount"
            :current="request()->routeIs('admin.coordinators')" wire:navigate>
            OJT Coordinators
        </flux:sidebar.item>

        <flux:sidebar.item icon="user-circle" href="{{ route('admin.professors') }}" :badge="$this->professorsCount"
            :current="request()->routeIs('admin.professors')" wire:navigate>
            Professors
        </flux:sidebar.item>

        <flux:sidebar.item icon="building-office-2" href="{{ route('admin.supervisors') }}" :badge="$this->supervisorsCount"
            :current="request()->routeIs('admin.supervisors')" wire:navigate>
            Company Supervisors
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring">
        <flux:sidebar.item icon="clipboard-document-list" href="{{ route('admin.attendance') }}" :badge="$this->attendanceCount"
            :current="request()->routeIs('admin.attendance')" wire:navigate>
            Attendance
        </flux:sidebar.item>

        <flux:sidebar.item icon="clock" href="{{ route('admin.duty-hours') }}" :badge="$this->dutyHoursCount"
            :current="request()->routeIs('admin.duty-hours')" wire:navigate>
            Duty Hours
        </flux:sidebar.item>

        <flux:sidebar.item icon="briefcase" href="{{ route('admin.ojt-assignments') }}" :badge="$this->ojtAssignmentsCount"
            :current="request()->routeIs('admin.ojt-assignments')" wire:navigate>
            OJT Assignments
        </flux:sidebar.item>

        <flux:sidebar.item icon="check-badge" href="{{ route('admin.evaluations') }}" :badge="$this->evaluationsCount"
            :current="request()->routeIs('admin.evaluations')" wire:navigate>
            Evaluation
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="shield-check" heading="System Management">
        <flux:sidebar.item icon="megaphone" href="{{ route('admin.announcements') }}" :badge="$this->announcementsCount"
            :current="request()->routeIs('admin.announcements')" wire:navigate>
            Announcements
        </flux:sidebar.item>

        <flux:sidebar.item icon="document-text" href="{{ route('admin.audit-logs') }}" :badge="$this->auditLogsCount"
            :current="request()->routeIs('admin.audit-logs')" wire:navigate>
            Audit Logs
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
