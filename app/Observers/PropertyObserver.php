<?php

namespace App\Observers;

use App\Models\Property;
use Illuminate\Support\Facades\Cache;

/**
 * Handles cache invalidation when properties are created, updated, or deleted.
 *
 * Uses Redis cache tags for granular invalidation and wasChanged()
 * for conditional flushing — avoids unnecessary cache busting
 * when non-display fields change (e.g., meta_description only).
 *
 * Tag groups:
 * - `properties` — all property listing caches (landing, search, index)
 * - `property:{slug}` — specific property detail cache
 * - `filter-options` — dropdown filter options (types, cities, listings)
 */
class PropertyObserver
{
    /** Fields that affect public listing display (cards, grid, map). */
    private const DISPLAY_FIELDS = [
        'title', 'price', 'currency', 'description', 'bedrooms', 'bathrooms',
        'area_sqft', 'address', 'city_id', 'is_published', 'is_featured', 'published_at',
        'property_status_id', 'listing_type_id', 'property_type_id',
        'latitude', 'longitude',
    ];

    /** Fields that affect filter dropdown options. */
    private const FILTER_FIELDS = ['city_id', 'property_type_id', 'listing_type_id'];

    public function created(Property $property): void
    {
        Cache::tags(['properties'])->flush();
        Cache::forget('home_stats');
    }

    public function updated(Property $property): void
    {
        // Always invalidate the specific property detail cache
        Cache::tags(["property:{$property->slug}"])->flush();

        // Only flush listing caches when display-relevant fields changed
        if ($property->wasChanged(self::DISPLAY_FIELDS)) {
            Cache::tags(['properties'])->flush();
            Cache::forget('home_stats');
        }

        // Only flush filter options when FK references changed
        if ($property->wasChanged(self::FILTER_FIELDS)) {
            Cache::tags(['filter-options'])->flush();
        }
    }

    public function deleted(Property $property): void
    {
        Cache::tags(["property:{$property->slug}"])->flush();
        Cache::tags(['properties'])->flush();
        Cache::tags(['filter-options'])->flush();
        Cache::forget('home_stats');
    }
}
