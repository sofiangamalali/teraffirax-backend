<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateScraper
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');

        if ($token !== 'Bearer ' . config('services.scraper.token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
