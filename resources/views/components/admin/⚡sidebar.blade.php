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
};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="user-group" heading="User Management">
        <flux:sidebar.item icon="academic-cap" href="{{ route('admin.students') }}" :current="request()->routeIs('admin.students')" wire:navigate>
            Students
        </flux:sidebar.item>

        <flux:sidebar.item icon="users" href="{{ route('admin.coordinators') }}" :current="request()->routeIs('admin.coordinators')" wire:navigate>
            OJT Coordinators
        </flux:sidebar.item>

        <flux:sidebar.item icon="user-circle" href="{{ route('admin.professors') }}" :current="request()->routeIs('admin.professors')" wire:navigate>
            Professors
        </flux:sidebar.item>

        <flux:sidebar.item icon="building-office-2" href="{{ route('admin.supervisors') }}" :current="request()->routeIs('admin.supervisors')" wire:navigate>
            Company Supervisors
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring">
        <flux:sidebar.item icon="clipboard-document-list" href="{{ route('admin.attendance') }}" :current="request()->routeIs('admin.attendance')" wire:navigate>
            Attendance
        </flux:sidebar.item>

        <flux:sidebar.item icon="clock" href="{{ route('admin.duty-hours') }}" :current="request()->routeIs('admin.duty-hours')" wire:navigate>
            Duty Hours
        </flux:sidebar.item>

        <flux:sidebar.item icon="briefcase" href="{{ route('admin.ojt-assignments') }}" :current="request()->routeIs('admin.ojt-assignments')" wire:navigate>
            OJT Assignments
        </flux:sidebar.item>

        <flux:sidebar.item icon="check-badge" href="{{ route('admin.evaluations') }}" :current="request()->routeIs('admin.evaluations')" wire:navigate>
            Evaluation
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="shield-check" heading="System Management">
        <flux:sidebar.item icon="megaphone" href="{{ route('admin.announcements') }}" :current="request()->routeIs('admin.announcements')" wire:navigate>
            Announcements
        </flux:sidebar.item>

        <flux:sidebar.item icon="document-text" href="{{ route('admin.audit-logs') }}" :current="request()->routeIs('admin.audit-logs')" wire:navigate>
            Audit Logs
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
