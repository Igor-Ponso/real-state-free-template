<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertySearchRequest;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Lightweight property search for the command palette.
 *
 * Uses Laravel Scout's search() for the database/collection engine,
 * with a query() callback to apply published scope and eager loading.
 * Returns minimal JSON (no Inertia) for fast autocomplete results.
 */
class PropertySearchController extends Controller
{
    public function __invoke(PropertySearchRequest $request): JsonResponse
    {
        $properties = Property::search($request->string('q')->toString())
            ->query(fn (Builder $query) => $query
                ->with(['city', 'media'])
                ->published()
                ->select(['id', 'title', 'slug', 'description', 'address', 'city_id', 'price', 'currency', 'bedrooms', 'bathrooms', 'published_at', 'is_published', 'property_status_id'])
                ->orderByDesc('published_at')
                ->limit(5)
            )
            ->get()
            ->map(fn (Property $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'location' => $p->city?->name ?? '',
                'price' => $p->price?->formatTo('en_CA') ?? '',
                'bedrooms' => $p->bedrooms,
                'bathrooms' => $p->bathrooms,
                'image' => $p->getFirstMediaUrl('images', 'thumbnail'),
            ]);

        return response()->json($properties);
    }
}
