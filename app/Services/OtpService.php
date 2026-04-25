<?php

namespace App\Services;

use App\DTOs\OtpDTO;
use App\Models\Creator;
use App\Jobs\SendOtpEmailJob;
use App\Repositories\Interfaces\OtpRepositoryInterface;
use App\Exceptions\Otp\{OtpInvalidException, OtpUsedException, OtpBlockedException, OtpExpiredException};
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;



class OtpService
{
    public function __construct(
        protected
        OtpRepositoryInterface $otpRepositoryInterface,
    ) {
    }
    public function sendEmailVerificationOtp($creator): void
    {
        $this->sendOtp($creator, 'email_verification');
    }


    public function sendPasswordResetOtp($creator): void
    {
        $this->sendOtp($creator, 'password_reset');
    }

    protected function sendOtp($creator, string $type): void
    {
        $this->checkRateLimit($creator->email, $type);

        $otp = $this->generateOtp();

        $this->create(new OtpDTO(
            otp: $otp,
            email: $creator->email,
            type: $type,
            expires_at: now()->addMinutes(10)
        ));

        $this->incrementRateLimit($creator->email, $type);

        SendOtpEmailJob::dispatch($creator, $otp, $type);
    }

    protected function checkRateLimit(string $email, string $type): void
    {
        $cooldownKey = "otp-cooldown:{$type}:{$email}";
        $maxKey = "otp-max:{$type}:{$email}";

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);
            throw ValidationException::withMessages([
                'otp' => [__('messages.otp_cooldown', ['seconds' => $seconds])],
            ]);
        }

        if (RateLimiter::tooManyAttempts($maxKey, 3)) {
            $seconds = RateLimiter::availableIn($maxKey);
            $minutes = ceil($seconds / 60);
            throw ValidationException::withMessages([
                'otp' => [__('messages.otp_limit_reached', ['minutes' => $minutes])],
            ]);
        }
    }

    protected function incrementRateLimit(string $email, string $type): void
    {
        $cooldownKey = "otp-cooldown:{$type}:{$email}";
        $maxKey = "otp-max:{$type}:{$email}";

        RateLimiter::hit($cooldownKey, 60);
        RateLimiter::hit($maxKey, 600);
    }

    public function verifyOtp(Creator $creator, string $email, string $type, string $otp): void
    {
        $record = $this->otpRepositoryInterface->findActiveOtp($email, $type);

        if (!$record) {
            throw new OtpInvalidException('OTP not found or expired');
        }

        if ($record->is_used) {
            throw new OtpUsedException('OTP already used');
        }

        if ($record->expires_at < now()) {
            throw new OtpExpiredException('OTP expired');
        }

        if ($record->attempts >= $record->max_attempts) {
            throw new OtpBlockedException('Too many attempts');
        }

        if ($record->otp !== $otp) {
            $this->otpRepositoryInterface->incrementAttempts($record->id);

            throw new OtpInvalidException('Invalid OTP');
        }

        if ($type === 'email_verification') {
            $creator->update([
                'email_verified_at' => now(),
            ]);
            $this->otpRepositoryInterface->delete($record->id);
        } else {
            $this->otpRepositoryInterface->markAsUsed($record->id);
        }
    }

    public function deleteOtp(string $email, string $type): void
    {
        $record = \App\Models\Otp::where('email', $email)->where('type', $type)->first();
        if ($record) {
            $this->otpRepositoryInterface->delete($record->id);
        }
    }
    public function isOtpVerified(string $email, string $type, string $otp): void
    {
        $record = $this->otpRepositoryInterface->findOtp($email, $type, $otp);

        if (!$record || !$record->is_used) {
            throw ValidationException::withMessages([
                'otp' => [__('messages.otp_not_verified')],
            ]);
        }
    }

    public function create(OtpDTO $dto): void
    {
        $this->otpRepositoryInterface->upsert(
            [
                'email' => $dto->email,
                'type' => $dto->type,
            ],
            [
                'otp' => $dto->otp,
                'is_used' => false,
                'attempts' => 0,
                'max_attempts' => 5,
                'expires_at' => $dto->expires_at,
            ]
        );
    }

    public function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }
}
