<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TemplateValidationService;
use Illuminate\Validation\Rule;
use App\Enums\PlatformEnum;

class UpdateContentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => [
                'sometimes',
                'required',
                'string',
                Rule::enum(PlatformEnum::class),
            ],
            'name' => 'sometimes|required|string',
            'content' => [
                'sometimes',
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    try {
                        $platform = $this->platform ?: $this->route('content_template')->platform;
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
