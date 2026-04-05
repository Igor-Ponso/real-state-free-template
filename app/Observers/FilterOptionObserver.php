<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Shared observer for lookup tables (City, PropertyType, ListingType).
 *
 * Flushes the `filter-options` cache tag when any lookup record changes,
 * ensuring filter dropdowns reflect the current state.
 */
class FilterOptionObserver
{
    public function created(Model $model): void
    {
        Cache::tags(['filter-options'])->flush();
    }

    public function updated(Model $model): void
    {
        Cache::tags(['filter-options'])->flush();
    }

    public function deleted(Model $model): void
    {
        Cache::tags(['filter-options'])->flush();
    }
}
