<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DynamicNotification extends Notification
{
    use Queueable;

    public $title; // Properti baru untuk judul
    public $message;
    public $url;
    public $data;

    /**
     * @param string $title Judul notifikasi
     * @param string $message Pesan notifikasi
     * @param string $url URL yang dituju saat diklik
     * @param array $data Data tambahan
     */
    public function __construct(string $title, string $message, string $url, array $data = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title, // Tambahkan 'title' ke data database
            'message' => $this->message,
            'url' => $this->url,
            'data' => $this->data,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title) // Gunakan judul di subjek email
            ->line($this->message)
            ->action('Notification Action', url($this->url))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'data' => $this->data,
        ];
    }
}
