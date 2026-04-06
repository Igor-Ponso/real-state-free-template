<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertySearchRequest;
use App\Models\Property;
use Illuminate\Http\JsonResponse;

/**
 * Lightweight property search for the command palette.
 *
 * Returns minimal JSON (no Inertia) for fast autocomplete results.
 */
class PropertySearchController extends Controller
{
    public function __invoke(PropertySearchRequest $request): JsonResponse
    {
        $properties = Property::query()
            ->with(['city', 'media'])
            ->published()
            ->searchByTitle($request->string('q'))
            ->select(['id', 'title', 'slug', 'city_id', 'price', 'currency', 'bedrooms', 'bathrooms', 'published_at'])
            ->orderByDesc('published_at')
            ->limit(5)
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
