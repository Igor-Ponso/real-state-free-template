<?php

namespace App\Actions\Property;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Handles the creation of a new property listing.
 *
 * Generates a unique slug, sets published_at when publishing,
 * and assigns the property to the authenticated agent.
 * Uses forceFill for user_id since it's excluded from $fillable
 * to prevent mass assignment from public-facing forms.
 */
class CreatePropertyAction
{
    public function execute(User $user, array $data): Property
    {
        do {
            $slug = Str::slug($data['title']).'-'.Str::random(4);
        } while (Property::where('slug', $slug)->exists());

        $data['slug'] = $slug;

        if (($data['is_published'] ?? false) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $property = new Property;
        $property->user_id = $user->id;
        $property->fill($data);
        $property->save();

        return $property;
    }
}
