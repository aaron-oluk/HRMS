<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class GenericNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public string $icon = 'bx-bell',
        public ?string $url = null,
    ) {}

    /**
     * @return list<string>
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
            'message' => $this->message,
            'icon' => $this->icon,
            'url' => $this->url,
        ];
    }
}
