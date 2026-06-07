<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => __('messages.unauthenticated')], 401);
        }

        $accessToken = PersonalAccessToken::where('token', hash('sha256', $token))->first();

        if (!$accessToken || ($accessToken->expires_at && $accessToken->expires_at->isPast())) {
            return response()->json(['message' => __('messages.unauthenticated')], 401);
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        $user = $accessToken->tokenable;
        if (method_exists($user, 'withAccessToken')) {
            $user->withAccessToken($accessToken);
        }

        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
