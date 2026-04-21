<?php

use App\Livewire\Forms\Coordinator\OjtAssignmentForm;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public OjtAssignmentForm $form;

    #[Computed]
    public function students()
    {
        return User::query()
            ->with(['studentProfile', 'ojtAssignment'])
            ->where('coordinator_id', auth()->id())
            ->role('student')
            ->whereDoesntHave('ojtAssignment')
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

    public function updatedFormStudentId($value): void
    {
        if (! $value) {
            $this->form->required_hours = 0;

            return;
        }

        $student = $this->students->firstWhere('id', (int) $value);

        $this->form->required_hours = (int) ($student?->studentProfile?->ojt_required_hours ?? 0);
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
        $this->form->store();
        $this->dispatch('assignment-saved');
    }
};
?>

<div>
    <flux:modal.trigger name="create-ojt-assignment">
        <flux:button icon="plus">Assign Student</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-ojt-assignment" flyout variant="floating" class="md:w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Create OJT Assignment</flux:heading>
                <flux:subheading class="mt-2">Assign one of your students to exactly one assignee: either a professor or a company supervisor.</flux:subheading>
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
                    <flux:radio value="internal" icon="academic-cap" label="Internal"
                        description="Assign to a professor" />
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

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="form.start_date" type="date" label="Start Date" />
                    <flux:input wire:model="form.end_date" type="date" label="End Date" badge="Optional" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="form.required_hours" type="number" min="0" label="Required Hours" />

                    <flux:select wire:model="form.status" label="Status">
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </flux:select>
                </div>

                <flux:textarea wire:model="form.remarks" label="Remarks" rows="4" badge="Optional"
                    placeholder="Add assignment notes, deployment details, or coordinator remarks." />
            </div>

            <flux:button type="submit" variant="primary" class="w-full">Save Assignment</flux:button>
        </form>
    </flux:modal>
</div>
