<?php

namespace App\Listeners;

use App\Events\CreatorRegistered;
use App\Services\OtpService;


class SendEmailVerification
{
    public function __construct(protected OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function handle(CreatorRegistered $event): void
    {
        $creator = $event->creator;
        $this->otpService->sendEmailVerificationOtp($creator);
    }
}
