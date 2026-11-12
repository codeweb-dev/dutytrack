<?php

namespace App\Livewire\Forms;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
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

    protected function announcementAuditData(?Announcement $announcement = null): array
    {
        $announcement ??= $this->announcement;

        return [
            'title' => $announcement?->title,
            'message' => $announcement?->message,
            'type' => $announcement?->type,
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

        $announcement = Announcement::create([
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
        ]);

        AuditLogger::log(
            module: 'Announcements',
            action: 'created',
            target: $announcement,
            targetLabel: $announcement->title,
            description: 'Created a new announcement.',
            newValues: $this->announcementAuditData($announcement),
        );

        // ponytail: sync fan-out to every user; queue it if the user count grows into the thousands.
        Notification::send(
            User::query()->active()->whereKeyNot(Auth::id())->get(),
            new SystemNotification('New announcement', $announcement->title, route('announcements'), 'megaphone'),
        );

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

        $oldValues = $this->announcementAuditData($this->announcement);

        $this->announcement->update([
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
        ]);

        $this->announcement = $this->announcement->fresh();
        $this->setAnnouncement($this->announcement);

        AuditLogger::log(
            module: 'Announcements',
            action: 'updated',
            target: $this->announcement,
            targetLabel: $this->announcement->title,
            description: 'Updated an announcement.',
            oldValues: $oldValues,
            newValues: $this->announcementAuditData($this->announcement),
        );

        Flux::toast(
            variant: 'success',
            heading: 'Announcement updated.',
            text: 'The announcement has been updated successfully.'
        );

        Flux::modal('edit-announcement-'.$this->announcement->id)->close();
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
