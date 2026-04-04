<?php

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Geographic city used for property location grouping and search.
 *
 * Featured cities are highlighted on the landing page. Sort order
 * controls display position in dropdowns and grids.
 *
 * @property int $id
 * @property string $name
 * @property string $state
 * @property string $country ISO 3166-1 alpha-2 code (default 'CA')
 * @property string $slug Unique URL-friendly identifier
 * @property string|null $latitude Decimal degrees (7 decimal places)
 * @property string|null $longitude Decimal degrees (7 decimal places)
 * @property string|null $image_path Path to the city hero/thumbnail image
 * @property bool $is_featured
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Property> $properties
 */
#[Fillable(['name', 'state', 'country', 'slug', 'latitude', 'longitude', 'image_path', 'is_featured', 'sort_order'])]
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Property listings located in this city.
     *
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Scope to only featured cities shown on the landing page.
     *
     * @param  Builder<City>  $query
     * @return Builder<City>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to order cities by their sort_order column.
     *
     * @param  Builder<City>  $query
     * @return Builder<City>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
