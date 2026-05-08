<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait FormatsDatesWithTimezone
{
    protected function formatWithTimezone($date, Request $request): ?string
    {
        if (!$date) {
            return null;
        }

        $timezone = $request->user()?->country?->timezone ?? 'UTC';

        return $date->timezone($timezone)->toDateTimeString();
    }
}
