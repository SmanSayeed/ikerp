<?php

namespace App\Jobs;

use App\Events\SendEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event;

    /**
     * Create a new job instance.
     *
     * @param SendEmail $event
     */
    public function __construct(SendEmail $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $type = $this->event->type;
        $emailData = $this->event->emailData;
        $recipient = $this->event->recipient;

        Mail::to($recipient)->send(new \App\Mail\CommonEmailMailable($type, $emailData, $recipient));
    }
}
