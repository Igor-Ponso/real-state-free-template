<?php

namespace App\Actions\Property;

use App\Models\Property;

/**
 * Handles updating an existing property listing.
 *
 * Sets published_at when transitioning to published state, and re-geocodes
 * coordinates when the address changes and no manual lat/lng is provided.
 */
class UpdatePropertyAction
{
    public function __construct(
        private readonly ResolvePropertyCoordinatesAction $resolveCoordinates,
    ) {}

    /**
     * @param  array<string, mixed>  $data  Validated property attributes.
     */
    public function execute(Property $property, array $data): Property
    {
        if (($data['is_published'] ?? false) && ! $property->is_published && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($this->shouldRegeocode($property, $data)) {
            $coords = $this->resolveCoordinates->execute([
                'address' => $data['address'] ?? $property->address,
                'city_id' => $data['city_id'] ?? $property->city_id,
                'state' => $data['state'] ?? $property->state,
            ]);
            if ($coords !== null) {
                $data['latitude'] = $coords['lat'];
                $data['longitude'] = $coords['lng'];
            }
        }

        $property->update($data);

        return $property;
    }

    /**
     * Re-geocode only when the address changed and the user did not provide
     * explicit coordinates in the update payload.
     *
     * @param  array<string, mixed>  $data
     */
    private function shouldRegeocode(Property $property, array $data): bool
    {
        if (! empty($data['latitude']) && ! empty($data['longitude'])) {
            return false;
        }

        $addressChanged = isset($data['address']) && $data['address'] !== $property->address;
        $cityChanged = isset($data['city_id']) && (int) $data['city_id'] !== (int) $property->city_id;

        return $addressChanged || $cityChanged;
    }
}
