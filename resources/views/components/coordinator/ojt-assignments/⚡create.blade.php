<?php

use App\Livewire\Forms\Coordinator\OjtAssignmentForm;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public OjtAssignmentForm $form;

    public function updatedFormOjtType(): void
    {
        $this->form->company_supervisor_id = null;
    }

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
    public function companySupervisors()
    {
        return User::query()
            ->with('supervisorProfile')
            ->availableToCoordinator(auth()->id())
            ->role('supervisor')
            ->whereHas('supervisorProfile', fn ($query) => $query->where('supervisor_type', $this->form->ojt_type))
            ->orderBy('first_name')
            ->get();
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
                <flux:subheading class="mt-2">Assign one of your students to a supervisor.</flux:subheading>
            </div>

            <div class="grid gap-6">
                <flux:select wire:model.live="form.student_id" label="Student" required>
                    <option value="">Select student</option>
                    @foreach ($this->students as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->name }}{{ $student->studentProfile?->student_id ? ' - ' . $student->studentProfile->student_id : '' }}
                        </option>
                    @endforeach
                </flux:select>

                <flux:radio.group wire:model.live="form.ojt_type" label="OJT Type" variant="cards" :indicator="false"
                    class="max-sm:flex-col" required>
                    <flux:radio value="internal" icon="academic-cap" label="Internal"
                        description="Internal OJT" />
                    <flux:radio value="external" icon="briefcase" label="External"
                        description="External OJT" />
                </flux:radio.group>

                <flux:text>
                    A student can only have one assignee for an OJT assignment.
                </flux:text>

                    <flux:select wire:key="create-external-supervisor-select"
                    wire:model.live="form.company_supervisor_id" label="Supervisor" required>
                    <option value="">Select supervisor</option>
                    @foreach ($this->companySupervisors as $supervisor)
                        <option value="{{ $supervisor->id }}">
                            {{ $supervisor->name }}{{ $supervisor->supervisorProfile?->company_name ? ' - ' . $supervisor->supervisorProfile->company_name : '' }}
                        </option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="form.status" label="Status" required>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </flux:select>

                <flux:textarea wire:model="form.remarks" label="Remarks" rows="4" badge="Optional"
                    placeholder="Add assignment notes, deployment details, or coordinator remarks." />
            </div>

            <flux:button type="submit" variant="primary" class="w-full">Save Assignment</flux:button>
        </form>
    </flux:modal>
</div>
