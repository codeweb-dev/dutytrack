<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Livewire\Forms\Admin\SupervisorForm;

new class extends Component {
    use WithFileUploads;

    public SupervisorForm $form;

    public function save()
    {
        $this->form->store();
        $this->dispatch('supervisor-saved');
    }

    public function fillRandom(): void
    {
        $this->form->fillRandom();
    }

    public function updatedFormSupervisorId(): void
    {
        $this->form->fillFromStaffDirectory();
    }

    public function updatedFormLastName(): void
    {
        $this->form->fillPasswordFromLastName();
    }
};
?>

<div>
    <flux:modal.trigger name="create-supervisor">
        <flux:button icon="plus">Add Supervisor</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-supervisor" flyout variant="floating" class="md:w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Create Supervisor</flux:heading>
                    <flux:subheading>Fill in the details to add a new supervisor.</flux:subheading>
                </div>

                <flux:button type="button" wire:click="fillRandom" icon="sparkles" variant="ghost">
                    Random
                </flux:button>
            </div>

            <flux:fieldset>
                <flux:legend>Supervisor Information</flux:legend>

                <div class="space-y-6">
                    <div class="w-full">
                        <x-custom-file-upload model="form.photo" label="Upload supervisor photo" accept="image/*" />
                    </div>

                    <flux:input wire:model.live.debounce.300ms="form.supervisor_id" label="Supervisor ID" placeholder="SUP-3001"
                        description:trailing="Supervisor ID can be found on the supervisor record." />

                    <div class="grid grid-cols-3 gap-x-4 gap-y-6">
                        <flux:input wire:model="form.first_name" label="First Name" placeholder="John" />
                        <flux:input wire:model="form.middle_name" label="Middle Name" badge="Optional"
                            placeholder="Doe" />
                        <flux:input wire:model.live.debounce.300ms="form.last_name" label="Last Name"
                            placeholder="Smith" />
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                        <flux:input wire:model="form.email" type="email" label="Email"
                            placeholder="juan@example.com" />

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

                    <flux:input wire:model="form.company_name" label="Company / Institution" placeholder="ABC Corporation" />

                    <flux:textarea wire:model="form.company_address" label="Company Address"
                        placeholder="Enter company address" rows="4" />
                </div>
            </flux:fieldset>

            <div class="w-full">
                <flux:button type="submit" variant="primary" class="w-full">
                    Create Supervisor
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
