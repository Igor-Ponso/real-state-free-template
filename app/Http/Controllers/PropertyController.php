<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeaturedPropertyResource;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles property listing and detail pages.
 *
 * The index method demonstrates several Inertia v3 features:
 * - Deferred props for filter options (loaded after initial render)
 * - Partial reloads when filters change (only properties re-fetched)
 * - Paginated API Resources with meta/links preserved
 * - PostgreSQL ilike for case-insensitive search
 */
class PropertyController extends Controller
{
    /**
     * Display the property listing with filters, sorting, and pagination.
     *
     * Filter options (property types, cities, listing types) are deferred —
     * they load after the initial render with a skeleton fallback on the frontend.
     * When the user changes a filter, only the `properties` prop is reloaded
     * via Inertia's partial reload mechanism.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Properties/Index', [
            'properties' => FeaturedPropertyResource::collection(
                Property::query()
                    ->with(['city', 'listingType', 'propertyType'])
                    ->published()
                    ->when($request->query('type'), fn ($q, $type) => $q->whereHas('propertyType', fn ($q) => $q->where('slug', $type)))
                    ->when($request->query('city'), fn ($q, $city) => $q->whereHas('city', fn ($q) => $q->where('slug', $city)))
                    ->when($request->query('listing'), fn ($q, $listing) => $q->whereHas('listingType', fn ($q) => $q->where('slug', $listing)))
                    ->when($request->query('min_price'), fn ($q, $min) => $q->where('price', '>=', (int) $min * 100))
                    ->when($request->query('max_price'), fn ($q, $max) => $q->where('price', '<=', (int) $max * 100))
                    ->when($request->query('bedrooms'), fn ($q, $beds) => $q->bedrooms((int) $beds))
                    ->when($request->query('search'), fn ($q, $search) => $q->where('title', 'ilike', "%{$search}%"))
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
                    ->paginate(12)
                    ->withQueryString(),
            ),
            'filters' => Inertia::defer(fn () => [
                'propertyTypes' => PropertyType::active()->ordered()->get(['name', 'slug']),
                'cities' => City::ordered()->get(['name', 'slug']),
                'listingTypes' => ListingType::active()->ordered()->get(['name', 'slug']),
            ]),
            'appliedFilters' => $request->only(['type', 'city', 'listing', 'min_price', 'max_price', 'bedrooms', 'search', 'sort']),
        ]);
    }
}
