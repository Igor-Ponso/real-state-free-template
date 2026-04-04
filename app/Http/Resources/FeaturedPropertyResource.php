<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturedPropertyResource extends JsonResource
{
    private const PLACEHOLDER_IMAGES = [
        ['/images/properties/penthouse.jpg', '/images/properties/penthouse-2.jpg'],
        ['/images/properties/villa.jpg', '/images/properties/villa-2.jpg'],
        ['/images/properties/loft.jpg', '/images/properties/loft-2.jpg'],
    ];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
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
            'images' => $this->getPropertyImages(),
        ];
    }

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
