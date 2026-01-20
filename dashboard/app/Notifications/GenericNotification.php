<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenericNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;
    public $url;
    public $additionalData;

    public function __construct(string $message, string $url, array $additionalData = [])
    {
        $this->message = $message;
        $this->url = $url;
        $this->additionalData = $additionalData;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Atau tambahkan 'mail' jika Anda mau
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'data' => $this->additionalData,
        ];
    }
}
