<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class SendEmail
{
    use SerializesModels;

    public $type;
    public $emailData;
    public $recipient;

    /**
     * Create a new event instance.
     *
     * @param string $type
     * @param array $emailData
     * @param string $recipient
     */
    public function __construct(string $type, array $emailData, string $recipient)
    {
        $this->type = $type;
        $this->emailData = $emailData;
        $this->recipient = $recipient;
    }
}
