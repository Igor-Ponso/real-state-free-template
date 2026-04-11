<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyIndexRequest;
use App\Http\Resources\FeaturedPropertyResource;
use App\Http\Resources\PropertyDetailResource;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\PropertyQueryService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Handles public property listing and detail pages.
 *
 * Delegates query building to PropertyQueryService (SRP).
 */
class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyQueryService $queryService,
    ) {}

    /**
     * Display the property listing with filters, sorting, and pagination.
     */
    public function index(PropertyIndexRequest $request): Response
    {
        $filters = $request->validated();
        $citySlugs = (array) ($filters['city'] ?? []);

        return Inertia::render('Properties/Index', [
            'properties' => Inertia::scroll(fn () => FeaturedPropertyResource::collection(
                $this->queryService->filteredListing($filters, (int) ($filters['per_page'] ?? 12)),
            )),
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
            'appliedFilters' => (object) $request->only([
                'type', 'city', 'listing', 'min_price', 'max_price',
                'bedrooms', 'bedrooms_exact', 'bathrooms', 'bathrooms_exact',
                'search', 'sort', 'unit_amenities', 'building_amenities',
            ]),
            'mapCenter' => $this->queryService->resolveMapCenter($citySlugs),
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
                    $this->queryService->similarProperties($property),
                )->resolve(),
            )),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
