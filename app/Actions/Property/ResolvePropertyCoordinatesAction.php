<?php

namespace App\Actions\Property;

use App\Models\City;
use App\Services\GeocodingService;

/**
 * Resolves latitude/longitude from validated property data.
 *
 * Looks up the City name from city_id and delegates to GeocodingService.
 * Shared between CreatePropertyAction and UpdatePropertyAction to avoid
 * duplicating the address-building and city-lookup logic.
 */
class ResolvePropertyCoordinatesAction
{
    public function __construct(
        private readonly GeocodingService $geocoder,
    ) {}

    /**
     * @param  array<string, mixed>  $data  Validated property attributes.
     * @return array{lat: float, lng: float}|null
     */
    public function execute(array $data): ?array
    {
        if (empty($data['address'])) {
            return null;
        }

        $cityName = null;
        if (! empty($data['city_id'])) {
            $cityName = City::query()->whereKey($data['city_id'])->value('name');
        }

        return $this->geocoder->geocode(
            address: (string) $data['address'],
            city: $cityName,
            state: $data['state'] ?? null,
        );
    }
}
