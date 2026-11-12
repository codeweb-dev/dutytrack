<?php

use App\Models\ChatMessage;
use App\Models\OjtAssignment;
use App\Models\StudentTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    // Counts of items waiting on the supervisor.
    #[Computed]
    public function badges(): array
    {
        return array_map(fn (int $count) => $count ?: null, [
            // Supervisors chat with trainees as the participant and with their coordinator as the "student" side.
            'messages' => ChatMessage::query()
                ->whereHas('conversation', fn (Builder $query) => $query
                    ->where(fn (Builder $query) => $query->where('participant_id', Auth::id())->where('participant_role', 'supervisor'))
                    ->orWhere(fn (Builder $query) => $query->where('student_id', Auth::id())->where('participant_role', 'coordinator')))
                ->where('sender_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->count(),
            'tasks' => StudentTask::query()
                ->where('assigned_by', Auth::id())
                ->where('status', 'in_review')
                ->whereHas('assignment', fn (Builder $query) => $query->where('company_supervisor_id', Auth::id()))
                ->count(),
            // Only trainees who finished their required hours, since the rest can't be evaluated yet.
            'evaluations' => OjtAssignment::query()
                ->where('company_supervisor_id', Auth::id())
                ->whereRaw('(select coalesce(max(ojt_required_hours), 0) from student_profiles where student_profiles.user_id = ojt_assignments.student_id) > 0')
                ->whereRaw('(select coalesce(sum(hours_rendered), 0) from attendance_logs where attendance_logs.student_id = ojt_assignments.student_id)
                    >= (select coalesce(max(ojt_required_hours), 0) from student_profiles where student_profiles.user_id = ojt_assignments.student_id)')
                ->whereDoesntHave('student.evaluations', fn (Builder $query) => $query->where('evaluator_type', 'company_supervisor'))
                ->count(),
        ]);
    }
};
?>

<flux:sidebar.nav data-tour="main-navigation">
    <flux:sidebar.item icon="home" data-tour="nav-dashboard" :href="route('supervisor.dashboard')" :current="request()->routeIs('supervisor.dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" data-tour="nav-assigned-students" href="{{ route('supervisor.assigned-students') }}"
        :current="request()->routeIs('supervisor.assigned-students')" wire:navigate>
        Assigned Students
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" data-tour="nav-messages" href="{{ route('supervisor.messages') }}" :badge="$this->badges['messages']" badgeColor="red"
        :current="request()->routeIs('supervisor.messages')" wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="chart-bar-square" heading="Monitoring" data-tour="nav-group-monitoring">
        <flux:sidebar.item icon="calendar-days" data-tour="nav-attendance" href="{{ route('supervisor.attendance') }}"
            :current="request()->routeIs('supervisor.attendance')" wire:navigate>
            Attendance
        </flux:sidebar.item>

        <flux:sidebar.item icon="rectangle-stack" data-tour="nav-tasks" href="{{ route('supervisor.tasks') }}" :badge="$this->badges['tasks']" badgeColor="red"
            :current="request()->routeIs('supervisor.tasks')" wire:navigate>
            Tasks
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="document-magnifying-glass" heading="Reviews" data-tour="nav-group-reviews">
        <flux:sidebar.item icon="document-text" data-tour="nav-reports" href="{{ route('supervisor.reports-submissions') }}"
            :current="request()->routeIs('supervisor.reports-submissions')" wire:navigate>
            Accomplishment Reports
        </flux:sidebar.item>

        <flux:sidebar.item icon="check-badge" data-tour="nav-evaluations" href="{{ route('supervisor.evaluations') }}" :badge="$this->badges['evaluations']" badgeColor="red"
            :current="request()->routeIs('supervisor.evaluations')" wire:navigate>
            Evaluations
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
