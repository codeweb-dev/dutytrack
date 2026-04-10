<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div>
    <flux:modal.trigger name="create-student">
        <flux:button icon="plus">Add Student
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-student" flyout variant="floating" class="md:w-2xl">
        <div class="space-y-6">
            <flux:heading size="lg">Create Student</flux:heading>
            <flux:subheading>Fill in the details to add a new student.</flux:subheading>

            <flux:fieldset>
                <flux:legend>Student Information</flux:legend>

                <div class="space-y-6">
                    <flux:input label="Student ID" placeholder="2316-02373C"
                        description:trailing="Student ID can be found on the student's ID card." />

                    <div class="grid grid-cols-3 gap-x-4 gap-y-6">
                        <flux:input label="First name" placeholder="John" />
                        <flux:input label="Middle name" badge="Optional" placeholder="Doe" />
                        <flux:input label="Last name" placeholder="Smith" />
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                        <flux:input type="email" label="Email" placeholder="john.smith@example.com" />

                        <flux:field>
                            <flux:label>Contact Number</flux:label>

                            <flux:input.group>
                                <flux:input.group.prefix>+63</flux:input.group.prefix>

                                <flux:input wire:model="contactNumber" placeholder="123-456-7890" />
                            </flux:input.group>

                            <flux:error name="contactNumber" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                        <flux:input type="password" label="Password" placeholder="John" viewable />
                        <flux:input type="password" label="Confirm Password" placeholder="John" viewable />
                    </div>

                    <div class="grid grid-cols-3 gap-x-4 gap-y-6">
                        <flux:select label="Course">
                            <option selected>BSCS</option>
                            <option>BSIT</option>
                        </flux:select>

                        <flux:input label="Year Level" placeholder="2024" />
                        <flux:input label="Section" placeholder="A" />
                    </div>
                </div>
            </flux:fieldset>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create Student</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
