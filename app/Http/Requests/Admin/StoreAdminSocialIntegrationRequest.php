<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PlatformEnum;
use App\Enums\IntegrationTypeEnum;
use App\Enums\SocialIntegrationStatusEnum;
use Illuminate\Validation\Rules\Enum;

class StoreAdminSocialIntegrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', new Enum(PlatformEnum::class)],
            'type' => ['required', new Enum(IntegrationTypeEnum::class)],
            'status' => ['required', new Enum(SocialIntegrationStatusEnum::class)],
            'external_id' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'access_token' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
