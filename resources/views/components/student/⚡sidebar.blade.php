<?php

use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    protected function studentId(): int
    {
        return (int) Auth::id();
    }

    #[Computed]
    public function assignmentsCount(): int
    {
        return OjtAssignment::query()->where('student_id', $this->studentId())->count();
    }

    #[Computed]
    public function attendanceCount(): int
    {
        return AttendanceLog::query()->where('student_id', $this->studentId())->count();
    }

    #[Computed]
    public function dutyHoursCount(): int
    {
        return DutyHourLog::query()->where('student_id', $this->studentId())->count();
    }

    #[Computed]
    public function submissionsCount(): int
    {
        return DutyHourLog::query()->where('student_id', $this->studentId())->where('status', 'pending')->count();
    }

    #[Computed]
    public function evaluationsCount(): int
    {
        return Evaluation::query()->where('student_id', $this->studentId())->count();
    }
};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="briefcase" :badge="$this->assignmentsCount" href="#">
        My Assignments
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-list" :badge="$this->attendanceCount" :href="route('student.attendance')" :current="request()->routeIs('student.attendance')"
        wire:navigate>
        Attendance
    </flux:sidebar.item>

    <flux:sidebar.item icon="clock" :badge="$this->dutyHoursCount" href="#">
        Duty Hours
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" :badge="$this->submissionsCount" href="#">
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="check-badge" :badge="$this->evaluationsCount" href="#">
        Evaluation
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="squares-2x2" heading="Productivity">
        <flux:sidebar.item icon="rectangle-stack" href="{{ route('student.kanban') }}" :current="request()->routeIs('student.kanban')"
            wire:navigate>
            Tasks
        </flux:sidebar.item>
        <flux:sidebar.item icon="bolt" href="{{ route('student.pomodoro') }}" :current="request()->routeIs('student.pomodoro')"
            wire:navigate>
            Pomodoro Timer
        </flux:sidebar.item>
        <flux:sidebar.item icon="fire" href="{{ route('student.pet-streak') }}" :current="request()->routeIs('student.pet-streak')"
            wire:navigate>
            Pet Streak
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
