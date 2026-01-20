<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $recipients; // Bisa berupa satu User atau koleksi User
    public $title; // Properti baru untuk judul
    public $message;
    public $url;
    public $data;

    /**
     * Create a new event instance.
     *
     * @param User|Collection $recipients
     * @param string $title
     * @param string $message
     * @param string $url
     * @param array $data
     */
    public function __construct(User|Collection $recipients, string $title, string $message, string $url, array $data = [])
    {
        $this->recipients = $recipients;
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Ganti 'channel-name' dengan channel yang sesuai
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
