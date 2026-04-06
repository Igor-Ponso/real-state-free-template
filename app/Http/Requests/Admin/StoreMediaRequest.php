<?php

namespace App\Http\Requests\Admin;

use App\Models\Property;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates and authorizes media uploads for property listings.
 *
 * Authorization: checks the 'update' policy on the route-bound Property.
 * Admins can upload to any property; agents can only upload to their own.
 */
class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Property $property */
        $property = $this->route('property');

        return $this->user()->can('update', $property);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'mimes:jpeg,png,webp,pdf', 'max:10240'],
            'collection' => ['nullable', 'string', 'in:images,floor_plans'],
        ];
    }
}
