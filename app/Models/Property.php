<?php

namespace App\Models;

use Brick\Money\Money;
use Database\Factories\PropertyFactory;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Core real estate property listing with media, pricing, and location.
 *
 * Uses Spatie MediaLibrary for images and floor plans, and
 * elegantly/laravel-money (Brick\Money) for monetary fields.
 * Supports soft deletes for archiving without data loss.
 *
 * @property int $id
 * @property int $user_id FK to the listing agent (User)
 * @property int $property_type_id FK to PropertyType lookup
 * @property string $title
 * @property string $slug Unique URL-friendly identifier
 * @property string $description
 * @property int $listing_type_id FK to ListingType lookup (sale, rental, etc.)
 * @property int $property_status_id FK to PropertyStatus lookup (active, sold, etc.)
 * @property Money $price Cast via MoneyCast, stored as bigint cents
 * @property string $currency ISO 4217 currency code (default 'CAD')
 * @property Money|null $price_min Optional price range lower bound
 * @property Money|null $price_max Optional price range upper bound
 * @property string $address
 * @property int $city_id FK to City
 * @property string|null $neighborhood
 * @property string $state
 * @property string $zip_code
 * @property string|null $latitude Decimal degrees (7 decimal places)
 * @property string|null $longitude Decimal degrees (7 decimal places)
 * @property int $bedrooms
 * @property string $bathrooms Decimal (e.g., 2.5) cast to 1 decimal place
 * @property int $area_sqft
 * @property int|null $lot_size_sqft
 * @property int|null $year_built
 * @property int $parking_spaces
 * @property int|null $floor
 * @property int|null $total_floors
 * @property array<int, string> $unit_amenities JSONB array — unit-level amenities (balcony, den, in-suite laundry, etc.)
 * @property array<int, string> $building_amenities JSONB array — building-level amenities (pool, gym, concierge, etc.)
 * @property array<string, mixed> $features JSONB object of feature key-value pairs
 * @property Money|null $deposit Rental deposit, stored as bigint cents
 * @property int|null $lease_length_months
 * @property Carbon|null $available_from Rental availability date
 * @property bool $pets_allowed
 * @property string|null $meta_title SEO title override
 * @property string|null $meta_description SEO description override
 * @property bool $is_featured
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $agent
 * @property-read PropertyType $propertyType
 * @property-read ListingType $listingType
 * @property-read PropertyStatus $propertyStatus
 * @property-read City $city
 * @property-read Collection<int, Inquiry> $inquiries
 * @property-read Collection<int, Favorite> $favorites
 * @property-read Collection<int, PropertyView> $views
 * @property-read MediaCollection<int, Media> $media
 */
