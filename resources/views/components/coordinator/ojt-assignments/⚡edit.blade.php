<?php

use App\Livewire\Forms\Coordinator\OjtAssignmentForm;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public OjtAssignmentForm $form;

    public OjtAssignment $assignment;

    public function mount(OjtAssignment $assignment): void
    {
        abort_unless((int) $assignment->student?->coordinator_id === (int) auth()->id(), 403);

        $this->assignment = $assignment;
        $this->form->setAssignment($assignment);
    }

    #[Computed]
    public function students()
    {
        return User::query()
            ->with(['studentProfile', 'ojtAssignment'])
            ->where('coordinator_id', auth()->id())
            ->role('student')
            ->where(function (Builder $query) {
                $query->whereDoesntHave('ojtAssignment')
                    ->orWhere('id', $this->assignment->student_id);
            })
            ->orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function professors()
    {
        return User::query()
            ->with('professorProfile')
            ->role('professor')
            ->orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function companySupervisors()
    {
        return User::query()
            ->with('supervisorProfile')
            ->role('company_supervisor')
            ->orderBy('first_name')
            ->get();
    }

    public function updatedFormOjtType(string $value): void
    {
        if ($value === 'internal') {
            $this->form->company_supervisor_id = null;
        }

        if ($value === 'external') {
            $this->form->professor_id = null;
        }
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('assignment-saved');
    }
};
?>

<form wire:submit="save" class="space-y-6">
    <div>
        <flux:heading size="lg">Edit OJT Assignment</flux:heading>
        <flux:subheading class="mt-2">Update the assignment details, but keep only one assignee: professor or company supervisor.</flux:subheading>
    </div>

    <div class="grid gap-6">
        <flux:select wire:model.live="form.student_id" label="Student">
            <option value="">Select student</option>
            @foreach ($this->students as $student)
                <option value="{{ $student->id }}">
                    {{ $student->name }}{{ $student->studentProfile?->student_id ? ' - ' . $student->studentProfile->student_id : '' }}
                </option>
            @endforeach
        </flux:select>

        <flux:radio.group wire:model.live="form.ojt_type" label="OJT Type" variant="cards" :indicator="false"
            class="max-sm:flex-col">
            <flux:radio value="internal" icon="academic-cap" label="Internal" description="Assign to a professor" />
            <flux:radio value="external" icon="briefcase" label="External"
                description="Assign to a company supervisor" />
        </flux:radio.group>

        <flux:text>
            A student can only have one assignee for an OJT assignment.
        </flux:text>

        @if ($form->ojt_type === 'internal')
            <flux:select wire:model="form.professor_id" label="Professor">
                <option value="">Select professor</option>
                @foreach ($this->professors as $professor)
                    <option value="{{ $professor->id }}">
                        {{ $professor->name }}
                    </option>
                @endforeach
            </flux:select>
        @else
            <flux:select wire:model="form.company_supervisor_id" label="Company Supervisor">
                <option value="">Select company supervisor</option>
                @foreach ($this->companySupervisors as $supervisor)
                    <option value="{{ $supervisor->id }}">
                        {{ $supervisor->name }}{{ $supervisor->supervisorProfile?->company_name ? ' - ' . $supervisor->supervisorProfile->company_name : '' }}
                    </option>
                @endforeach
            </flux:select>
        @endif

        <flux:select wire:model="form.status" label="Status">
            <option value="pending">Pending</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </flux:select>

        <flux:textarea wire:model="form.remarks" label="Remarks" rows="4" badge="Optional"
            placeholder="Add assignment notes, deployment details, or coordinator remarks." />
    </div>

    <flux:button type="submit" variant="primary" class="w-full">Save Changes</flux:button>
</form>
