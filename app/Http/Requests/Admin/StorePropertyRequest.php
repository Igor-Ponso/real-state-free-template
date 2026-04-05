<?php

namespace App\Http\Requests\Admin;

use App\Models\Property;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates property creation from the admin panel.
 */
class StorePropertyRequest extends FormRequest
{
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50'],
            'property_type_id' => ['required', 'integer', 'exists:property_types,id'],
            'listing_type_id' => ['required', 'integer', 'exists:listing_types,id'],
            'property_status_id' => ['required', 'integer', 'exists:property_statuses,id'],
            'price' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'address' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:10'],
            'zip_code' => ['required', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['required', 'numeric', 'min:0', 'max:20'],
            'area_sqft' => ['required', 'integer', 'min:1'],
            'lot_size_sqft' => ['nullable', 'integer', 'min:0'],
            'year_built' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'parking_spaces' => ['required', 'integer', 'min:0'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'total_floors' => ['nullable', 'integer', 'min:0'],
            'unit_amenities' => ['nullable', 'array'],
            'unit_amenities.*' => ['string', 'in:'.implode(',', Property::UNIT_AMENITIES)],
            'building_amenities' => ['nullable', 'array'],
            'building_amenities.*' => ['string', 'in:'.implode(',', Property::BUILDING_AMENITIES)],
            'deposit' => ['nullable', 'integer', 'min:0'],
            'lease_length_months' => ['nullable', 'integer', 'min:1'],
            'available_from' => ['nullable', 'date'],
            'pets_allowed' => ['boolean'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }
}
