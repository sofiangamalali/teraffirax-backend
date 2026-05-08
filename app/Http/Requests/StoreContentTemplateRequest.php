<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TemplateValidationService;
use Illuminate\Validation\Rule;
use App\Enums\PlatformEnum;

class StoreContentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => [
                'required',
                'string',
                Rule::enum(PlatformEnum::class),
            ],
            'name' => 'required|string',
            'content' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    try {
                        app(TemplateValidationService::class)->validate($this->platform, $value);
                    } catch (\InvalidArgumentException $e) {
                        $fail($e->getMessage());
                    }
                }
            ],
            'is_default' => 'boolean',
        ];
    }
}
