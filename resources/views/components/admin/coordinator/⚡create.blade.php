<?php

use App\Livewire\Forms\Admin\CoordinatorForm;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public CoordinatorForm $form;

    public function save(): void
    {
        $this->form->store();
        $this->dispatch('coordinator-saved');
    }

    public function updatedFormLastName(): void
    {
        $this->form->fillPasswordFromLastName();
    }

    public function updatedFormDepartment(): void
    {
        $this->form->program = '';
    }
};
?>

<div>
    <flux:modal.trigger name="create-coordinator">
        <flux:button icon="plus">Add Coordinator</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-coordinator" flyout variant="floating" class="md:w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Create Coordinator</flux:heading>
                    <flux:subheading>Fill in the details to add a new OJT coordinator.</flux:subheading>
                </div>

            </div>

            <flux:fieldset>
                <flux:legend>Coordinator Information</flux:legend>

                <div class="space-y-6">
                    <div class="w-full">
                        <x-custom-file-upload model="form.photo" label="Upload profile photo" accept="image/*" />
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                        <flux:input wire:model="form.first_name" label="First Name" placeholder="John" />
                        <flux:input wire:model="form.middle_name" label="Middle Name" badge="Optional"
                            placeholder="Doe" />
                        <div class="col-span-2">
                            <flux:input wire:model.live.debounce.300ms="form.last_name" label="Last Name"
                                placeholder="Smith" />
                        </div>
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

                    <flux:select wire:model.live="form.department" label="Department" placeholder="Select department">
                        <option value="">Select department</option>
                        @foreach ($form->departments() as $department)
                            <option value="{{ $department }}">{{ $department }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="form.program" label="Program" placeholder="Select program"
                        :disabled="! $form->department || empty($form->programs())">
                        <option value="">Select program</option>
                        @foreach ($form->programs() as $program)
                            <option value="{{ $program }}">{{ $program }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </flux:fieldset>

            <div class="w-full">
                <flux:button type="submit" variant="primary" class="w-full">
                    Create Coordinator
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
