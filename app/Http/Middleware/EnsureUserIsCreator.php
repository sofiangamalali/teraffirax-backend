<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Creator;

class EnsureUserIsCreator
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !($request->user() instanceof Creator)) {
            return response()->json(['message' => __('messages.unauthorized')], 403);
        }

        return $next($request);
    }
}
