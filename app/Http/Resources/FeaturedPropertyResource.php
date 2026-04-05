<?php

namespace App\Http\Resources;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Property model into the data structure used by the landing page featured properties section.
 *
 * Resolves relationships (city, listingType, propertyType) via `whenLoaded()` and formats
 * the monetary price from Brick\Money into a display-ready dollar string. Falls back to
 * placeholder images when the property has no media attached.
 *
 * @mixin Property
 */
class FeaturedPropertyResource extends JsonResource
{
    /**
     * Curated Unsplash photo IDs of real estate properties.
     * Each property picks images deterministically based on its ID.
     * In production, Spatie MediaLibrary handles real uploads.
     *
     * @see https://unsplash.com — Free, no API key required for hotlinking
     *
     * @var string[]
     */
    private const PLACEHOLDER_PHOTO_IDS = [
        'KqrbNYj7QJQ', // modern house greenery
        'r6J0hko5sQE', // luxury interior design
        'FYnqaaBOI8k', // kitchen island
        'qe0Tu9mVs7U', // luxury living room
        '8WG4EXvdM4M', // modern bathroom
        'n1OuA3zNUpA', // luxury kitchen
        'ouK1sAbIHvk', // modern living
        'r0IRSS5QujY', // villa exterior
        '_vYG0J4oL0w', // house palm trees
        '4iEuIV8_84k', // pool night
        'vbSRUrNm3Ik', // residential exterior
        'fhD2TTSWfG0', // modern building
        'prBWpfgpyjA', // modernist villa
        'ZAUHqu8Z1uU', // tall white building
        'fk3JUWP0RNA', // building sunlight
        'b08Pe9MV_eU', // modern lobby
        'Ypv0MH4izf8', // lobby seating
        'OcvPfruiEyY', // apartment buildings
    ];

    /**
     * Transform the Property model into the featured listing payload.
     *
     * @return array{
     *     id: int,
     *     title: string,
     *     slug: string,
     *     location: string|null,
     *     price: string,
     *     bedrooms: int,
     *     bathrooms: float,
     *     area_sqft: string,
     *     listing_type: string|null,
     *     property_type: string|null,
     *     latitude: string|null,
     *     longitude: string|null,
     *     images: string[],
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'location' => $this->whenLoaded('city', fn () => $this->city->name.', '.$this->city->state),
            'price' => $this->formatPrice(),
            'bedrooms' => $this->bedrooms,
            'bathrooms' => (float) $this->bathrooms,
            'area_sqft' => number_format($this->area_sqft),
            'listing_type' => $this->whenLoaded('listingType', fn () => $this->listingType->name),
            'property_type' => $this->whenLoaded('propertyType', fn () => $this->propertyType->name),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'images' => $this->getPropertyImages(),
        ];
    }

    /**
     * Convert the Brick\Money price into a formatted dollar string (e.g. "$2,450,000").
     *
     * Extracts the minor amount (cents), divides by 100, and applies number formatting
     * without decimal places for the landing page display.
     */
    private function formatPrice(): string
    {
        $dollars = intdiv($this->price->getMinorAmount()->toInt(), 100);

        return '$'.number_format($dollars);
    }

    /**
     * Expose photo IDs for reuse in PropertyDetailResource.
     *
     * @return string[]
     */
    public static function placeholderPhotoIds(): array
    {
        return self::PLACEHOLDER_PHOTO_IDS;
    }

    /**
     * @return string[]
     */
    private function getPropertyImages(): array
    {
        $media = $this->getMedia('images');

        if ($media->isNotEmpty()) {
            return $media->map(fn ($item) => $item->getUrl())->all();
        }

        $ids = self::PLACEHOLDER_PHOTO_IDS;
        $count = count($ids);

        return collect(range(0, 2))
            ->map(fn ($i) => 'https://images.unsplash.com/photo-'.$ids[($this->id * 3 + $i) % $count].'?w=800&h=500&fit=crop&auto=format')
            ->all();
    }
}
