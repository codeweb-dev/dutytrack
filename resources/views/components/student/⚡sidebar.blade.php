<?php

use App\Models\ChatMessage;
use App\Models\StudentRequirement;
use App\Models\StudentTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    // Each count mirrors the matching page's summary card so the badge and the card agree.
    #[Computed]
    public function badges(): array
    {
        // Counted from the definitions so requirement rows that haven't been created yet still count as missing.
        $activeKeys = StudentRequirement::activeKeys();
        $started = StudentRequirement::query()
            ->where('student_id', Auth::id())
            ->whereIn('key', $activeKeys)
            ->where(fn (Builder $query) => $query->where('is_completed', true)->orWhereNotNull('submitted_at'))
            ->count();

        return array_map(fn (int $count) => $count ?: null, [
            'messages' => ChatMessage::query()
                ->whereHas('conversation', fn (Builder $query) => $query->where('student_id', Auth::id()))
                ->where('sender_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->count(),
            'requirements' => count($activeKeys) - $started,
            'tasks' => StudentTask::query()->where('student_id', Auth::id())->where('status', '!=', 'done')->count(),
        ]);
    }
};
?>

<flux:sidebar.nav data-tour="main-navigation">
    <flux:sidebar.item icon="home" data-tour="nav-dashboard" :href="route('student.dashboard')" :current="request()->routeIs('student.dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="clipboard-document-check" data-tour="nav-requirements" :href="route('student.requirements')" :badge="$this->badges['requirements']" badgeColor="red" :current="request()->routeIs('student.requirements*')"
        wire:navigate>
        Requirements
    </flux:sidebar.item>

    <flux:sidebar.item icon="chat-bubble-left-right" data-tour="nav-messages" :href="route('student.messages')" :badge="$this->badges['messages']" badgeColor="red" :current="request()->routeIs('student.messages')"
        wire:navigate>
        Messages
    </flux:sidebar.item>

    <flux:sidebar.group expandable icon="calendar-days" heading="Daily Logs" data-tour="nav-group-daily-logs">
        <flux:sidebar.item icon="calendar-days" data-tour="nav-attendance" :href="route('student.attendance')" :current="request()->routeIs('student.attendance')"
            wire:navigate>
            Attendance
        </flux:sidebar.item>

        <flux:sidebar.item icon="document-text" data-tour="nav-reports" href="{{ route('student.reports-submissions') }}" :current="request()->routeIs('student.reports-submissions')"
            wire:navigate>
            Accomplishment Reports
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="squares-2x2" heading="Productivity" data-tour="nav-group-productivity">
        <flux:sidebar.item icon="rectangle-stack" data-tour="nav-tasks" href="{{ route('student.tasks') }}" :badge="$this->badges['tasks']" badgeColor="red" :current="request()->routeIs('student.tasks', 'student.kanban')"
            wire:navigate>
            Tasks
        </flux:sidebar.item>
        <flux:sidebar.item icon="pencil-square" data-tour="nav-notes" href="{{ route('student.notes') }}" :current="request()->routeIs('student.notes')"
            wire:navigate>
            Notes
        </flux:sidebar.item>
        <flux:sidebar.item icon="clock" data-tour="nav-pomodoro" href="{{ route('student.pomodoro') }}" :current="request()->routeIs('student.pomodoro')"
            wire:navigate>
            Pomodoro Timer
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group expandable icon="folder" heading="Documents" data-tour="nav-group-documents">
        <flux:sidebar.item icon="identification" data-tour="nav-resume-builder" href="{{ route('student.resumes') }}" :current="request()->routeIs('student.resumes*')"
            wire:navigate>
            Resume Builder
        </flux:sidebar.item>

        <flux:sidebar.item icon="check-badge" data-tour="nav-evaluations" href="{{ route('student.evaluations') }}" :current="request()->routeIs('student.evaluations')"
            wire:navigate>
            Evaluations
        </flux:sidebar.item>

        <flux:sidebar.item icon="trophy" data-tour="nav-certificate" href="{{ route('student.certificate') }}" :current="request()->routeIs('student.certificate')"
            wire:navigate>
            Certificates
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>
