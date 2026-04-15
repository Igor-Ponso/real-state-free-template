<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAnyRole(['admin', 'agent']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:32'],
            'license_number' => ['nullable', 'string', 'max:64'],
            'specializations' => ['nullable', 'array', 'max:10'],
            'specializations.*' => ['string', 'max:64'],
            'social_links' => ['nullable', 'array'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'social_links.instagram' => ['nullable', 'url', 'max:255'],
            'social_links.twitter' => ['nullable', 'url', 'max:255'],
            'social_links.facebook' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * Cast empty arrays sent as `null` by the frontend back into arrays so
     * the database column gets a consistent shape.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'specializations' => $this->input('specializations') ?? [],
            'social_links' => $this->input('social_links') ?? [],
        ]);
    }
}
