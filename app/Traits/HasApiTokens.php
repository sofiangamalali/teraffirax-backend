<?php

namespace App\Traits;

use App\Models\PersonalAccessToken;
use Illuminate\Support\Str;

trait HasApiTokens
{
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function createToken(string $name, array $abilities = ['*'], $expiresAt = null)
    {
        $token = Str::random(64);

        $accessToken = $this->tokens()->create([
            'name'       => $name,
            'token'      => hash('sha256', $token),
            'abilities'  => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return (object) [
            'accessToken' => $accessToken,
            'plainTextToken' => $token,
        ];
    }

    public function currentAccessToken()
    {
        return $this->accessToken;
    }

    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }
}
