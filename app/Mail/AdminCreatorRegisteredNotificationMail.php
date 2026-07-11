<?php

namespace App\Mail;

use App\Models\Creator;
use Illuminate\Mail\Mailable;

class AdminCreatorRegisteredNotificationMail extends Mailable
{
    public function __construct(
        public Creator $creator
    ) {}

    public function build()
    {
        return $this->subject(__('New Creator Registered: ') . $this->creator->name)
            ->view('emails.admin-creator-registered');
    }
}
