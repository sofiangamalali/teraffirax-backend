<?php

namespace App\Services;

use App\Models\Creator;
use App\DTOs\LoginDTO;
use App\DTOs\CreatorDTO;
use App\DTOs\ResetPasswordWithOtpDTO;
use App\Events\CreatorRegistered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\OtpService;
use App\Models\PersonalAccessToken;

class AuthService
{
    public function __construct(
        protected CreatorRepositoryInterface $creatorRepository,
        protected CountryRepositoryInterface $countryRepository,
        protected CategoryRepositoryInterface $categoryRepository,
        protected OtpService $otpService,
    ) {
    }

    public function getRegistrationData(): array
    {
        return [
            'countries' => $this->countryRepository->all(),
            'categories' => $this->categoryRepository->all(),
        ];
    }

    public function register(CreatorDTO $dto): Creator
    {
        return DB::transaction(function () use ($dto) {
            $creator = $this->creatorRepository->create($dto->toArray());

            if (!empty($dto->categories)) {
                $creator->categories()->sync($dto->categories);
            }
            event(new CreatorRegistered($creator));
            return $creator;
        });
    }

    public function login(LoginDTO $dto): array
    {
        $creator = $this->creatorRepository->findByEmail($dto->identifier)
            ?? $this->creatorRepository->findByPhone($dto->identifier);

        if (!$creator || !Hash::check($dto->password, $creator->password)) {
            throw ValidationException::withMessages([
                'identifier' => [__('auth.failed')],
            ]);
        }

        return $this->issueTokens($creator);
    }

    /**
     * Issue Access and Refresh tokens for a creator.
     */
    protected function issueTokens(Creator $creator): array
    {
        // Issue Access Token (valid for 60 minutes)
        $accessToken = $creator->createToken('access_token', ['access'], now()->addMinutes(60));

        // Issue Refresh Token (valid for 30 days)
        $refreshToken = $creator->createToken('refresh_token', ['refresh'], now()->addDays(30));

        return [
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
        ];
    }

    public function logout(Creator $creator): void
    {
        if ($creator->currentAccessToken()) {
            $creator->currentAccessToken()->delete();
        }
    }

    public function forgotPassword(string $email): string
    {
        $creator = $this->creatorRepository->findByEmail($email);
        if (!$creator) {
            throw ValidationException::withMessages(['email' => [__('passwords.user')]]);
        }

        $this->otpService->sendPasswordResetOtp($creator);

        return 'messages.otp_sent';
    }

    public function verifyOtp(string $email, string $type, string $otp): void
    {
        $creator = $this->creatorRepository->findByEmail($email);
        if (!$creator) {
            throw ValidationException::withMessages(['email' => [__('passwords.user')]]);
        }
        if ($type === 'email_verification' && $creator->email_verified_at) {
            throw ValidationException::withMessages(['email' => [__('messages.email_already_verified')]]);
        }
        $this->otpService->verifyOtp($creator, $email, $type, $otp);
    }

    public function resetPasswordWithOtp(ResetPasswordWithOtpDTO $dto): void
    {
        $creator = $this->creatorRepository->findByEmail($dto->email);
        if (!$creator) {
            throw ValidationException::withMessages(['email' => [__('passwords.user')]]);
        }

        $this->otpService->isOtpVerified($dto->email, 'password_reset', $dto->otp);

        $creator->update([
            'password' => $dto->password,
        ]);

        $this->otpService->deleteOtp($dto->email, 'password_reset');

        $creator->tokens()->delete();
    }

    public function resendOtp(string $email, string $type): string
    {
        $creator = $this->creatorRepository->findByEmail($email);
        if (!$creator) {
            throw ValidationException::withMessages(['email' => [__('passwords.user')]]);
        }
        if ($type === 'email_verification') {
            if ($creator->email_verified_at) {
                throw ValidationException::withMessages(['email' => [__('messages.email_already_verified')]]);
            }
            $this->otpService->sendEmailVerificationOtp($creator);
        } else {
            $this->otpService->sendPasswordResetOtp($creator);
        }

        return 'messages.otp_sent';
    }

    public function refreshToken(string $refreshTokenText): array
    {
        $token = PersonalAccessToken::findToken($refreshTokenText);

        if (!$token || !in_array('refresh', $token->abilities ?? []) || ($token->expires_at && $token->expires_at->isPast())) {
            throw ValidationException::withMessages(['refresh_token' => ['Invalid or expired refresh token.']]);
        }

        $creator = $token->tokenable;

        $token->delete();

        return $this->issueTokens($creator);
    }




}
