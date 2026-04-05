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
     * Number of unique placeholder images per property.
     * Each property gets deterministic images based on its ID via picsum.photos.
     */
    private const PLACEHOLDER_IMAGE_COUNT = 3;

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

        return collect(range(0, self::PLACEHOLDER_IMAGE_COUNT - 1))
            ->map(fn ($i) => "https://picsum.photos/seed/property-{$this->id}-{$i}/800/500")
            ->all();
    }
}
