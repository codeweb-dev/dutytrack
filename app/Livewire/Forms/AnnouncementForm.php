<?php

namespace App\Livewire\Forms;

use App\Models\Announcement;
use Flux\Flux;
use Livewire\Form;

class AnnouncementForm extends Form
{
    public ?Announcement $announcement = null;

    public string $title = '';
    public string $message = '';
    public string $type = 'info';

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'in:info,warning,urgent,maintenance'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'title' => 'title',
            'message' => 'message',
            'type' => 'type',
        ];
    }

    public function setAnnouncement(Announcement $announcement): void
    {
        $this->announcement = $announcement;
        $this->title = $announcement->title;
        $this->message = $announcement->message;
        $this->type = $announcement->type;

        $this->resetValidation();
    }

    public function store(): void
    {
        $this->validate();

        Announcement::create([
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
        ]);

        $this->resetForm();

        Flux::toast(
            variant: 'success',
            heading: 'Announcement created.',
            text: 'The announcement has been added successfully.'
        );

        Flux::modal('create-announcement')->close();
    }

    public function update(): void
    {
        if (! $this->announcement) {
            return;
        }

        $this->validate();

        $this->announcement->update([
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
        ]);

        $this->announcement = $this->announcement->fresh();
        $this->setAnnouncement($this->announcement);

        Flux::toast(
            variant: 'success',
            heading: 'Announcement updated.',
            text: 'The announcement has been updated successfully.'
        );

        Flux::modal('edit-announcement-' . $this->announcement->id)->close();
    }

    public function resetForm(): void
    {
        $this->announcement = null;
        $this->title = '';
        $this->message = '';
        $this->type = 'info';

        $this->resetValidation();
    }
}
