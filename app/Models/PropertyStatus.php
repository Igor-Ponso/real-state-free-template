<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Lookup table for property lifecycle statuses (e.g., active, sold, pending).
 *
 * Admin-manageable via panel without deploys. The `scopePublished()`
 * on Property checks for slug 'active' on this table.
 *
 * @property int $id
 * @property string $name Human-readable status label
 * @property string $slug Unique machine-friendly identifier (e.g., 'active', 'sold')
 * @property int $sort_order Display ordering position
 * @property bool $is_active Whether this status is currently available
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Property> $properties
 */
#[Fillable(['name', 'slug', 'sort_order', 'is_active'])]
class PropertyStatus extends Model
{
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
     * Properties currently in this status.
     *
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Scope to only active (available) property statuses.
     *
     * @param  Builder<PropertyStatus>  $query
     * @return Builder<PropertyStatus>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order statuses by their sort_order column.
     *
     * @param  Builder<PropertyStatus>  $query
     * @return Builder<PropertyStatus>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
