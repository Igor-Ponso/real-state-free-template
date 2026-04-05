<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeaturedPropertyResource;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyType;
use Database\Factories\PropertyFactory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
    public function index(Request $request): Response
    {
        $citySlugs = $this->toArray($request->query('city'));
        $typeSlugs = $this->toArray($request->query('type'));
        $bedroomValues = $this->toArray($request->query('bedrooms'));
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
                    ->with(['city', 'listingType', 'propertyType'])
                    ->published()
                    ->when($typeSlugs, fn ($q) => $q->whereHas('propertyType', fn ($q) => $q->whereIn('slug', $typeSlugs)))
                    ->when($citySlugs, fn ($q) => $q->whereHas('city', fn ($q) => $q->whereIn('slug', $citySlugs)))
                    ->when($listingSlugs, fn ($q) => $q->whereHas('listingType', fn ($q) => $q->whereIn('slug', $listingSlugs)))
                    ->when($request->query('min_price'), fn ($q, $min) => $q->where('price', '>=', (int) $min))
                    ->when($request->query('max_price'), fn ($q, $max) => $q->where('price', '<=', (int) $max))
                    ->when($bedroomValues, fn ($q) => $q->where('bedrooms', '>=', (int) min($bedroomValues)))
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
                    ->paginate(min((int) $request->query('per_page', 12), 48))
                    ->withQueryString(),
            ),
            'filters' => Inertia::defer(fn () => [
                'propertyTypes' => PropertyType::active()->ordered()->get(['name', 'slug']),
                'cities' => City::ordered()->get(['name', 'slug', 'latitude', 'longitude']),
                'listingTypes' => ListingType::active()->ordered()->get(['name', 'slug']),
                'unitAmenities' => collect(PropertyFactory::unitAmenityOptions())->map(fn ($a) => ['name' => str($a)->replace('_', ' ')->title()->toString(), 'slug' => $a]),
                'buildingAmenities' => collect(PropertyFactory::buildingAmenityOptions())->map(fn ($a) => ['name' => str($a)->replace('_', ' ')->title()->toString(), 'slug' => $a]),
            ]),
            'appliedFilters' => $request->only(['type', 'city', 'listing', 'min_price', 'max_price', 'bedrooms', 'search', 'sort', 'unit_amenities', 'building_amenities']),
            'mapCenter' => $mapCenter,
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
