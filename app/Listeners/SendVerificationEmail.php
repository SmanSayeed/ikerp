<?php

namespace App\Listeners;

use App\Events\SendEmail;
use App\Jobs\SendVerificationEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendVerificationEmail implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SendEmail $event)
    {
         // Generate the email verification URL
         $verificationUrl = URL::temporarySignedRoute(
            'verify.email', // Named route for email verification
            now()->addMinutes(60), // URL expiration time
            ['user' => $event->user->id]
        );

        // Dispatch the job
        SendVerificationEmailJob::dispatch($event->user, $verificationUrl);
    }
}
