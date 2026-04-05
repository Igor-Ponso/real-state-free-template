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
     * Fallback image sets cycled by property ID when no media is uploaded.
     * Each entry is a pair of images (primary + secondary) for the carousel.
     *
     * @var array<int, string[]>
     */
    private const PLACEHOLDER_IMAGES = [
        ['/images/properties/penthouse.jpg', '/images/properties/penthouse-2.jpg'],
        ['/images/properties/villa.jpg', '/images/properties/villa-2.jpg'],
        ['/images/properties/loft.jpg', '/images/properties/loft-2.jpg'],
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
     * @return string[]
     */
    private function getPropertyImages(): array
    {
        $media = $this->getMedia('images');

        if ($media->isNotEmpty()) {
            return $media->map(fn ($item) => $item->getUrl())->all();
        }

        return self::PLACEHOLDER_IMAGES[$this->id % count(self::PLACEHOLDER_IMAGES)];
    }
}
