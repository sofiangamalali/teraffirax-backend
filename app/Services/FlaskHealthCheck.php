<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FlaskHealthCheck
{
    public static function isAlive(): bool
    {
        try {
            $response = Http::timeout(8)->get(
                config('services.flask.base_url') . '/health'
            );
            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }
}
