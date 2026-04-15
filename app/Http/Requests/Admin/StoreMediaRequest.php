<?php

namespace App\Http\Requests\Admin;

use App\Models\Property;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

/**
 * Validates and authorizes media uploads for property listings.
 *
 * Authorization: checks the 'update' policy on the route-bound Property.
 * Admins can upload to any property; agents can only upload to their own.
 *
 * Validation rules adapt to the `collection` parameter:
 * - `images`       — strict image rules (jpeg/png/webp, 8MB cap, min 1024x768, max 6000x6000, up to 30 total)
 * - `floor_plans`  — looser rules allowing PDFs for floor layouts (15MB cap, up to 5 total)
 *
 * Both collections reject SVG by default (XSS vector via embedded scripts) and
 * HEIC (iPhone default) to avoid server-side decode dependencies. iPhone users
 * should switch camera format to "Most Compatible" in iOS settings, or upload
 * will fail with a clear message.
 */
class StoreMediaRequest extends FormRequest
{
    /** Max images per property. Industry norm is 20–30; we cap at 30. */
    public const MAX_IMAGES = 30;

    /** Max floor plans per property. */
    public const MAX_FLOOR_PLANS = 5;

    /** Max image file size in KB (8 MB). */
    public const MAX_IMAGE_KB = 8 * 1024;

    /** Max floor plan file size in KB (15 MB — allows larger PDF plans). */
    public const MAX_FLOOR_PLAN_KB = 15 * 1024;

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
        /** @var Property $property */
        $property = $this->route('property');

        $collection = $this->input('collection', 'images');
        $isFloorPlans = $collection === 'floor_plans';

        $existing = $property->getMedia($collection)->count();
        $max = $isFloorPlans ? self::MAX_FLOOR_PLANS : self::MAX_IMAGES;
        $remaining = max(0, $max - $existing);

        return [
            'collection' => ['nullable', 'string', Rule::in(['images', 'floor_plans'])],
            'files' => ['required', 'array', 'min:1', "max:{$remaining}"],
            'files.*' => $isFloorPlans
                ? [
                    'required',
                    'file',
                    'mimes:jpeg,png,webp,pdf',
                    'mimetypes:image/jpeg,image/png,image/webp,application/pdf',
                    'max:'.self::MAX_FLOOR_PLAN_KB,
                ]
                : [
                    'required',
                    File::image()
                        ->max(self::MAX_IMAGE_KB)
                        ->dimensions(
                            Rule::dimensions()
                                ->minWidth(1024)
                                ->minHeight(768)
                                ->maxWidth(6000)
                                ->maxHeight(6000)
                        ),
                    'mimes:jpeg,png,webp',
                    'mimetypes:image/jpeg,image/png,image/webp',
                ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'files.max' => 'You can upload at most :max file(s) — remaining slots on this property.',
            'files.*.max' => 'Each file must be under '.self::MAX_IMAGE_KB / 1024 .'MB.',
            'files.*.mimes' => 'Allowed formats: JPEG, PNG, WebP (and PDF for floor plans). HEIC and SVG are not supported.',
            'files.*.mimetypes' => 'File content does not match its extension.',
            'files.*.dimensions' => 'Images must be between 1024x768 and 6000x6000 pixels.',
        ];
    }
}
