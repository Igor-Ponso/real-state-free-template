<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Normalize single-value params to arrays before validation.
     * Supports both ?city=vancouver and ?city[]=vancouver&city[]=toronto.
     */
    protected function prepareForValidation(): void
    {
        foreach (['type', 'city', 'listing', 'bedrooms', 'unit_amenities', 'building_amenities'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => [$this->input($field)]]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:100'],
            'type' => ['sometimes', 'array'],
            'type.*' => ['string', 'max:50'],
            'city' => ['sometimes', 'array'],
            'city.*' => ['string', 'max:50'],
            'listing' => ['sometimes', 'array'],
            'listing.*' => ['string', 'max:50'],
            'bedrooms' => ['sometimes', 'array'],
            'bedrooms.*' => ['integer', 'min:1', 'max:10'],
            'unit_amenities' => ['sometimes', 'array'],
            'unit_amenities.*' => ['string', 'max:50'],
            'building_amenities' => ['sometimes', 'array'],
            'building_amenities.*' => ['string', 'max:50'],
            'min_price' => ['sometimes', 'integer', 'min:0'],
            'max_price' => ['sometimes', 'integer', 'min:0'],
            'sort' => ['sometimes', 'string', 'in:newest,oldest,price_asc,price_desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:48'],
        ];
    }
}
