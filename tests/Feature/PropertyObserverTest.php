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

it('flushes properties and property-specific cache tags on property update', function () {
    // Create without the observer interfering with our mock setup
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Cache::shouldReceive('tags')->with(["property:{$property->slug}"])->once()->andReturnSelf();
    Cache::shouldReceive('tags')->with(['properties'])->once()->andReturnSelf();
    Cache::shouldReceive('flush')->twice();
    Cache::shouldReceive('forget')->with('home_stats')->once();

    $property->update(['title' => 'Updated Title']);
});

it('flushes filter-options cache when FK fields change on update', function () {
    $newCity = City::factory()->create();

    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Cache::shouldReceive('tags')->with(["property:{$property->slug}"])->once()->andReturnSelf();
    Cache::shouldReceive('tags')->with(['properties'])->once()->andReturnSelf();
    Cache::shouldReceive('tags')->with(['filter-options'])->once()->andReturnSelf();
    Cache::shouldReceive('flush')->times(3);
    Cache::shouldReceive('forget')->with('home_stats')->once();

    $property->update(['city_id' => $newCity->id]);
});

it('does not flush filter-options cache when non-FK fields change on update', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Cache::shouldReceive('tags')->with(["property:{$property->slug}"])->once()->andReturnSelf();
    Cache::shouldReceive('tags')->with(['properties'])->once()->andReturnSelf();
    Cache::shouldReceive('flush')->twice();
    Cache::shouldReceive('forget')->with('home_stats')->once();

    // filter-options should NOT be flushed for title changes
    $property->update(['title' => 'New Title']);
});

it('flushes all cache tags on property delete', function () {
    $property = Property::factory()->active()->create([
        'user_id' => $this->agent->id,
        'city_id' => $this->city->id,
        'property_type_id' => $this->type->id,
    ]);

    Cache::shouldReceive('tags')->with(["property:{$property->slug}"])->once()->andReturnSelf();
    Cache::shouldReceive('tags')->with(['properties'])->once()->andReturnSelf();
    Cache::shouldReceive('tags')->with(['filter-options'])->once()->andReturnSelf();
    Cache::shouldReceive('flush')->times(3);
    Cache::shouldReceive('forget')->with('home_stats')->once();

    $property->delete();
});
