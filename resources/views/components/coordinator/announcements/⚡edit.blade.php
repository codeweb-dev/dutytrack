<?php

use App\Livewire\Forms\AnnouncementForm;
use App\Models\Announcement;
use Livewire\Component;

new class extends Component {
    public AnnouncementForm $form;
    public Announcement $announcement;

    public function mount(Announcement $announcement): void
    {
        $this->announcement = $announcement;
        $this->form->setAnnouncement($announcement);
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('announcement-saved');
    }
};
?>

<form wire:submit="save" class="space-y-6">
    <flux:heading size="lg">Edit Announcement</flux:heading>
    <flux:subheading>Update the announcement details below.</flux:subheading>

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

    <div class="flex">
        <flux:spacer />
        <flux:button type="submit" variant="primary">Save Changes</flux:button>
    </div>
</form>
