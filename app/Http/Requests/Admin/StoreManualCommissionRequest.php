<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualCommissionRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'track_id' => ['required', 'string', 'exists:creator_vendor_tracking,track_id'],
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'earned_at' => ['required', 'date', 'before_or_equal:today'],
            'revenue_amount' => ['required', 'numeric', 'min:0'],
            'total_commission' => ['required', 'numeric', 'min:0.01'],
            'clicks_count' => ['nullable', 'integer', 'min:0'],
            'items_count' => ['nullable', 'integer', 'min:0'],
            'returned_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
