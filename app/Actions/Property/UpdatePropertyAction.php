<?php

namespace App\Actions\Property;

use App\Models\Property;

/**
 * Handles updating an existing property listing.
 *
 * Sets published_at when transitioning to published state.
 */
class UpdatePropertyAction
{
    public function execute(Property $property, array $data): Property
    {
        if (($data['is_published'] ?? false) && ! $property->is_published && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $property->update($data);

        return $property;
    }
}
