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

new #[Title('OJT Certificates')] class extends Component {
    use WithPagination;

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Monitoring', 'href' => '#'], ['label' => 'Certificates']];

    public string $title = 'OJT Certificates';

    public string $description = 'View the Certificate of Completion of your students. Students can download theirs once they render their required OJT hours.';

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status');
        $this->resetPage();
    }

    protected function studentQuery(): Builder
    {
        return User::query()->whereHas('roles', fn(Builder $query) => $query->where('name', EnumsRole::STUDENT->value))->where('users.coordinator_id', Auth::id())->leftJoin('student_profiles', 'users.id', '=', 'student_profiles.user_id');
    }

    // Same rule as the row badge: required hours set and rendered hours reached.
    protected function whereCompleted(Builder $query, bool $completed = true): Builder
    {
        $hours = AttendanceLog::query()->selectRaw('coalesce(sum(hours_rendered), 0)')->whereColumn('student_id', 'users.id');
        $sql = "(student_profiles.ojt_required_hours > 0 and ({$hours->toSql()}) >= student_profiles.ojt_required_hours)";

        return $query->whereRaw($completed ? $sql : "not coalesce({$sql}, false)", $hours->getBindings());
    }

    #[Computed]
    public function summary(): array
    {
        $total = $this->studentQuery()->count();
        $completed = $this->whereCompleted($this->studentQuery())->count();

        return ['total' => $total, 'completed' => $completed, 'in_progress' => $total - $completed];
    }

    #[Computed]
    public function students(): LengthAwarePaginator
    {
        $search = '%' . $this->search . '%';

        return $this->studentQuery()
            ->select('users.*', 'student_profiles.student_id as student_number', 'student_profiles.ojt_required_hours')
            ->selectSub(AttendanceLog::query()->selectRaw('coalesce(sum(hours_rendered), 0)')->whereColumn('student_id', 'users.id'), 'rendered_hours')
            ->when($this->search !== '', fn(Builder $query) => $query->where(fn(Builder $query) => $query->where('users.name', 'like', $search)->orWhere('users.email', 'like', $search)->orWhere('student_profiles.student_id', 'like', $search)))
            ->when($this->status !== '', fn(Builder $query) => $this->whereCompleted($query, $this->status === 'completed'))
            ->orderBy('users.last_name')
            ->orderBy('users.name')
            ->paginate(10);
    }
};
?>

<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 sm:grid-cols-3">
        @php $cards = ['' => ['Total Students', 'total', 'users'], 'completed' => ['Completed', 'completed', 'check-badge'], 'in_progress' => ['In Progress', 'in_progress', 'clock']]; @endphp
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('status', '{{ $value }}')" class="group text-left"
                aria-pressed="{{ $status === $value ? 'true' : 'false' }}">
                <flux:card size="xs"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-700 group-aria-pressed:border-zinc-800 group-aria-pressed:outline-1 group-aria-pressed:outline-zinc-800 dark:group-aria-pressed:border-white dark:group-aria-pressed:outline-white">
                    <flux:heading class="flex items-center gap-2">{{ $label }}
                        <flux:icon :name="$icon" :variant="$status === $value ? 'solid' : 'outline'"
                            class="ml-auto size-5 {{ $status === $value ? 'text-zinc-800 dark:text-white' : 'text-zinc-400' }}" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $this->summary[$key] }}</flux:heading>
                </flux:card>
            </button>
        @endforeach
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Total Students' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-certificates" heading="Filter Certificates"
                    description="Filter students by certificate status.">
                    <flux:radio.group wire:model.live="status" label="Certificate Status" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="completed" label="Completed" />
                        <flux:radio value="in_progress" label="In Progress" />
                    </flux:radio.group>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->students->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="academic-cap" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No students found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find a student's certificate.
                    </flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->students" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Hours Rendered</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
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
                                <flux:avatar size="md" :src="$student->profile_photo_url"
                                    :name="$student->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $student->display_name }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $student->email }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">{{ $student->student_number }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ HourFormatter::format($rendered) }} / {{ HourFormatter::format($required) }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="\App\Support\BadgeColor::for($eligible ? 'completed' : 'in_progress')" size="sm" inset="top bottom">
                                    {{ $eligible ? 'Completed' : 'In Progress' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:button variant="filled" size="xs" icon="eye" inset="top bottom"
                                    :href="route('coordinator.certificates.view', $student)" target="_blank">View
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
