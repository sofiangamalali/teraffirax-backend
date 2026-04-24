<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');

        // Check for common formats like 'ar,en-US;q=0.7,en;q=0.3'
        if ($locale) {
            $primaryLocale = explode(',', $locale)[0];
            $primaryLocale = explode('-', $primaryLocale)[0]; // get 'en' from 'en-US'
            
            if (in_array($primaryLocale, ['en', 'ar'])) {
                app()->setLocale($primaryLocale);
            } else {
                app()->setLocale('en');
            }
        } else {
            app()->setLocale('en');
        }

        return $next($request);
    }
}
