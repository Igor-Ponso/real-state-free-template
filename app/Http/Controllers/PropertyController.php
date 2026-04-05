<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyIndexRequest;
use App\Http\Resources\FeaturedPropertyResource;
use App\Http\Resources\PropertyDetailResource;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Handles property listing and detail pages.
 *
 * Supports multi-value filters (city[], type[], bedrooms[]) via array query params.
 * Map center is derived from the first selected city's coordinates.
 */
class PropertyController extends Controller
{
    /**
     * Display the property listing with filters, sorting, and pagination.
     */
    public function index(PropertyIndexRequest $request): Response
    {
        $citySlugs = $this->toArray($request->query('city'));
        $typeSlugs = $this->toArray($request->query('type'));
        $bedroomValues = $this->toArray($request->query('bedrooms'));
        $bathroomValues = $this->toArray($request->query('bathrooms'));
        $listingSlugs = $this->toArray($request->query('listing'));
        $unitAmenities = $this->toArray($request->query('unit_amenities'));
        $buildingAmenities = $this->toArray($request->query('building_amenities'));

        // Resolve map center from first selected city
        $mapCenter = null;
        if ($citySlugs) {
            $city = City::where('slug', $citySlugs[0])->first(['latitude', 'longitude']);
            if ($city) {
                $mapCenter = [(float) $city->latitude, (float) $city->longitude];
            }
        }

        return Inertia::render('Properties/Index', [
            'properties' => FeaturedPropertyResource::collection(
                Property::query()
                    ->with(['city', 'listingType', 'propertyType', 'media'])
                    ->published()
                    ->when($typeSlugs, fn ($q) => $q->whereHas('propertyType', fn ($q) => $q->whereIn('slug', $typeSlugs)))
                    ->when($citySlugs, fn ($q) => $q->whereHas('city', fn ($q) => $q->whereIn('slug', $citySlugs)))
                    ->when($listingSlugs, fn ($q) => $q->whereHas('listingType', fn ($q) => $q->whereIn('slug', $listingSlugs)))
                    ->when($request->query('min_price'), fn ($q, $min) => $q->where('price', '>=', (int) $min))
                    ->when($request->query('max_price'), fn ($q, $max) => $q->where('price', '<=', (int) $max))
                    ->when($bedroomValues, fn ($q) => $q->where('bedrooms', '>=', (int) min($bedroomValues)))
                    ->when($bathroomValues, fn ($q) => $q->where('bathrooms', '>=', (float) min($bathroomValues)))
                    ->when($unitAmenities, function ($q) use ($unitAmenities) {
                        foreach ($unitAmenities as $amenity) {
                            $q->whereJsonContains('unit_amenities', $amenity);
                        }
                    })
                    ->when($buildingAmenities, function ($q) use ($buildingAmenities) {
                        foreach ($buildingAmenities as $amenity) {
                            $q->whereJsonContains('building_amenities', $amenity);
                        }
                    })
                    ->when($request->validated('search'), function ($q, $search) {
                        $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
                        $q->where('title', 'ilike', "%{$escaped}%");
                    })
                    ->when(
                        $request->query('sort'),
                        fn ($q, $sort) => match ($sort) {
                            'price_asc' => $q->orderBy('price'),
                            'price_desc' => $q->orderByDesc('price'),
                            'oldest' => $q->oldest('published_at'),
                            default => $q->latest('published_at'),
                        },
                        fn ($q) => $q->latest('published_at'),
                    )
                    ->paginate(max(1, min((int) $request->query('per_page', 12), 48)))
                    ->withQueryString(),
            ),
            'filters' => Inertia::defer(fn () => Cache::tags(['filter-options'])->remember(
                'property_filter_options',
                3600,
                fn () => [
                    'propertyTypes' => PropertyType::active()->ordered()->get(['name', 'slug'])->toArray(),
                    'cities' => City::ordered()->get(['name', 'slug', 'latitude', 'longitude'])->toArray(),
                    'listingTypes' => ListingType::active()->ordered()->get(['name', 'slug'])->toArray(),
                    'unitAmenities' => collect(Property::UNIT_AMENITIES)->map(fn ($a) => ['name' => str($a)->replace('_', ' ')->title()->toString(), 'slug' => $a])->toArray(),
                    'buildingAmenities' => collect(Property::BUILDING_AMENITIES)->map(fn ($a) => ['name' => str($a)->replace('_', ' ')->title()->toString(), 'slug' => $a])->toArray(),
                ],
            )),
            'appliedFilters' => (object) $request->only(['type', 'city', 'listing', 'min_price', 'max_price', 'bedrooms', 'bathrooms', 'search', 'sort', 'unit_amenities', 'building_amenities']),
            'mapCenter' => $mapCenter,
        ]);
    }

    /**
     * Display a single property detail page.
     */
    public function show(Property $property): Response
    {
        $property->load(['city', 'listingType', 'propertyType', 'propertyStatus', 'agent.agentProfile', 'media']);

        abort_unless(
            $property->is_published && $property->propertyStatus?->slug === 'active',
            404,
        );

        $cacheKey = "property_detail:{$property->slug}";

        // Extend TTL on frequently accessed properties (Cache::touch — hidden feature)
        Cache::touch($cacheKey, 1800);

        return Inertia::render('Properties/Show', [
            'property' => fn () => Cache::tags(['properties', "property:{$property->slug}"])->remember(
                $cacheKey,
                1800,
                fn () => (new PropertyDetailResource($property))->resolve(),
            ),
            'similarProperties' => Inertia::defer(fn () => Cache::tags(['properties'])->remember(
                "similar_properties:{$property->slug}",
                1800,
                fn () => FeaturedPropertyResource::collection(
                    Property::query()
                        ->with(['city', 'listingType', 'propertyType', 'media'])
                        ->published()
                        ->where('city_id', $property->city_id)
                        ->where('id', '!=', $property->id)
                        ->limit(4)
                        ->latest('published_at')
                        ->get(),
                )->resolve(),
            )),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    /**
     * Normalize a query param to an array (supports both ?city=vancouver and ?city[]=vancouver&city[]=toronto).
     *
     * @return string[]
     */
    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_filter($value);
        }

        if (is_string($value) && $value !== '') {
            return [$value];
        }

        return [];
    }
}
