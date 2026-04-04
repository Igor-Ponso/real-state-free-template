<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Lookup table for how a property is listed (e.g., sale, rental, lease).
 *
 * Admin-manageable via panel without deploys. Referenced by Property
 * via FK and used in scopes like `forSale()` / `forRent()`.
 *
 * @property int $id
 * @property string $name Human-readable listing type label
 * @property string $slug Unique machine-friendly identifier (e.g., 'sale', 'rental')
 * @property int $sort_order Display ordering position
 * @property bool $is_active Whether this listing type is currently available
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Property> $properties
 */
#[Fillable(['name', 'slug', 'sort_order', 'is_active'])]
class ListingType extends Model
{
    /**
     * Resolve route model binding by slug instead of ID.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Properties using this listing type.
     *
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Scope to only active (available) listing types.
     *
     * @param  Builder<ListingType>  $query
     * @return Builder<ListingType>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order listing types by their sort_order column.
     *
     * @param  Builder<ListingType>  $query
     * @return Builder<ListingType>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
