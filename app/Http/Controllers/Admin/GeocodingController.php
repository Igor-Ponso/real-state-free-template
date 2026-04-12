<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeocodingRequest;
use App\Models\City;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;

/**
 * Provides geocoding for the admin property location form.
 *
 * Proxies requests through the existing GeocodingService (Nominatim)
 * which handles caching and rate limiting. Returns coordinates for
 * the LocationMapPicker component to display.
 */
class GeocodingController extends Controller
{
    public function __invoke(GeocodingRequest $request, GeocodingService $geocoder): JsonResponse
    {
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
