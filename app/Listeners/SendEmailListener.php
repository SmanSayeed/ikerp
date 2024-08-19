<?php

namespace App\Listeners;

use App\Events\SendEmail;
use App\Mail\VerificationEmailMailable;
use App\Mail\PasswordResetEmailMailable;
use App\Mail\FileEmailMailable;
use App\Mail\WarningEmailMailable;
use App\Mail\GeneralEmailMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailListener implements ShouldQueue
{
    public function handle(SendEmail $event)
    {
        $type = $event->type;
        $emailData = $event->emailData;
        $recipient = $event->recipient;

        $mailable = $this->getMailableByType($type, $emailData);

        if ($mailable) {
            Mail::to($recipient)->send($mailable);
        } else {
            // Handle the case where the type is not recognized
            throw new \Exception('Unknown email type: ' . $type);
        }
    }

    private function getMailableByType($type, $emailData)
    {
        switch ($type) {
            case 'verification':
                return new VerificationEmailMailable($emailData);
            case 'password_reset':
                return new PasswordResetEmailMailable($emailData);
            case 'file':
                return new FileEmailMailable($emailData);
            case 'warning':
                return new WarningEmailMailable($emailData);
            case 'general':
                return new GeneralEmailMailable($emailData);
            default:
                return null;
        }
    }
}
