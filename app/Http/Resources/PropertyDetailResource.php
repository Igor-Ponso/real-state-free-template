<?php

namespace App\Http\Resources;

use App\Models\Property;
use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Property model into the full data structure for the detail page.
 *
 * Includes all property fields, formatted prices, agent profile, media URLs,
 * amenities, features, and rental-specific details. Uses `whenLoaded()` for
 * conditional relationship data.
 *
 * @mixin Property
 */
class PropertyDetailResource extends JsonResource
{
    /** Reuse local placeholder images from FeaturedPropertyResource. */

    /**
     * Unique avatar seeds per agent — each produces a distinct face via pravatar.cc.
     *
     * @var int[]
     */
    private const AVATAR_SEEDS = [3, 5, 8, 12, 16, 25, 32, 36, 41, 49];

    /**
     * Transform the Property model into the detail page payload.
     *
     * @return array{
     *     id: int,
     *     title: string,
     *     slug: string,
     *     description: string,
     *     price: string,
     *     currency: string,
     *     listing_type: string|null,
     *     listing_type_slug: string|null,
     *     property_type: string|null,
     *     property_status: string|null,
     *     address: string,
     *     city: array{name: string, state: string, slug: string}|null,
     *     neighborhood: string|null,
     *     state: string,
     *     zip_code: string,
     *     latitude: string|null,
     *     longitude: string|null,
     *     bedrooms: int,
     *     bathrooms: float,
     *     area_sqft: string,
     *     lot_size_sqft: string|null,
     *     year_built: int|null,
     *     parking_spaces: int,
     *     floor: int|null,
     *     total_floors: int|null,
     *     unit_amenities: string[],
     *     building_amenities: string[],
     *     features: array<string, mixed>,
     *     deposit: string|null,
     *     lease_length_months: int|null,
     *     available_from: string|null,
     *     pets_allowed: bool,
     *     is_rental: bool,
     *     meta_title: string|null,
     *     meta_description: string|null,
     *     images: string[],
     *     floor_plans: string[],
     *     agent: array{id: int, name: string, bio: string|null, specializations: string[], avatar: string, social_links: array<string, string>|null}|null,
     *     published_at: string|null,
     * }
     */
    public function toArray(Request $request): array
    {
        $isRental = $this->whenLoaded('listingType', fn () => $this->listingType->slug === 'rental', false);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->formatPrice($this->price),
            'currency' => $this->currency,
            'listing_type' => $this->whenLoaded('listingType', fn () => $this->listingType->name),
            'listing_type_slug' => $this->whenLoaded('listingType', fn () => $this->listingType->slug),
            'property_type' => $this->whenLoaded('propertyType', fn () => $this->propertyType->name),
            'property_status' => $this->whenLoaded('propertyStatus', fn () => $this->propertyStatus->name),
            'address' => $this->address,
            'city' => $this->whenLoaded('city', fn () => [
                'name' => $this->city->name,
                'state' => $this->city->state,
                'slug' => $this->city->slug,
            ]),
            'neighborhood' => $this->neighborhood,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => (float) $this->bathrooms,
            'area_sqft' => number_format($this->area_sqft),
            'lot_size_sqft' => $this->lot_size_sqft ? number_format($this->lot_size_sqft) : null,
            'year_built' => $this->year_built,
            'parking_spaces' => $this->parking_spaces,
            'floor' => $this->floor,
            'total_floors' => $this->total_floors,
            'unit_amenities' => $this->unit_amenities ?? [],
            'building_amenities' => $this->building_amenities ?? [],
            'features' => $this->features ?? [],
            'deposit' => $this->deposit ? $this->formatPrice($this->deposit) : null,
            'lease_length_months' => $this->lease_length_months,
            'available_from' => $this->available_from?->format('Y-m-d'),
            'pets_allowed' => $this->pets_allowed,
            'is_rental' => $isRental,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'images' => $this->getPropertyImages(),
            'floor_plans' => $this->getFloorPlans(),
            'agent' => $this->whenLoaded('agent', fn () => $this->formatAgent()),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }

    /**
     * Convert a Brick\Money price into a formatted dollar string (e.g. "$2,450,000").
     */
    private function formatPrice(Money $money): string
    {
        $dollars = intdiv($money->getMinorAmount()->toInt(), 100);

        return '$'.number_format($dollars);
    }

    /**
     * Get property images from MediaLibrary or fall back to placeholders.
     *
     * @return string[]
     */
    private function getPropertyImages(): array
    {
        $media = $this->getMedia('images');

        if ($media->isNotEmpty()) {
            return $media->map(fn ($item) => $item->getUrl())->all();
        }

        if ($this->features['_no_images'] ?? false) {
            return [];
        }

        if (! app()->environment('local', 'testing') && ! config('demo.show_placeholder_images')) {
            return [];
        }

        $images = [
            '/images/properties/penthouse.jpg',
            '/images/properties/penthouse-2.jpg',
            '/images/properties/villa.jpg',
            '/images/properties/villa-2.jpg',
            '/images/properties/loft.jpg',
            '/images/properties/loft-2.jpg',
        ];
        $count = count($images);

        return collect(range(0, 4))
            ->map(fn ($i) => $images[($this->id * 5 + $i) % $count])
            ->all();
    }

    /**
     * Get floor plan URLs from MediaLibrary.
     *
     * @return string[]
     */
    private function getFloorPlans(): array
    {
        return $this->getMedia('floor_plans')
            ->map(fn ($item) => $item->getUrl())
            ->all();
    }

    /**
     * Format the agent (User + AgentProfile) into the detail page payload.
     *
     * @return array{id: int, name: string, bio: string|null, specializations: string[], avatar: string, social_links: array<string, string>|null}
     */
    private function formatAgent(): array
    {
        $agent = $this->agent;
        $profile = $agent->agentProfile;
        $seed = self::AVATAR_SEEDS[$agent->id % count(self::AVATAR_SEEDS)];

        return [
            'id' => $agent->id,
            'name' => $agent->name,
            'bio' => $profile?->bio,
            'specializations' => $profile?->specializations ?? [],
            'avatar' => "https://i.pravatar.cc/400?img={$seed}",
            'social_links' => $profile?->social_links,
        ];
    }
}
