<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PasswordResetOtpMail extends Mailable
{
    public function __construct(
        public string $otp
    ) {}

    public function build()
    {
        return $this->subject('Reset your password')
            ->view('emails.password-reset-otp');
    }
}
