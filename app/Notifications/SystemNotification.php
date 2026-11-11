<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// ponytail: one generic in-app notification; add per-event classes when one needs mail/push.
class SystemNotification extends Notification
{
    public function __construct(
        public string $title,
        public ?string $body = null,
        public ?string $url = null,
        public string $icon = 'bell',
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => $this->icon,
        ];
    }
}
