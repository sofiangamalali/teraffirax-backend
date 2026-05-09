<?php

namespace App\Http\Requests;

use App\Models\CreatorWithdrawMethod;
use Illuminate\Foundation\Http\FormRequest;

class SetDefaultWithdrawMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $methodId = $this->route('id');
        $method = CreatorWithdrawMethod::find($methodId);

        return $method && $method->creator_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
