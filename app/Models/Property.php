<?php

namespace App\Models;

use Database\Factories\PropertyFactory;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'user_id', 'property_type_id', 'title', 'slug', 'description',
    'listing_type_id', 'property_status_id', 'price', 'currency', 'price_min', 'price_max',
    'address', 'city_id', 'neighborhood', 'state', 'zip_code', 'latitude', 'longitude',
    'bedrooms', 'bathrooms', 'area_sqft', 'lot_size_sqft', 'year_built',
    'parking_spaces', 'floor', 'total_floors',
    'amenities', 'features',
    'deposit', 'lease_length_months', 'available_from', 'pets_allowed',
    'meta_title', 'meta_description',
    'is_featured', 'is_published', 'published_at',
])]
class Property extends Model implements HasMedia
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::of('currency'),
            'price_min' => MoneyCast::of('currency'),
            'price_max' => MoneyCast::of('currency'),
            'deposit' => MoneyCast::of('currency'),
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'bathrooms' => 'decimal:1',
            'amenities' => 'array',
            'features' => 'array',
            'pets_allowed' => 'boolean',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'available_from' => 'date',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('floor_plans')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function listingType(): BelongsTo
    {
        return $this->belongsTo(ListingType::class);
    }

    public function propertyStatus(): BelongsTo
    {
        return $this->belongsTo(PropertyStatus::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(PropertyView::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereHas('propertyStatus', fn ($q) => $q->where('slug', 'active'));
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForSale($query)
    {
        return $query->whereHas('listingType', fn ($q) => $q->where('slug', 'sale'));
    }

    public function scopeForRent($query)
    {
        return $query->whereHas('listingType', fn ($q) => $q->where('slug', 'rental'));
    }

    public function scopeByCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopePriceRange($query, ?int $min = null, ?int $max = null)
    {
        return $query
            ->when($min, fn ($q) => $q->where('price', '>=', $min))
            ->when($max, fn ($q) => $q->where('price', '<=', $max));
    }

    public function scopeBedrooms($query, int $min)
    {
        return $query->where('bedrooms', '>=', $min);
    }

    public function scopeWithAmenity($query, string $amenity)
    {
        return $query->whereJsonContains('amenities', $amenity);
    }
}
