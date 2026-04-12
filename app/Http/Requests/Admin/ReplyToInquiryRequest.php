<?php

namespace App\Http\Requests\Admin;

use App\Models\Inquiry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates and authorizes replies to inquiries.
 *
 * Admins can reply to any inquiry; agents can only reply to inquiries on their own properties.
 */
class ReplyToInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Inquiry $inquiry */
        $inquiry = $this->route('inquiry');

        return $this->user()->can('update', $inquiry);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
