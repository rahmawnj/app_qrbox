<?php

namespace App\Listeners;

use App\Events\NewActivityEvent;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendGenericNotification implements ShouldQueue
{
    public function handle(NewActivityEvent $event)
    {
        // Temukan semua user dengan role yang ditentukan di event
        $users = User::whereIn('role', $event->recipients)->get();

        // Kirim notifikasi generik ke semua user tersebut
        Notification::send(
            $users,
            new GenericNotification($event->message, $event->url, $event->additionalData)
        );
    }
}
