<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Provides geocoding for the admin property location form.
 *
 * Proxies requests through the existing GeocodingService (Nominatim)
 * which handles caching and rate limiting. Returns coordinates for
 * the LocationMapPicker component to display.
 */
class GeocodingController extends Controller
{
    public function __invoke(Request $request, GeocodingService $geocoder): JsonResponse
    {
        $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'city_id' => ['nullable', 'integer'],
            'state' => ['nullable', 'string', 'max:10'],
        ]);

        $cityName = $request->city_id
            ? City::whereKey($request->city_id)->value('name')
            : null;

        $result = $geocoder->geocode(
            $request->input('address'),
            $cityName,
            $request->input('state'),
        );

        if (! $result) {
            return response()->json(['error' => 'Address not found'], 422);
        }

        return response()->json($result);
    }
}
