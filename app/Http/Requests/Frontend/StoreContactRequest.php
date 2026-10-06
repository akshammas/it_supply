<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public const ENQUIRY_TYPES = [
        'Product enquiry',
        'Price / quotation',
        'Bulk or project order',
        'Technical support',
        'Partnership',
        'Other',
    ];

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
            'phone' => ['nullable', 'string', 'max:30'],
            'enquiry_type' => ['nullable', Rule::in(self::ENQUIRY_TYPES)],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    /** After a failed validation, return to the form itself rather than the top of the page. */
    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl().'#contact-form';
    }
}