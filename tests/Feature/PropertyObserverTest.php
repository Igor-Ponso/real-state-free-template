<?php

use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    PropertyStatus::create(['name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_active' => true]);
    PropertyStatus::create(['name' => 'Draft', 'slug' => 'draft', 'sort_order' => 2, 'is_active' => true]);
    PropertyStatus::create(['name' => 'Sold', 'slug' => 'sold', 'sort_order' => 3, 'is_active' => true]);
    ListingType::create(['name' => 'For Sale', 'slug' => 'sale', 'sort_order' => 1, 'is_active' => true]);
    ListingType::create(['name' => 'For Rent', 'slug' => 'rental', 'sort_order' => 2, 'is_active' => true]);

    $this->agent = User::factory()->create();
    $this->city = City::factory()->create();
    $this->type = PropertyType::factory()->create();
});

it('flushes properties cache tag on property create', function () {
    Cache::shouldReceive('tags')->with(['properties'])->once()->andReturnSelf();
    Cache::shouldReceive('flush')->once();
    Cache::shouldReceive('forget')->with('home_stats')->once();

    Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);
});

it('flushes display-relevant caches when title changes on update', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    // Spy allows calls without pre-declaring expectations
    Cache::spy();

    $property->update(['title' => 'Updated Title']);

    // property-specific cache always flushed
    Cache::shouldHaveReceived('tags')->with(["property:{$property->slug}"]);
    // display fields changed → properties listing cache flushed
    Cache::shouldHaveReceived('tags')->with(['properties']);
    Cache::shouldHaveReceived('forget')->with('home_stats');
});

it('flushes filter-options cache when FK fields change on update', function () {
    $newCity = City::factory()->create();

    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Cache::spy();

    $property->update(['city_id' => $newCity->id]);

    Cache::shouldHaveReceived('tags')->with(["property:{$property->slug}"]);
    Cache::shouldHaveReceived('tags')->with(['properties']);
    Cache::shouldHaveReceived('tags')->with(['filter-options']);
    Cache::shouldHaveReceived('forget')->with('home_stats');
});

it('only flushes property-specific cache when only meta fields change', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Cache::spy();

    $property->update(['meta_description' => 'Updated SEO description']);

    // property-specific always flushed
    Cache::shouldHaveReceived('tags')->with(["property:{$property->slug}"]);
    // non-display field → should NOT flush listing caches
    Cache::shouldNotHaveReceived('tags', [['properties']]);
    Cache::shouldNotHaveReceived('forget', ['home_stats']);
});

it('flushes all cache tags on property delete', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Cache::spy();

    $property->delete();

    Cache::shouldHaveReceived('tags')->with(["property:{$property->slug}"]);
    Cache::shouldHaveReceived('tags')->with(['properties']);
    Cache::shouldHaveReceived('tags')->with(['filter-options']);
    Cache::shouldHaveReceived('forget')->with('home_stats');
});
