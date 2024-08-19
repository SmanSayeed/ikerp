<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
class CommonEmailMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $type;
    public $data;
    public $recipient;

    /**
     * Create a new message instance.
     *
     * @param string $type
     * @param array $data
     * @param string $recipient
     */
    public function __construct(string $type, array $data, string $recipient)
    {
        $this->type = $type;
        $this->data = $data;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.common')
                    ->with([
                        'type' => $this->type,
                        'data' => $this->data,
                        'recipient' => $this->recipient,
                    ]);
    }
}
