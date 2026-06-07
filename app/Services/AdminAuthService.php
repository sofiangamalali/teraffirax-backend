<?php

namespace App\Services;

use App\Models\Admin;
use App\DTOs\AdminLoginDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Models\PersonalAccessToken;

class AdminAuthService
{
    public function __construct(
        protected AdminRepositoryInterface $adminRepository,
    ) {}

    /**
     * Authenticate admin and return session tokens.
     */
    public function login(AdminLoginDTO $dto): array
    {
        $admin = $this->adminRepository->findByEmail($dto->email);

        if (!$admin || !Hash::check($dto->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        return $this->issueTokens($admin);
    }

    /**
     * Issue Access and Refresh tokens for an admin.
     */
    protected function issueTokens(Admin $admin): array
    {
        // Issue Access Token (valid for 60 minutes)
        $accessToken = $admin->createToken('access_token', ['access'], now()->addMinutes(60));

        // Issue Refresh Token (valid for 30 days)
        $refreshToken = $admin->createToken('refresh_token', ['refresh'], now()->addDays(30));

        return [
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'email' => $admin->email,
            'name' => $admin->name,
        ];
    }

    /**
     * Log out admin by deleting current access token.
     */
    public function logout(Admin $admin): void
    {
        if ($admin->currentAccessToken()) {
            $admin->currentAccessToken()->delete();
        }
    }
}
