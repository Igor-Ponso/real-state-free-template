<?php

namespace App\Models;

use Database\Factories\SocialAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * OAuth provider account linked to a user (e.g., Google, GitHub).
 *
 * Stores provider credentials so users can log in via Socialite
 * without a local password. Unique constraint on provider + provider_id.
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider OAuth provider name (e.g., 'google', 'github')
 * @property string $provider_id Unique ID from the OAuth provider
 * @property string|null $provider_token OAuth access token
 * @property string|null $provider_refresh_token OAuth refresh token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'provider', 'provider_id', 'provider_token', 'provider_refresh_token'])]
class SocialAccount extends Model
{
    /** @use HasFactory<SocialAccountFactory> */
    use HasFactory;

    /**
     * The user who owns this social account.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
