<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerifyEmailOtpMail extends Mailable
{
    public function __construct(
        public string $otp
    ) {}

    public function build()
    {
        return $this->subject('Verify your email')
            ->view('emails.verify-otp');
    }
}
