<?php

use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    protected function professorId(): int
    {
        return (int) Auth::id();
    }

    #[Computed]
    public function assignedStudentsCount(): int
    {
        return OjtAssignment::query()->where('professor_id', $this->professorId())->count();
    }

    #[Computed]
    public function productivityCount(): int
    {
        return AttendanceLog::query()
            ->whereHas('assignment', fn (Builder $query) => $query->where('professor_id', $this->professorId()))
            ->count();
    }

    #[Computed]
    public function submissionsCount(): int
    {
        return DutyHourLog::query()
            ->whereHas('assignment', fn (Builder $query) => $query->where('professor_id', $this->professorId()))
            ->count();
    }

    #[Computed]
    public function evaluationsCount(): int
    {
        return Evaluation::query()->where('evaluator_id', $this->professorId())->count();
    }
};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" :badge="$this->assignedStudentsCount" href="#">
        Assigned Students
    </flux:sidebar.item>

    <flux:sidebar.item icon="chart-bar-square" :badge="$this->productivityCount" href="#">
        Productivity
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" :badge="$this->submissionsCount" href="#">
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="check-badge" :badge="$this->evaluationsCount" href="#">
        Evaluation
    </flux:sidebar.item>
</flux:sidebar.nav>
