<?php

namespace App\Models;

use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

/**
 * Contact inquiry submitted on a property listing.
 *
 * PII fields (name, email, phone) are encrypted at rest via CipherSweet.
 * Email has a blind index (`inquiry_email_index`) for exact-match lookups.
 * User is nullable to allow guest inquiries.
 *
 * @property int $id
 * @property int $property_id
 * @property int|null $user_id Null for guest (unauthenticated) inquiries
 * @property string $name Encrypted at rest (CipherSweet)
 * @property string $email Encrypted at rest (CipherSweet), blind-indexed as `inquiry_email_index`
 * @property string|null $phone Encrypted at rest (CipherSweet)
 * @property string $message
 * @property int $inquiry_status_id
 * @property Carbon|null $replied_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Property $property
 * @property-read User|null $user
 * @property-read InquiryStatus $inquiryStatus
 */
#[Fillable(['property_id', 'user_id', 'name', 'email', 'phone', 'message', 'inquiry_status_id', 'replied_at'])]
class Inquiry extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory, SoftDeletes, UsesCipherSweet;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    /**
     * Configure CipherSweet encryption for PII fields.
     *
     * Name and email are encrypted. Phone is an optional encrypted text field.
     * A blind index on email allows exact-match lookups without decryption.
     */
    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('name')
            ->addField('email')
            ->addOptionalTextField('phone')
            ->addBlindIndex('email', new BlindIndex('inquiry_email_index'));
    }

    /**
     * The property this inquiry was submitted about.
     *
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The authenticated user who submitted this inquiry, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The current workflow status of this inquiry.
     *
     * @return BelongsTo<InquiryStatus, $this>
     */
    public function inquiryStatus(): BelongsTo
    {
        return $this->belongsTo(InquiryStatus::class);
    }

    /**
     * Scope to inquiries with the "new" status.
     *
     * @param  Builder<Inquiry>  $query
     * @return Builder<Inquiry>
     */
    public function scopeNew($query)
    {
        return $query->whereHas('inquiryStatus', fn ($q) => $q->where('slug', 'new'));
    }

    /**
     * Scope to unread inquiries (alias for "new" status).
     *
     * @param  Builder<Inquiry>  $query
     * @return Builder<Inquiry>
     */
    public function scopeUnread($query)
    {
        return $query->whereHas('inquiryStatus', fn ($q) => $q->where('slug', 'new'));
    }
}
