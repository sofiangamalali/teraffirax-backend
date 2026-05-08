<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TemplateValidationService;
use App\Models\SocialIntegration;

class UpdateContentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'social_integration_id' => 'sometimes|required|exists:social_integrations,id',
            'name' => 'sometimes|required|string',
            'content' => [
                'sometimes',
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    try {
                        $integrationId = $this->social_integration_id ?: $this->route('content_template')->social_integration_id;
                        $integration = SocialIntegration::find($integrationId);
                        $platform = $integration ? $integration->platform : 'telegram';
                        
                        app(TemplateValidationService::class)->validate($platform, $value);
                    } catch (\InvalidArgumentException $e) {
                        $fail($e->getMessage());
                    }
                }
            ],
            'is_default' => 'boolean',
        ];
    }
}
