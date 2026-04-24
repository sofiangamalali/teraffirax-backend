<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTelegramChannelRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'creator_id' => 'nullable|exists:creators,id',
            'name' => 'required|string|max:255',
            'telegram_id' => 'required|string|unique:telegram_channels,telegram_id',
            'link' => 'required|url'
        ];
    }
}
