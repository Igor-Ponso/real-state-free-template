<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Resolves street addresses into latitude/longitude coordinates via Nominatim.
 *
 * Nominatim (OpenStreetMap) is a free geocoding service with strict usage limits:
 * - Max 1 request/second per application
 * - Requires a meaningful User-Agent header
 * - Results must be cached aggressively
 *
 * @see https://operations.osmfoundation.org/policies/nominatim/
 * @see https://nominatim.org/release-docs/develop/api/Search/
 */
class GeocodingService
{
    /**
     * Resolve a full address into coordinates.
     *
     * Returns null if geocoding is disabled, the API fails, or no match is found.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function geocode(string $address, ?string $city = null, ?string $state = null, ?string $country = 'Canada'): ?array
    {
        if (! config('services.nominatim.enabled')) {
            return null;
        }

        if (trim($address) === '') {
            return null;
        }

        $query = $this->buildQuery($address, $city, $state, $country);

        return Cache::remember(
            $this->cacheKey($query),
            (int) config('services.nominatim.cache_ttl'),
            fn () => $this->request($query),
        );
    }

    /**
     * Perform the HTTP request to Nominatim.
     *
     * @return array{lat: float, lng: float}|null
     */
    private function request(string $query): ?array
    {
        try {
            $response = Http::withUserAgent((string) config('services.nominatim.user_agent'))
                ->timeout((int) config('services.nominatim.timeout'))
                ->retry(2, 500, throw: false)
                ->get(config('services.nominatim.base_url').'/search', [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'addressdetails' => 0,
                ]);

            if (! $response->successful()) {
                Log::warning('Nominatim geocoding failed', [
                    'query' => $query,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();

            if (! is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) {
                return null;
            }

            return [
                'lat' => (float) $data[0]['lat'],
                'lng' => (float) $data[0]['lon'],
            ];
        } catch (ConnectionException $e) {
            Log::warning('Nominatim geocoding exception', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build the free-form query string from address components.
     */
    private function buildQuery(string $address, ?string $city, ?string $state, ?string $country): string
    {
        return collect([$address, $city, $state, $country])
            ->filter(fn ($part) => ! empty($part))
            ->map(fn ($part) => trim((string) $part))
            ->implode(', ');
    }

    /**
     * Generate a stable cache key from the query.
     */
    private function cacheKey(string $query): string
    {
        return 'geocoding:'.sha1($query);
    }
}
