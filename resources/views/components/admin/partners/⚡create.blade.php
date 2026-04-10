<?php

use App\Livewire\Forms\ProfessorCoordinatorForm;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ProfessorCoordinatorForm $form;

    public function save()
    {
        $this->form->store();
        $this->dispatch('professor-saved');
    }
};
?>

<div>
    <flux:modal.trigger name="create-professor">
        <flux:button icon="plus">Add Professor or Coordinator</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-professor" flyout variant="floating" class="md:w-2xl">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Create Professor or Coordinator</flux:heading>
            <flux:subheading>Fill in the details to add a new professor or coordinator.</flux:subheading>

            <flux:fieldset>
                <flux:legend>Professor / Coordinator Information</flux:legend>

                <div class="space-y-6">
                    <div class="w-full">
                        <x-custom-file-upload model="form.photo" label="Upload profile photo" accept="image/*" />
                    </div>

                    <flux:input wire:model="form.employee_id" label="Employee ID" placeholder="EMP-0001"
                        description:trailing="Employee ID can be found on the employee's ID card." />

                    <div class="grid grid-cols-3 gap-x-4 gap-y-6">
                        <flux:input wire:model="form.first_name" label="First Name" placeholder="John" />
                        <flux:input wire:model="form.middle_name" label="Middle Name" badge="Optional"
                            placeholder="Doe" />
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
                        <flux:input wire:model="form.password" type="password" label="Password"
                            placeholder="Enter password" viewable />
                        <flux:input wire:model="form.password_confirmation" type="password" label="Confirm Password"
                            placeholder="Confirm password" viewable />
                    </div>

                    <flux:input wire:model="form.department" label="Department" placeholder="Computer Science" />

                    <flux:radio.group wire:model="form.position" label="Position" variant="cards" :indicator="false" class="max-sm:flex-col">
                        <flux:radio value="OJT Coordinator" icon="globe-americas" label="OJT Coordinator"
                            description="Oversees internship programs, assigns students to professors, and coordinates with partner companies." />
                        <flux:radio value="Professor" icon="academic-cap" label="Professor"
                            description="Handles assigned students, monitors their OJT progress, and provides guidance and evaluation." />
                    </flux:radio.group>
                </div>
            </flux:fieldset>

            <div class="w-full">
                <flux:button type="submit" variant="primary" class="w-full">
                    Create Professor or Coordinator
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
