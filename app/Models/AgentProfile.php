<?php

namespace App\Models;

use Database\Factories\AgentProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

/**
 * Extended profile for users with the agent role.
 *
 * Phone is encrypted at rest via CipherSweet (optional text field,
 * no blind index). One-to-one with User via unique FK.
 *
 * @property int $id
 * @property int $user_id
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
class AgentProfile extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<AgentProfileFactory> */
    use HasFactory, UsesCipherSweet;

    /**
     * Get the attributes that should be cast.
     *
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
