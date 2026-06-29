<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TemplateValidationService;
use Illuminate\Validation\Rule;
use App\Models\SocialIntegration;

class StoreContentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'social_integration_id' => [
                'required',
                'integer',
                Rule::exists('social_integrations', 'id')->where(function ($query) {
                    return $query->where('creator_id', $this->user()->id);
                }),
            ],
            'name' => 'required|string',
            'content' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    try {
                        if ($this->social_integration_id) {
                            $integration = SocialIntegration::find($this->social_integration_id);
                            if ($integration) {
                                app(TemplateValidationService::class)->validate($integration->platform->value, $value);
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
