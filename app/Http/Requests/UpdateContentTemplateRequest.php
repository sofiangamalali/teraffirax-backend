<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TemplateValidationService;
use Illuminate\Validation\Rule;
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
            'social_integration_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('social_integrations', 'id')->where(function ($query) {
                    return $query->where('creator_id', $this->user()->id);
                }),
            ],
            'name' => 'sometimes|required|string',
            'content' => [
                'sometimes',
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    try {
                        $integrationId = $this->social_integration_id ?: $this->route('content_template')->social_integration_id;
                        if ($integrationId) {
                            $integration = SocialIntegration::find($integrationId);
                            if ($integration) {
                                app(TemplateValidationService::class)->validate($integration->platform, $value);
                            }
                        }
                    } catch (\InvalidArgumentException $e) {
                        $fail($e->getMessage());
                    }
                }
            ],
            'is_default' => 'boolean',
        ];
    }
}
