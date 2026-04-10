<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Livewire\Forms\StudentForm;

new class extends Component {
    use WithFileUploads;

    public StudentForm $form;

    public function save()
    {
        $this->form->store();
    }
};
?>

<div>
    <flux:modal.trigger name="create-student">
        <flux:button icon="plus">Add Student</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-student" flyout variant="floating" class="md:w-2xl">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Create Student</flux:heading>
            <flux:subheading>Fill in the details to add a new student.</flux:subheading>

            <flux:fieldset>
                <flux:legend>Student Information</flux:legend>

                <div class="space-y-6">
                    <div class="w-full">
                        <x-custom-file-upload model="form.photo" label="Upload student photo" accept="image/*" />
                    </div>

                    <flux:input wire:model="form.student_id" label="Student ID" placeholder="2316-02373C"
                        description:trailing="Student ID can be found on the student's ID card." />

                    <div class="grid grid-cols-3 gap-x-4 gap-y-6">
                        <flux:input wire:model="form.first_name" label="First name" placeholder="John" />
                        <flux:input wire:model="form.middle_name" label="Middle name" badge="Optional"
                            placeholder="Doe" />
                        <flux:input wire:model="form.last_name" label="Last name" placeholder="Smith" />
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                        <flux:input wire:model="form.email" type="email" label="Email"
                            placeholder="john.smith@example.com" />

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

                    <div class="grid grid-cols-3 gap-x-4 gap-y-6">
                        <flux:select wire:model="form.course" label="Course">
                            <option value="">Select course</option>
                            <option value="BSCS">BSCS</option>
                            <option value="BSIT">BSIT</option>
                        </flux:select>

                        <flux:input wire:model="form.year_level" label="Year Level" placeholder="4" />
                        <flux:input wire:model="form.section" label="Section" placeholder="A" />
                    </div>

                    <flux:input wire:model="form.ojt_required_hours" type="number" label="OJT Required Hours"
                        placeholder="486" />
                </div>
            </flux:fieldset>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create Student</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
