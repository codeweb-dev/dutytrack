<?php

use App\Livewire\Forms\AnnouncementForm;
use Livewire\Component;

new class extends Component {
    public AnnouncementForm $form;

    public function save()
    {
        $this->form->store();
        $this->dispatch('announcement-saved');
    }
};
?>

<div>
    <flux:modal.trigger name="create-announcement">
        <flux:button icon="plus">Add Announcement</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-announcement" flyout variant="floating" class="md:w-2xl">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Create Announcement</flux:heading>
            <flux:subheading>Post a new system-wide announcement.</flux:subheading>

            <flux:fieldset>
                <flux:legend>Announcement Details</flux:legend>

                <div class="space-y-6">
                    <flux:input wire:model="form.title" label="Title" placeholder="Scheduled System Maintenance" required />
                    
                    <flux:radio.group wire:model="form.type" label="Priority" variant="pills" required>
                        <flux:radio value="info" label="Info" />
                        <flux:radio value="warning" label="Warning" />
                        <flux:radio value="urgent" label="Urgent" />
                        <flux:radio value="maintenance" label="Maintenance" />
                    </flux:radio.group>

                    <flux:textarea wire:model="form.message" label="Message / Body" required
                        placeholder="Write the announcement message here..." rows="6" />
                </div>
            </flux:fieldset>

            <div class="w-full">
                <flux:button type="submit" variant="primary" class="w-full">
                    Create Announcement
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
