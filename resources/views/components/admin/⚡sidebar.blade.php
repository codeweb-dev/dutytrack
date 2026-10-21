<?php

use App\Enums\Role as EnumsRole;
use App\Models\Announcement;
use App\Models\AttendanceLog;
use App\Models\AuditLog;
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

<flux:sidebar.nav data-tour="main-navigation">
    <flux:sidebar.item icon="home" data-tour="nav-dashboard" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="user-group" heading="User Management" data-tour="nav-group-user-management">
        <flux:sidebar.item icon="academic-cap" data-tour="nav-students" href="{{ route('admin.students') }}" :current="request()->routeIs('admin.students')" wire:navigate>
            Students
        </flux:sidebar.item>

        <flux:sidebar.item icon="users" data-tour="nav-coordinators" href="{{ route('admin.coordinators') }}" :current="request()->routeIs('admin.coordinators')" wire:navigate>
            OJT Coordinators
        </flux:sidebar.item>

        <flux:sidebar.item icon="building-office-2" data-tour="nav-supervisors" href="{{ route('admin.supervisors') }}" :current="request()->routeIs('admin.supervisors')" wire:navigate>
            Supervisors
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring" data-tour="nav-group-monitoring">
        <flux:sidebar.item icon="clipboard-document-list" data-tour="nav-attendance" href="{{ route('admin.attendance') }}" :current="request()->routeIs('admin.attendance')" wire:navigate>
            Attendance
        </flux:sidebar.item>

        <flux:sidebar.item icon="briefcase" data-tour="nav-assignments" href="{{ route('admin.ojt-assignments') }}" :current="request()->routeIs('admin.ojt-assignments')" wire:navigate>
            OJT Assignments
        </flux:sidebar.item>

        <flux:sidebar.item icon="check-badge" data-tour="nav-evaluations" href="{{ route('admin.evaluations') }}" :current="request()->routeIs('admin.evaluations')" wire:navigate>
            Evaluation
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="shield-check" heading="System Management" data-tour="nav-group-system">
        <flux:sidebar.item icon="megaphone" data-tour="nav-admin-announcements" href="{{ route('admin.announcements') }}" :current="request()->routeIs('admin.announcements')" wire:navigate>
            Announcements
        </flux:sidebar.item>

        <flux:sidebar.item icon="document-text" data-tour="nav-audit-logs" href="{{ route('admin.audit-logs') }}" :current="request()->routeIs('admin.audit-logs')" wire:navigate>
            Audit Logs
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