#[Fillable([
    'property_type_id', 'title', 'slug', 'description',
    'listing_type_id', 'property_status_id', 'price', 'currency', 'price_min', 'price_max',
    'address', 'city_id', 'neighborhood', 'state', 'zip_code', 'latitude', 'longitude',
    'bedrooms', 'bathrooms', 'area_sqft', 'lot_size_sqft', 'year_built',
    'parking_spaces', 'floor', 'total_floors',
    'unit_amenities', 'building_amenities', 'features',
    'deposit', 'lease_length_months', 'available_from', 'pets_allowed',
    'meta_title', 'meta_description',
    'is_featured', 'is_published', 'published_at',
])]
class Property extends Model implements HasMedia
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, HasSlug, InteractsWithMedia, Searchable, SoftDeletes;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /** @var list<string> */
    public const UNIT_AMENITIES = [
        'balcony', 'den', 'solarium', 'fireplace', 'in_suite_laundry',
        'walk_in_closet', 'hardwood_floors', 'air_conditioning', 'dishwasher',
        'storage_locker', 'ev_charger', 'smart_home', 'heated_floors',
    ];

    /** @var list<string> */
    public const BUILDING_AMENITIES = [
        'pool', 'gym', 'concierge', 'doorman', 'elevator', 'rooftop_deck',
        'party_room', 'bbq_area', 'sauna', 'steam_room', 'private_theater',
        'shared_laundry', 'bike_storage', 'guest_suite', 'playground',
        'tennis_court', 'security', 'underground_parking', 'garden',
    ];

    /**
     * Resolve route model binding by slug instead of ID.
     *
     * Produces SEO-friendly URLs like /properties/luxury-villa-in-waterfront
     * and prevents exposing sequential integer IDs.
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
            'price' => MoneyCast::of('currency'),
            'price_min' => MoneyCast::of('currency'),
            'price_max' => MoneyCast::of('currency'),
            'deposit' => MoneyCast::of('currency'),
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'bathrooms' => 'decimal:1',
            'unit_amenities' => 'array',
            'building_amenities' => 'array',
            'features' => 'array',
            'pets_allowed' => 'boolean',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'available_from' => 'date',
        ];
    }

    /**
     * Register Spatie MediaLibrary collections for property images and floor plans.
     *
     * - `images`: JPEG, PNG, and WebP photos of the property.
     * - `floor_plans`: JPEG, PNG images or PDF documents of floor layouts.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('floor_plans')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }

    /**
     * Register responsive image conversions for property photos.
     *
     * Generates thumbnail (400px), card (800px), and gallery (1400px) sizes
     * in both original format and WebP for optimal loading. Each conversion
     * also generates responsive images (srcset) for right-sized delivery
     * per device viewport.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(250)
            ->sharpen(10)
            ->withResponsiveImages()
            ->performOnCollections('images')
            ->nonQueued();

        $this->addMediaConversion('thumb-webp')
            ->format('webp')
            ->quality(80)
            ->width(400)
            ->height(250)
            ->sharpen(10)
            ->performOnCollections('images')
            ->nonQueued();

        $this->addMediaConversion('card')
            ->width(800)
            ->height(500)
            ->withResponsiveImages()
            ->performOnCollections('images')
            ->nonQueued();

        $this->addMediaConversion('card-webp')
            ->format('webp')
            ->quality(80)
            ->width(800)
            ->height(500)
            ->performOnCollections('images')
            ->nonQueued();

        $this->addMediaConversion('gallery')
            ->width(1400)
            ->height(875)
            ->withResponsiveImages()
            ->performOnCollections('images')
            ->nonQueued();

        $this->addMediaConversion('gallery-webp')
            ->format('webp')
            ->quality(80)
            ->width(1400)
            ->height(875)
            ->performOnCollections('images')
            ->nonQueued();
    }

    /**
     * The agent (user) who created this property listing.
     *
     * @return BelongsTo<User, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The category of this property (house, condo, land, etc.).
     *
     * @return BelongsTo<PropertyType, $this>
     */
    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    /**
     * How this property is listed (sale, rental, etc.).
     *
     * @return BelongsTo<ListingType, $this>
     */
    public function listingType(): BelongsTo
    {
        return $this->belongsTo(ListingType::class);
    }

    /**
     * The current lifecycle status of this property (active, sold, pending, etc.).
     *
     * @return BelongsTo<PropertyStatus, $this>
     */
    public function propertyStatus(): BelongsTo
    {
        return $this->belongsTo(PropertyStatus::class);
    }

    /**
     * The city where this property is located.
     *
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Contact inquiries submitted on this property.
     *
     * @return HasMany<Inquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * User favorites/bookmarks for this property.
     *
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Page view records for analytics tracking.
     *
     * @return HasMany<PropertyView, $this>
     */
    public function views(): HasMany
    {
        return $this->hasMany(PropertyView::class);
    }

    /**
     * Scope to only published and active properties.
     *
     * Requires `is_published = true` AND the related PropertyStatus
     * to have slug 'active'.
     *
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereHas('propertyStatus', fn ($q) => $q->where('slug', 'active'));
    }

    /**
     * Scope to only featured properties highlighted on the landing page.
     *
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to properties listed for sale.
     *
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeForSale($query)
    {
        return $query->whereHas('listingType', fn ($q) => $q->where('slug', 'sale'));
    }

    /**
     * Scope to properties listed for rent.
     *
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeForRent($query)
    {
        return $query->whereHas('listingType', fn ($q) => $q->where('slug', 'rental'));
    }

    /**
     * Scope to properties in a specific city.
     *
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeByCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Scope to filter properties within an optional price range (in smallest currency unit).
     *
     * @param  Builder<Property>  $query
     * @param  int|null  $min  Minimum price in cents (inclusive)
     * @param  int|null  $max  Maximum price in cents (inclusive)
     * @return Builder<Property>
     */
    public function scopePriceRange($query, ?int $min = null, ?int $max = null)
    {
        return $query
            ->when($min !== null, fn ($q) => $q->where('price', '>=', $min))
            ->when($max !== null, fn ($q) => $q->where('price', '<=', $max));
    }

    /**
     * Scope to properties with at least the given number of bedrooms.
     *
     * @param  Builder<Property>  $query
     * @param  int  $min  Minimum bedroom count (inclusive)
     * @return Builder<Property>
     */
    public function scopeBedrooms($query, int $min)
    {
        return $query->where('bedrooms', '>=', $min);
    }

    /**
     * Scope to properties that include a specific amenity in their JSONB array.
     *
     * @param  Builder<Property>  $query
     * @param  string  $amenity  The amenity string to search for
     * @return Builder<Property>
     */
    public function scopeWithUnitAmenity($query, string $amenity)
    {
        return $query->whereJsonContains('unit_amenities', $amenity);
    }

    /**
     * Scope: properties whose building has a specific amenity.
     *
     * @param  Builder<Property>  $query
     */
    public function scopeWithBuildingAmenity($query, string $amenity)
    {
        return $query->whereJsonContains('building_amenities', $amenity);
    }

    // ── Scout ──────────────────────────────────────────────────

    /**
     * Get the indexable data array for the model.
     *
     * Used by Scout's database/collection engine for search queries.
     * Only title, description, and address are indexed — city search
     * is handled via the scopeSearch() Eloquent scope.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
        ];
    }

    /**
     * Determine if the model should be searchable.
     *
     * Note: has no effect with the database engine (queries the table directly).
     * Effective when using collection, Algolia, Meilisearch, or Typesense engines.
     */
    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }

    // ── Scopes ─────────────────────────────────────────────────

    /**
     * Scope: multi-column case-insensitive search across title, description,
     * address, and city name.
     *
     * Uses ILIKE on PostgreSQL (leverages trigram GIN indexes) and
     * LIKE on SQLite (case-insensitive by default for ASCII).
     *
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeSearch($query, string $search)
    {
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
        $driver = $query->getConnection()->getDriverName();
        $operator = $driver === 'pgsql' ? 'ilike' : 'like';
        $term = "%{$escaped}%";

        return $query->where(function ($q) use ($operator, $term) {
            $q->where('title', $operator, $term)
                ->orWhere('description', $operator, $term)
                ->orWhere('address', $operator, $term)
                ->orWhereHas('city', fn ($c) => $c->where('name', $operator, $term));
        });
    }

    /**
     * Scope: case-insensitive title-only search.
     *
     * Kept for admin search (needs to find drafts) and backward compatibility.
     * For public search, prefer scopeSearch() which covers multiple columns.
     *
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeSearchByTitle($query, string $search)
    {
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            return $query->where('title', 'ilike', "%{$escaped}%");
        }

        return $query->where('title', 'like', "%{$escaped}%");
    }
}
