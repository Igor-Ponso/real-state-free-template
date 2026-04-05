<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates property updates from the admin panel.
 *
 * Authorization is handled here (not in controller) because Form Request
 * authorize() runs before validation rules — giving a 403 before 422.
 */
class UpdatePropertyRequest extends StorePropertyRequest
{
    /**
     * Authorize via PropertyPolicy — agents can only update their own properties.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('property'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return parent::rules();
    }
}
