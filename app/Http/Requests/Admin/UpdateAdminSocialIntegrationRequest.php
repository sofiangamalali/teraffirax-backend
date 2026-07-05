<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\SocialIntegrationStatusEnum;
use Illuminate\Validation\Rules\Enum;

class UpdateAdminSocialIntegrationRequest extends FormRequest
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
            'status' => ['sometimes', 'required', new Enum(SocialIntegrationStatusEnum::class)],
            'name' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
        ];
    }
}
