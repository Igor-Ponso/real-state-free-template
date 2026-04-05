<?php

namespace App\Http\Resources\Admin;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin property resource — includes management fields not shown publicly.
 *
 * @mixin Property
 */
class PropertyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->formatPrice(),
            'price_raw' => $this->price->getMinorAmount()->toInt(),
            'currency' => $this->currency,
            'address' => $this->address,
            'city' => $this->whenLoaded('city', fn () => $this->city->name),
            'city_id' => $this->city_id,
            'neighborhood' => $this->neighborhood,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => (float) $this->bathrooms,
            'area_sqft' => $this->area_sqft,
            'lot_size_sqft' => $this->lot_size_sqft,
            'year_built' => $this->year_built,
            'parking_spaces' => $this->parking_spaces,
            'floor' => $this->floor,
            'total_floors' => $this->total_floors,
            'unit_amenities' => $this->unit_amenities ?? [],
            'building_amenities' => $this->building_amenities ?? [],
            'deposit' => $this->deposit?->getMinorAmount()->toInt(),
            'lease_length_months' => $this->lease_length_months,
            'available_from' => $this->available_from?->format('Y-m-d'),
            'pets_allowed' => $this->pets_allowed,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'property_type' => $this->whenLoaded('propertyType', fn () => $this->propertyType->name),
            'property_type_id' => $this->property_type_id,
            'listing_type' => $this->whenLoaded('listingType', fn () => $this->listingType->name),
            'listing_type_id' => $this->listing_type_id,
            'status' => $this->whenLoaded('propertyStatus', fn () => $this->propertyStatus->name),
            'status_slug' => $this->whenLoaded('propertyStatus', fn () => $this->propertyStatus->slug),
            'property_status_id' => $this->property_status_id,
            'is_published' => $this->is_published,
            'is_featured' => $this->is_featured,
            'inquiries_count' => $this->whenCounted('inquiries'),
            'images' => $this->getMedia('images')->map(fn ($m) => $m->getUrl())->toArray(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    private function formatPrice(): string
    {
        $dollars = intdiv($this->price->getMinorAmount()->toInt(), 100);

        return '$'.number_format($dollars);
    }
}
