<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates geocoding requests from the admin property location form.
 *
 * Any authenticated admin or agent can geocode addresses.
 */
class GeocodingRequest extends FormRequest
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
            'address' => ['required', 'string', 'max:255'],
            'city_id' => ['nullable', 'integer'],
            'state' => ['nullable', 'string', 'max:10'],
        ];
    }
}
