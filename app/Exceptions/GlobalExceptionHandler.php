<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use App\Http\Resources\TrackingLinkResource;
use Illuminate\Validation\ValidationException;
use App\Exceptions\DuplicateTrackingLinkException;
use Illuminate\Foundation\Configuration\Exceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Exceptions\Otp\{OtpExpiredException, OtpInvalidException, OtpBlockedException, OtpUsedException};
use App\Exceptions\UnsupportedVendorException;
use App\Exceptions\UnsupportedTrackingGeneratorException;
use App\Exceptions\CreatorNotSubscribedToVendorException;

class GlobalExceptionHandler
{
    public static function handle(Exceptions $exceptions): void
    {
        $exceptions->render(function (DuplicateTrackingLinkException $e, Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => $e->getMessage(),
                'data' => new TrackingLinkResource($e->getTrackingLink()),
            ], 200);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.not_found'),
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.method_not_allowed'),
            ], 405);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.unauthorized'),
            ], 403);
        });

        $exceptions->render(function (OtpExpiredException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => __('otp.expired'),
            ], 400);
        });
        $exceptions->render(function (OtpInvalidException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => __('otp.invalid'),
            ], 400);
        });
        $exceptions->render(function (OtpBlockedException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => __('otp.blocked'),
            ], 429);
        });
        $exceptions->render(function (OtpUsedException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => __('otp.used'),
            ], 400);
        });

        $exceptions->render(function (CreatorNotSubscribedToVendorException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (UnsupportedVendorException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (UnsupportedTrackingGeneratorException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (VendorNotFoundForProductException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTrace() : [],
                ], 500);
            }
        });
    }
}
