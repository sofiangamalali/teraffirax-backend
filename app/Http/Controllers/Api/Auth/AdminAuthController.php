<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\DTOs\AdminLoginDTO;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Services\AdminAuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AdminAuthService $authService,
    ) {}

    /**
     * Authenticate admin.
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(AdminLoginDTO::fromRequest($request));

        return $this->successResponse($data, 'messages.authenticated');
    }

    /**
     * Log out admin.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'messages.logged_out');
    }
}
