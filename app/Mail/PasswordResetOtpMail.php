<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PasswordResetOtpMail extends Mailable
{
    public function __construct(
        public string $otp,
        public string $language = 'en'
    ) {}

    public function build()
    {
        app()->setLocale($this->language);

        return $this->subject(__('Reset your password'))
            ->locale($this->language)
            ->view('emails.password-reset-otp');
    }
}
