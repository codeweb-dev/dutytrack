<?php

use App\Livewire\Forms\Coordinator\OjtAssignmentForm;
use App\Models\OjtAssignment;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public OjtAssignmentForm $form;

    public OjtAssignment $assignment;

    public function updatedFormOjtType(): void
    {
        $this->form->company_supervisor_id = null;
    }

    public function mount(OjtAssignment $assignment): void
    {
        abort_unless((int) $assignment->student?->coordinator_id === (int) auth()->id(), 403);

        $this->assignment = $assignment;
        $this->form->setAssignment($assignment);
    }

    #[Computed]
    public function companySupervisors()
    {
        return User::query()->with('supervisorProfile')->availableToCoordinator(auth()->id())->active()->role('supervisor')->whereHas('supervisorProfile', fn($query) => $query->where('supervisor_type', $this->form->ojt_type))->orderBy('first_name')->get();
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
        <flux:subheading class="mt-2">Update the student assignment and supervisor.</flux:subheading>
    </div>

    <div class="grid gap-6">
        {{-- The student is fixed: logs, reports, tasks and chat hang off this assignment. To move a student, archive and create a new one. --}}
        <flux:input label="Student" disabled
            :value="$assignment->student?->display_name . ($assignment->student?->studentProfile?->student_id ? ' - ' . $assignment->student->studentProfile->student_id : '')" />

        <flux:radio.group wire:model.live="form.ojt_type" label="OJT Type" variant="cards" :indicator="false"
            class="max-sm:flex-col" required>
            <flux:radio value="internal" icon="academic-cap" label="Internal" description="Internal OJT" />
            <flux:radio value="external" icon="briefcase" label="External" description="External OJT" />
        </flux:radio.group>

        <flux:select wire:key="edit-{{ $assignment->id }}-external-supervisor-select"
            wire:model.live="form.company_supervisor_id" label="Supervisor" required>
            <option value="">Select supervisor</option>
            @foreach ($this->companySupervisors as $supervisor)
                <option value="{{ $supervisor->id }}">
                    {{ $supervisor->display_name }}{{ $supervisor->supervisorProfile?->company_name ? ' - ' . $supervisor->supervisorProfile->company_name : '' }}
                </option>
            @endforeach
        </flux:select>


        <flux:field>
            <flux:label badge="Required">Attendance Location</flux:label>
            <flux:description>Pin where the student must be to record attendance.</flux:description>
            <x-geofence-picker-map id="edit-geofence-map-{{ $assignment->id }}" />
        </flux:field>
    </div>

    <flux:button type="submit" variant="primary" class="w-full">Save Changes</flux:button>
</form>
