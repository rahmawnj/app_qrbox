<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use App\Models\User;

class NewActivityEvent
{
    use Dispatchable;

    public $recipients;
    public $message;
    public $url;
    public $additionalData;

    public function __construct(array $recipients, string $message, string $url, array $additionalData = [])
    {
        $this->recipients = $recipients;
        $this->message = $message;
        $this->url = $url;
        $this->additionalData = $additionalData;
    }
}
