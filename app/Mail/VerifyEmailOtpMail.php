<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerifyEmailOtpMail extends Mailable
{
    public function __construct(
        public string $otp,
        public string $language = 'en'
    ) {}

    public function build()
    {
        app()->setLocale($this->language);

        return $this->subject(__('Verify your email'))
            ->locale($this->language)
            ->view('emails.verify-otp');
    }
}
