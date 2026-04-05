<?php

namespace App\Observers;

use App\Models\Property;
use Illuminate\Support\Facades\Cache;

/**
 * Handles cache invalidation when properties are created, updated, or deleted.
 *
 * Uses Redis cache tags for granular invalidation:
 * - `properties` tag: flushes all property listing caches
 * - `property:{slug}` tag: flushes a specific property detail cache
 * - `filter-options` tag: flushes when FK changes affect dropdown options
 */
class PropertyObserver
{
    public function created(Property $property): void
    {
        Cache::tags(['properties'])->flush();
        Cache::forget('home_stats');
    }

    public function updated(Property $property): void
    {
        Cache::tags(["property:{$property->slug}"])->flush();
        Cache::tags(['properties'])->flush();
        Cache::forget('home_stats');

        if ($property->wasChanged(['city_id', 'property_type_id', 'listing_type_id'])) {
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
