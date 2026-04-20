<?php

use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    protected function coordinatorId(): int
    {
        return (int) Auth::id();
    }

    #[Computed]
    public function managedStudentsCount(): int
    {
        return User::query()->where('coordinator_id', $this->coordinatorId())->count();
    }

    #[Computed]
    public function managedAssignmentsCount(): int
    {
        return OjtAssignment::query()
            ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', $this->coordinatorId()))
            ->count();
    }

    #[Computed]
    public function managedAttendanceCount(): int
    {
        return AttendanceLog::query()
            ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', $this->coordinatorId()))
            ->count();
    }

    #[Computed]
    public function managedDutyHoursCount(): int
    {
        return DutyHourLog::query()
            ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', $this->coordinatorId()))
            ->count();
    }

    #[Computed]
    public function managedEvaluationsCount(): int
    {
        return Evaluation::query()
            ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', $this->coordinatorId()))
            ->count();
    }

    #[Computed]
    public function managedReportsCount(): int
    {
        return DutyHourLog::query()
            ->where('status', 'pending')
            ->whereHas('student', fn (Builder $query) => $query->where('coordinator_id', $this->coordinatorId()))
            ->count();
    }
};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" :href="route('coordinator.student-management')" :badge="$this->managedStudentsCount"
        :current="request()->routeIs('coordinator.student-management')"
        wire:navigate>
        Student Management
    </flux:sidebar.item>

    <flux:sidebar.item icon="briefcase" :href="route('coordinator.ojt-assignments')" :badge="$this->managedAssignmentsCount"
        :current="request()->routeIs('coordinator.ojt-assignments')"
        wire:navigate>
        OJT Assignments
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring">
        <flux:sidebar.item icon="clipboard-document-list" :badge="$this->managedAttendanceCount" href="#">
            Attendance
        </flux:sidebar.item>
        <flux:sidebar.item icon="clock" :badge="$this->managedDutyHoursCount" href="#">
            Duty Hours
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="check-badge" heading="Reviews">
        <flux:sidebar.item icon="hand-thumb-up" :badge="$this->managedEvaluationsCount" href="#">
            Exaltations
        </flux:sidebar.item>
        <flux:sidebar.item icon="document-text" :badge="$this->managedReportsCount" href="#">
            Reports
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
