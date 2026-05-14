<?php

namespace App\Http\Requests;

use App\Models\CreatorWithdrawMethod;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateCreatorWithdrawMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $methodId = $this->route('my_withdraw_method');
        $method = CreatorWithdrawMethod::find($methodId);
        if (!$method) {
            throw new NotFoundHttpException();
        }

        return $method->creator_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

        ];
    }
}
