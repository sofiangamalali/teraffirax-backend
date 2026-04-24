<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:creators,email'],
            'type'  => ['required', 'string', 'in:email_verification,password_reset'],
            'otp'   => ['required', 'string', 'size:6'],
        ];
    }
}
