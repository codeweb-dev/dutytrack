<?php

use App\Enums\Role as EnumsRole;
use App\Models\AttendanceLog;
use App\Models\User;
use App\Support\HourFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('OJT Certificates')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Coordinator Workspace', 'href' => '#'],
        ['label' => 'Monitoring', 'href' => '#'],
        ['label' => 'Certificates'],
    ];

    public string $title = 'OJT Certificates';

    public string $description = 'View and download the Certificate of Completion of your students. Students can download theirs once they render their required OJT hours.';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function students(): LengthAwarePaginator
    {
        $search = '%'.$this->search.'%';

        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', EnumsRole::STUDENT->value))
            ->where('users.coordinator_id', Auth::id())
            ->leftJoin('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->select('users.*', 'student_profiles.student_id as student_number', 'student_profiles.ojt_required_hours')
            ->selectSub(AttendanceLog::query()->selectRaw('coalesce(sum(hours_rendered), 0)')->whereColumn('student_id', 'users.id'), 'rendered_hours')
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('users.name', 'like', $search)
                ->orWhere('users.email', 'like', $search)
                ->orWhere('student_profiles.student_id', 'like', $search)))
            ->orderBy('users.last_name')
            ->orderBy('users.name')
            ->paginate(10);
    }
};
?>

<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold">Certificate of Completion</h2>

            <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                placeholder="Search students..." clearable class="w-full sm:w-64" />
        </div>

        @if ($this->students->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="academic-cap" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No students found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search to find a student's certificate.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->students">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Hours Rendered</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->students as $student)
                        @php
                            $required = (int) $student->ojt_required_hours;
                            $rendered = (float) $student->rendered_hours;
                            $eligible = $required > 0 && $rendered >= $required;
                        @endphp

                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$student->profile_photo_url" :name="$student->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $student->display_name }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $student->email }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">{{ $student->student_number }}</flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ HourFormatter::format($rendered) }} / {{ HourFormatter::format($required) }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="$eligible ? 'green' : 'amber'" size="sm" inset="top bottom">
                                    {{ $eligible ? 'Completed' : 'In Progress' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex gap-1">
                                    <flux:button size="xs" icon="eye" :href="route('coordinator.certificates.view', $student)" target="_blank">View</flux:button>
                                    <flux:button size="xs" variant="primary" icon="arrow-down-tray" :href="route('coordinator.certificates.download', $student)">Download</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
