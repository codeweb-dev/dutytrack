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
};
?>

<flux:sidebar.nav>
    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
        wire:navigate>
        Dashboard
    </flux:sidebar.item>

    <flux:sidebar.item icon="academic-cap" href="#">
        Assigned Students
    </flux:sidebar.item>

    <flux:sidebar.item icon="chart-bar-square" href="#">
        Productivity
    </flux:sidebar.item>

    <flux:sidebar.item icon="document-text" href="#">
        Reports / Submissions
    </flux:sidebar.item>

    <flux:sidebar.item icon="check-badge" href="#">
        Evaluation
    </flux:sidebar.item>
</flux:sidebar.nav>
