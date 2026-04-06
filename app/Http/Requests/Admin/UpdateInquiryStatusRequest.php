<?php

namespace App\Http\Requests\Admin;

use App\Models\Inquiry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates and authorizes inquiry status changes from the admin panel.
 *
 * Authorization: checks the 'update' policy on the route-bound Inquiry.
 * Admins can update any inquiry; agents can only update inquiries on their own properties.
 */
class UpdateInquiryStatusRequest extends FormRequest
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
            'inquiry_status_id' => ['required', 'integer', 'exists:inquiry_statuses,id'],
        ];
    }
}
