<?php

use App\Livewire\Forms\Admin\ProfessorForm;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ProfessorForm $form;

    public function save(): void
    {
        $this->form->store();
        $this->dispatch('professor-saved');
    }
};
?>

<div>
    <flux:modal.trigger name="create-professor">
        <flux:button icon="plus">Add Professor</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-professor" flyout variant="floating" class="md:w-2xl">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Create Professor</flux:heading>
            <flux:subheading>Fill in the details to add a new professor.</flux:subheading>

            <flux:fieldset>
                <flux:legend>Professor Information</flux:legend>

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

                    <flux:select wire:model="form.department" label="Department" placeholder="Select department">
                        <option value="">Select department</option>

                        <option value="Industrial Technology">Industrial Technology</option>
                        <option value="Business Administration">Business Administration</option>
                        <option value="Criminology">Criminology</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Hospitality Management">Hospitality Management</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Office Administration">Office Administration</option>
                        <option value="Psychology">Psychology</option>
                        <option value="Teacher Education">Teacher Education</option>
                    </flux:select>
                </div>
            </flux:fieldset>

            <div class="w-full">
                <flux:button type="submit" variant="primary" class="w-full">
                    Create Professor
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
