<?php

namespace App\Http\Controllers\Api\Auth;

use App\DTOs\{CreatorDTO, LoginDTO, ResetPasswordWithOtpDTO};
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{ForgotPasswordRequest, LoginRequest, RegisterRequest, ResetPasswordWithOtpRequest, VerifyOtpRequest, RefreshTokenRequest, ResendOtpRequest};
use App\Http\Resources\{CountryResource, CategoryResource};
use App\Services\CreatorAuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\{JsonResponse, Request};

class CreatorAuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CreatorAuthService $authService,
    ) {
    }

    public function registrationData(): JsonResponse
    {
        $data = $this->authService->getRegistrationData();

        return $this->successResponse([
            'countries' => CountryResource::collection($data['countries']),
            'categories' => CategoryResource::collection($data['categories']),
        ], 'messages.fetched');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $creator = $this->authService->register(CreatorDTO::fromRequest($request));
        return $this->successResponse(
            null,
            'messages.created',
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(LoginDTO::fromRequest($request));

        return $this->successResponse([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'is_verified' => $data['is_verified'],
            'email' => $data['email'],
            'account_manager_id' => $data['account_manager_id'],
            'is_active' => $data['is_active'],
        ], 'messages.authenticated');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'messages.logged_out');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $message = $this->authService->forgotPassword($request->email);

        return $this->successResponse(null, $message);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $this->authService->verifyOtp($request->email, $request->type, $request->otp);

        return $this->successResponse(null, 'messages.otp_verified');
    }

    public function resetPassword(ResetPasswordWithOtpRequest $request): JsonResponse
    {
        $this->authService->resetPasswordWithOtp(ResetPasswordWithOtpDTO::fromRequest($request));

        return $this->successResponse(null, 'messages.password_reset');
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $message = $this->authService->resendOtp($request->email, $request->type);

        return $this->successResponse(null, $message);
    }

    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $data = $this->authService->refreshToken($request->refresh_token);

        return $this->successResponse([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'is_verified' => $data['is_verified'],
            'account_manager_id' => $data['account_manager_id'],
            'is_active' => $data['is_active'],
        ], 'messages.token_refreshed');
    }
}
