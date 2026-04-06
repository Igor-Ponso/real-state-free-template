<?php

namespace App\Services;

use App\Models\City;
use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Encapsulates property query building with filters, sorting, and pagination.
 *
 * Extracts complex query logic from PropertyController to follow
 * Single Responsibility — controller orchestrates, service queries.
 */
class PropertyQueryService
{
    /**
     * Build a filtered, sorted, paginated query for property listings.
     *
     * @param  array<string, mixed>  $filters
     */
    public function filteredListing(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->applyFilters(
            Property::query()->with(['city', 'listingType', 'propertyType', 'media'])->published(),
            $filters,
        )
            ->when(
                $filters['sort'] ?? null,
                fn ($q, $sort) => match ($sort) {
                    'price_asc' => $q->orderBy('price'),
                    'price_desc' => $q->orderByDesc('price'),
                    'oldest' => $q->oldest('published_at'),
                    default => $q->latest('published_at'),
                },
                fn ($q) => $q->latest('published_at'),
            )
            ->paginate(max(1, min($perPage, 48)))
            ->withQueryString();
    }

    /**
     * Get similar properties in the same city, excluding the given property.
     */
    public function similarProperties(Property $property, int $limit = 4): Collection
    {
        return Property::query()
            ->with(['city', 'listingType', 'propertyType', 'media'])
            ->published()
            ->where('city_id', $property->city_id)
            ->where('id', '!=', $property->id)
            ->limit($limit)
            ->latest('published_at')
            ->get();
    }

    /**
     * Apply all filter conditions to a query builder.
     *
     * @param  Builder<Property>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Property>
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['type'] ?? null, fn ($q, $slugs) => $q->whereHas('propertyType', fn ($q) => $q->whereIn('slug', (array) $slugs)))
            ->when($filters['city'] ?? null, fn ($q, $slugs) => $q->whereHas('city', fn ($q) => $q->whereIn('slug', (array) $slugs)))
            ->when($filters['listing'] ?? null, fn ($q, $slugs) => $q->whereHas('listingType', fn ($q) => $q->whereIn('slug', (array) $slugs)))
            ->when($filters['min_price'] ?? null, fn ($q, $min) => $q->where('price', '>=', (int) $min))
            ->when($filters['max_price'] ?? null, fn ($q, $max) => $q->where('price', '<=', (int) $max))
            ->when($filters['bedrooms'] ?? null, fn ($q) => ($filters['bedrooms_exact'] ?? false)
                ? $q->whereIn('bedrooms', array_map('intval', (array) $filters['bedrooms']))
                : $q->where('bedrooms', '>=', (int) min((array) $filters['bedrooms'])))
            ->when($filters['bathrooms'] ?? null, fn ($q) => ($filters['bathrooms_exact'] ?? false)
                ? $q->whereIn('bathrooms', array_map('floatval', (array) $filters['bathrooms']))
                : $q->where('bathrooms', '>=', (float) min((array) $filters['bathrooms'])))
            ->when($filters['unit_amenities'] ?? null, function ($q) use ($filters) {
                foreach ((array) $filters['unit_amenities'] as $amenity) {
                    $q->whereJsonContains('unit_amenities', $amenity);
                }
            })
            ->when($filters['building_amenities'] ?? null, function ($q) use ($filters) {
                foreach ((array) $filters['building_amenities'] as $amenity) {
                    $q->whereJsonContains('building_amenities', $amenity);
                }
            })
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->searchByTitle($search));
    }

    /**
     * Resolve map center coordinates from the first selected city.
     *
     * @param  string[]  $citySlugs
     * @return array{0: float, 1: float}|null
     */
    public function resolveMapCenter(array $citySlugs): ?array
    {
        if (empty($citySlugs)) {
            return null;
        }

        $city = City::where('slug', $citySlugs[0])->first(['latitude', 'longitude']);

        if (! $city) {
            return null;
        }

        return [(float) $city->latitude, (float) $city->longitude];
    }
}
