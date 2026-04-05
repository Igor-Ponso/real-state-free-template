<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates inquiry submissions from the property detail page.
 *
 * Open to guests (no auth required). Includes a honeypot field
 * for basic spam prevention — bots that fill it trigger a silent discard.
 */
class StoreInquiryRequest extends FormRequest
{
    /**
     * Guest inquiries are allowed — no authentication required.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'honeypot' => ['nullable', 'string'],
        ];
    }
}
