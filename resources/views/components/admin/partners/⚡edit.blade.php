<?php

use App\Livewire\Forms\ProfessorCoordinatorForm;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ProfessorCoordinatorForm $form;
    public User $professor;

    public function mount(User $professor): void
    {
        $this->professor = $professor;
        $this->form->setProfessor($professor);
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('professor-saved');
    }
};
?>

<form wire:submit="save" class="space-y-6">
    <flux:heading size="lg">Edit Professor or Coordinator</flux:heading>
    <flux:subheading>Update the employee details below.</flux:subheading>

    <flux:fieldset>
        <flux:legend>Professor / Coordinator Information</flux:legend>

        <div class="space-y-6">
            <div class="w-full">
                <x-custom-file-upload model="form.photo" label="Upload profile photo" accept="image/*" :existing="$form->existing_photo_url" />
            </div>

            <flux:input wire:model="form.employee_id" label="Employee ID" placeholder="EMP-0001"
                description:trailing="Employee ID can be found on the employee's ID card." />

            <div class="grid grid-cols-3 gap-x-4 gap-y-6">
                <flux:input wire:model="form.first_name" label="First Name" placeholder="John" />
                <flux:input wire:model="form.middle_name" label="Middle Name" badge="Optional" placeholder="Doe" />
                <flux:input wire:model="form.last_name" label="Last Name" placeholder="Smith" />
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                <flux:input wire:model="form.email" type="email" label="Email Address"
                    placeholder="john@example.com" />

                <flux:field>
                    <flux:label>Contact Number</flux:label>

                    <flux:input.group>
                        <flux:input.group.prefix>+63</flux:input.group.prefix>
                        <flux:input wire:model="form.contact_number" placeholder="123-456-7890" />
                    </flux:input.group>

                    <flux:error name="form.contact_number" />
                </flux:field>
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                <flux:input wire:model="form.password" type="password" label="New Password"
                    placeholder="Leave blank to keep current password" viewable />
                <flux:input wire:model="form.password_confirmation" type="password" label="Confirm New Password"
                    placeholder="Confirm new password" viewable />
            </div>

            <flux:input wire:model="form.department" label="Department" placeholder="Computer Science" />

            <flux:radio.group wire:model="form.position" label="Position" variant="cards" :indicator="false"
                class="max-sm:flex-col">
                <flux:radio value="OJT Coordinator" icon="globe-americas" label="OJT Coordinator"
                    description="Oversees internship programs, assigns students to professors, and coordinates with partner companies." />
                <flux:radio value="Professor" icon="academic-cap" label="Professor"
                    description="Handles assigned students, monitors their OJT progress, and provides guidance and evaluation." />
            </flux:radio.group>
        </div>
    </flux:fieldset>

    <div class="flex">
        <flux:spacer />
        <flux:button type="submit" variant="primary">Save Changes</flux:button>
    </div>
</form>
