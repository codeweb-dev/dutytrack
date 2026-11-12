<?php

use App\Enums\Role;
use App\Models\ChatMessage;
use App\Models\Evaluation;
use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    // Each count mirrors the matching page's summary card so the badge and the card agree.
    #[Computed]
    public function badges(): array
    {
        $students = fn() => User::query()->whereHas('roles', fn(Builder $query) => $query->where('name', Role::STUDENT->value))->where('coordinator_id', Auth::id());

        return array_map(fn(int $count) => $count ?: null, [
            'messages' => ChatMessage::query()->whereHas('conversation', fn(Builder $query) => $query->where('participant_id', Auth::id())->where('participant_role', 'coordinator'))->where('sender_id', '!=', Auth::id())->whereNull('read_at')->count(),
            'students' => $students()->where('status', 'pending')->count(),
            'requirements' => $students()->whereHas('studentRequirements', fn(Builder $query) => $query->whereIn('key', StudentRequirement::activeKeys())->where('is_completed', false)->whereNotNull('submitted_at'))->count(),
            'evaluations' => Evaluation::query()->where('evaluator_type', 'company_supervisor')->where('status', 'submitted')->whereHas('student', fn(Builder $query) => $query->where('coordinator_id', Auth::id()))->count(),
        ]);
    }
};
?>

<flux:sidebar.nav data-tour="main-navigation">
    <flux:sidebar.item icon="home" data-tour="nav-dashboard" :href="route('coordinator.dashboard')"
        :current="request()->routeIs('coordinator.dashboard')" wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" data-tour="nav-messages" :href="route('coordinator.messages')"
        :badge="$this->badges['messages']" badgeColor="red" :current="request()->routeIs('coordinator.messages')" wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="users" heading="People" data-tour="nav-group-people">
        <flux:sidebar.item icon="academic-cap" data-tour="nav-student-management"
            :href="route('coordinator.student-management')" :badge="$this->badges['students']" badgeColor="red"
            :current="request()->routeIs('coordinator.student-management')" wire:navigate>
            Student Management
        </flux:sidebar.item>

        <flux:sidebar.item icon="building-office-2" data-tour="nav-supervisor-management"
            :href="route('coordinator.supervisor-management')"
            :current="request()->routeIs('coordinator.supervisor-management')" wire:navigate>
            Supervisor Management
        </flux:sidebar.item>

        <flux:sidebar.item icon="briefcase" data-tour="nav-assignments" :href="route('coordinator.ojt-assignments')"
            :current="request()->routeIs('coordinator.ojt-assignments')" wire:navigate>
            OJT Assignments
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring" data-tour="nav-group-monitoring">
        <flux:sidebar.item icon="clipboard-document-check" data-tour="nav-requirements"
            href="{{ route('coordinator.requirements') }}" :badge="$this->badges['requirements']" badgeColor="red"
            :current="request()->routeIs('coordinator.requirements')" wire:navigate>
            Requirements
        </flux:sidebar.item>

        <flux:sidebar.item icon="calendar-days" data-tour="nav-attendance" href="{{ route('coordinator.attendance') }}"
            :current="request()->routeIs('coordinator.attendance')" wire:navigate>
            Attendance
        </flux:sidebar.item>

        <flux:sidebar.item icon="check-badge" data-tour="nav-evaluations" href="{{ route('coordinator.evaluations') }}"
            :badge="$this->badges['evaluations']" badgeColor="red" :current="request()->routeIs('coordinator.evaluations*')"
            wire:navigate>
            Evaluations
        </flux:sidebar.item>

        <flux:sidebar.item icon="trophy" data-tour="nav-certificates" href="{{ route('coordinator.certificates') }}"
            :current="request()->routeIs('coordinator.certificates')" wire:navigate>
            Certificates
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="shield-check" heading="System Management" data-tour="nav-group-system">
        <flux:sidebar.item icon="megaphone" data-tour="nav-announcements"
            href="{{ route('coordinator.announcements') }}" :current="request()->routeIs('coordinator.announcements')"
            wire:navigate>
            Announcements
        </flux:sidebar.item>

        <flux:sidebar.item icon="queue-list" data-tour="nav-audit-logs" href="{{ route('coordinator.audit-logs') }}"
            :current="request()->routeIs('coordinator.audit-logs')" wire:navigate>
            Audit Logs
        </flux:sidebar.item>

        <flux:sidebar.item icon="archive-box" href="{{ route('coordinator.archive') }}"
            :current="request()->routeIs('coordinator.archive')" wire:navigate>
            Archive
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
