<?php

namespace App\Observers;

use App\Models\Property;
use Illuminate\Support\Facades\Cache;

class PropertyObserver
{
    public function created(Property $property): void
    {
        Cache::forget('home_stats');
    }

    public function updated(Property $property): void
    {
        Cache::forget('home_stats');
    }

    public function deleted(Property $property): void
    {
        Cache::forget('home_stats');
    }
}
