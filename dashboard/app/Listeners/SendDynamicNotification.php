<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\NotificationEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\DynamicNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendDynamicNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationEvent $event)
    {
        // Pastikan recipients adalah sebuah koleksi
        $recipients = $event->recipients instanceof User ?
                      collect([$event->recipients]) :
                      $event->recipients;

        Notification::send($recipients, new DynamicNotification(
            $event->title, // Tambahkan ini
            $event->message,
            $event->url,
            $event->data
        ));
    }
}
