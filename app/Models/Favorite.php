<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A user's saved/favorited property listing.
 *
 * Pivot-like model with a unique constraint on user_id + property_id
 * to prevent duplicate favorites.
 *
 * @property int $id
 * @property int $user_id
 * @property int $property_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Property $property
 */
#[Fillable(['user_id', 'property_id'])]
class Favorite extends Model
{
    /**
     * The user who favorited the property.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The property that was favorited.
     *
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
