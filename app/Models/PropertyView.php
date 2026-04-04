<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tracks individual page views on a property listing.
 *
 * Records are created for both authenticated and anonymous visitors.
 * This model does not use Eloquent timestamps; it stores a single
 * `viewed_at` datetime instead.
 *
 * @property int $id
 * @property int $property_id
 * @property int|null $user_id Null for anonymous visitors
 * @property string|null $ip_address IPv4 or IPv6, max 45 chars
 * @property string|null $user_agent Browser user-agent string
 * @property Carbon $viewed_at
 * @property-read Property $property
 * @property-read User|null $user
 */
#[Fillable(['property_id', 'user_id', 'ip_address', 'user_agent', 'viewed_at'])]
class PropertyView extends Model
{
    /** @var bool Disable default created_at/updated_at columns. */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    /**
     * The property that was viewed.
     *
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The authenticated user who viewed the property, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
