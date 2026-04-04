<?php

namespace App\Models;

use Database\Factories\PropertyTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Lookup table for property categories (e.g., house, condo, townhouse, land).
 *
 * Admin-manageable via panel without deploys. Includes an optional icon
 * field for frontend display in filters and cards.
 *
 * @property int $id
 * @property string $name Human-readable type label
 * @property string $slug Unique machine-friendly identifier
 * @property string|null $icon Icon identifier for frontend rendering
 * @property int $sort_order Display ordering position
 * @property bool $is_active Whether this property type is currently available
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Property> $properties
 */
#[Fillable(['name', 'slug', 'icon', 'sort_order', 'is_active'])]
class PropertyType extends Model
{
    /** @use HasFactory<PropertyTypeFactory> */
    use HasFactory;

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
     * Properties classified under this type.
     *
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Scope to only active (available) property types.
     *
     * @param  Builder<PropertyType>  $query
     * @return Builder<PropertyType>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order property types by their sort_order column.
     *
     * @param  Builder<PropertyType>  $query
     * @return Builder<PropertyType>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
