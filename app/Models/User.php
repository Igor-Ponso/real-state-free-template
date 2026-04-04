<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\Permission\Traits\HasRoles;

/**
 * Authenticated user account with encrypted PII fields.
 *
 * Name and email are encrypted at rest via CipherSweet. Email has a
 * blind index for exact-match lookups (login, uniqueness checks).
 * Password is nullable to support social-only authentication.
 *
 * @property int $id
 * @property string $name Encrypted at rest (CipherSweet)
 * @property string $email Encrypted at rest (CipherSweet), blind-indexed as `email_index`
 * @property Carbon|null $email_verified_at
 * @property string|null $password Nullable for social-login-only users
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, SocialAccount> $socialAccounts
 * @property-read AgentProfile|null $agentProfile
 * @property-read Collection<int, Property> $properties
 * @property-read Collection<int, Favorite> $favorites
 * @property-read Collection<int, Inquiry> $inquiries
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements CipherSweetEncrypted
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes, TwoFactorAuthenticatable, UsesCipherSweet;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Configure CipherSweet encryption for PII fields.
     *
     * Email and name are encrypted at rest. A blind index on email
     * allows exact-match lookups (e.g., "does this email exist?")
     * without exposing the actual value in the database.
     */
    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('name')
            ->addField('email')
            ->addBlindIndex('email', new BlindIndex('email_index'));
    }

    /**
     * OAuth social login accounts linked to this user.
     *
     * @return HasMany<SocialAccount, $this>
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Agent profile with bio, phone, license, and specializations.
     *
     * @return HasOne<AgentProfile, $this>
     */
    public function agentProfile(): HasOne
    {
        return $this->hasOne(AgentProfile::class);
    }

    /**
     * Properties listed by this user (agent).
     *
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Properties this user has favorited.
     *
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Inquiries submitted by this user on property listings.
     *
     * @return HasMany<Inquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }
}
