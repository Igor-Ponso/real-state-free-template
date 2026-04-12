<?php

namespace App\Http\Requests\Admin;

use App\Models\Property;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates and authorizes media reordering for property listings.
 *
 * Admins can reorder any property's media; agents can only reorder their own.
 */
class ReorderMediaRequest extends FormRequest
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
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ];
    }
}
