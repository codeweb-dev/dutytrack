<?php

use App\Livewire\Forms\SupervisorForm;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public SupervisorForm $form;
    public User $supervisor;

    public function mount(User $supervisor): void
    {
        $this->supervisor = $supervisor;
        $this->form->setSupervisor($supervisor);
    }

    public function updatedFormSupervisorType(): void
    {
        $this->form->applySupervisorType();
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('supervisor-saved');
    }
};
?>

<form wire:submit="save" class="space-y-6">
    <flux:heading size="lg">Edit Supervisor</flux:heading>
    <flux:subheading>Update the supervisor details below.</flux:subheading>

    <flux:fieldset>
        <flux:legend>Supervisor Information</flux:legend>

        <div class="space-y-6">
            <div class="w-full">
                <x-custom-file-upload model="form.photo" label="Upload supervisor photo" accept="image/*"
                    :existing="$form->existing_photo_url" />
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                <flux:input wire:model="form.first_name" label="First Name" placeholder="John" required />
                <flux:input wire:model="form.middle_name" label="Middle Name" badge="Optional" placeholder="Doe" />
                <div class="col-span-2">
                    <flux:input wire:model="form.last_name" label="Last Name" placeholder="Smith" required />
                </div>
                <flux:select wire:model="form.extension_name" label="Extension name" badge="Optional">
                    <option value="">None</option>
                    @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $extension)
                        <option value="{{ $extension }}">{{ $extension }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                <flux:input wire:model="form.email" type="email" label="Email" placeholder="juan@example.com" required />

                <flux:field>
                    <flux:label badge="Optional">Contact Number</flux:label>

                    <flux:input.group>
                        <flux:input.group.prefix>+63</flux:input.group.prefix>
                        <flux:input wire:model="form.contact_number" placeholder="123-456-7890" />
                    </flux:input.group>

                    <flux:error name="form.contact_number" />
                </flux:field>
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                <flux:input wire:model="form.password" type="password" label="New Password" badge="Optional"
                    placeholder="Leave blank to keep current password" viewable />

                <flux:input wire:model="form.password_confirmation" type="password" label="Confirm New Password" badge="Optional"
                    placeholder="Confirm new password" viewable />
            </div>

            <flux:radio.group wire:model.live="form.supervisor_type" label="Type" variant="segmented" required>
                <flux:radio value="internal" label="Internal" />
                <flux:radio value="external" label="External" />
            </flux:radio.group>

            @if ($form->supervisor_type === 'internal')
                <flux:input wire:model="form.company_name" label="Institution" placeholder="EARIST CAVITE CAMPUS" required />
            @else
                <flux:input wire:model="form.company_name" label="Company" placeholder="ABC Corporation" required />
            @endif

            <flux:input wire:model="form.position_department" label="Position and Department" badge="Optional"
                placeholder="e.g. HR Manager, Human Resources" />

            <flux:textarea wire:model="form.company_address"
                :label="$form->supervisor_type === 'internal' ? 'Institution Address' : 'Company Address'"
                badge="Optional" placeholder="Enter address" rows="4" />
        </div>
    </flux:fieldset>

    <div class="flex">
        <flux:spacer />
        <flux:button type="submit" variant="primary">Save Changes</flux:button>
    </div>
</form>
