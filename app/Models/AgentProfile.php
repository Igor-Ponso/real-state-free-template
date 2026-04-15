<?php

namespace App\Models;

use Database\Factories\AgentProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Extended profile for users with the agent role.
 *
 * Phone is encrypted at rest via CipherSweet (optional text field,
 * no blind index). One-to-one with User via unique FK.
 *
 * Public-facing: exposes a `slug` for URLs like /agents/{slug}.
 * Slug is derived from the user's (decrypted) name and stabilized —
 * renaming the user does not change the slug, keeping agent URLs
 * SEO-stable over time.
 *
 * Profile photo stored via Spatie MediaLibrary (`profile_photo` single-file
 * collection). Consumers should call getFirstMediaUrl('profile_photo', 'card')
 * with a placeholder fallback.
 *
 * @property int $id
 * @property int $user_id
 * @property string $slug Public URL-friendly identifier
 * @property string|null $phone Encrypted at rest (CipherSweet)
 * @property string|null $bio
 * @property string|null $license_number
 * @property array<int, string> $specializations
 * @property array<string, string> $social_links
 * @property bool $is_featured
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'phone', 'bio', 'license_number', 'specializations', 'social_links', 'is_featured'])]
class AgentProfile extends Model implements CipherSweetEncrypted, HasMedia
{
    /** @use HasFactory<AgentProfileFactory> */
    use HasFactory, HasSlug, InteractsWithMedia, SoftDeletes, UsesCipherSweet;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'social_links' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Configure CipherSweet encryption for PII fields.
     *
     * Phone is encrypted at rest as an optional text field (no blind index).
     */
    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addOptionalTextField('phone');
    }

    /**
     * Derive slug from the associated user's name.
     *
     * `doNotGenerateSlugsOnUpdate()` keeps URLs stable if an agent later
     * edits their name. For brand-new profiles with no linked user yet
     * (defensive fallback), falls back to `agent-{id}` which the factory
     * flow never actually hits in practice.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn () => $this->user?->name ?? 'agent')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Use slug for public route model binding (e.g. /agents/{agentProfile}).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Single-file collection for the agent's profile photo.
     *
     * `singleFile()` replaces any existing photo on re-upload instead of
     * accumulating, matching user expectation for "change my photo".
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Two conversions covering most UI contexts:
     * - `thumb` (150x150 square) for nav avatars, table rows, compact lists
     * - `card` (400x400 square) for team section cards and public profile hero
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('profile_photo')
            ->nonQueued();

        $this->addMediaConversion('card')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->performOnCollections('profile_photo')
            ->nonQueued();
    }

    /**
     * The user this agent profile belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to only featured agent profiles.
     *
     * @param  Builder<AgentProfile>  $query
     * @return Builder<AgentProfile>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
