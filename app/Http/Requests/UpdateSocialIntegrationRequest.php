<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PlatformEnum;
use App\Enums\IntegrationTypeEnum;
use Illuminate\Validation\Rules\Enum;

class UpdateSocialIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['sometimes', 'required', new Enum(PlatformEnum::class)],
            'type' => ['sometimes', 'required', new Enum(IntegrationTypeEnum::class)],
            'external_id' => 'nullable|string',
            'name' => 'nullable|string',
            'username' => 'nullable|string',
            'access_token' => 'nullable|string',
            'metadata' => 'nullable|array',
        ];
    }
}
