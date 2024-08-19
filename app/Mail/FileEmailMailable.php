<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileEmailMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $emailData;

    public function __construct($emailData)
    {
        $this->emailData = $emailData;
    }

    public function build()
    {
        return $this->view('emails.common')
                    ->with([
                        'type' => 'file',
                        'data' => $this->emailData,
                    ])
                    ->attach($this->emailData['file_path']); // Assuming `file_path` is in emailData
    }
}
