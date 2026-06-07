<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'platform' => ['required', 'string', 'in:telegram,tiktok,instagram,youtube,facebook'],
            'type' => ['required', 'string', 'in:channel,group,profile,page'],
            'status' => ['required', 'string', 'in:active,inactive,pending'],
            'external_id' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'access_token' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
