<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Section 17 form fields — required core fields plus optional UAE-specific
 * fields (TRN, Emirate, required delivery date).
 */
class StoreQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
            'delivery_location' => ['nullable', 'string', 'max:255'],
            'trn' => ['nullable', 'string', 'max:50'],
            'emirate' => ['nullable', 'string', 'max:50'],
            'required_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
