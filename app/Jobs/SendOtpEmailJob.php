<?php

namespace App\Jobs;

use App\Mail\PasswordResetOtpMail;
use App\Models\Creator;
use Illuminate\Bus\Queueable;
use App\Mail\VerifyEmailOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class SendOtpEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Creator $creator,
        public string $otp,
        public string $type = 'email_verification'
    ) {
        $this->onQueue('otp');
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mailable = match ($this->type) {
            'password_reset' => new PasswordResetOtpMail($this->otp),
            'email_verification' => new VerifyEmailOtpMail($this->otp),
            default => new VerifyEmailOtpMail($this->otp),
        };

        Mail::to($this->creator->email)
            ->send($mailable);
    }
}
