<?php

namespace App\Models;

use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

#[Fillable(['property_id', 'user_id', 'name', 'email', 'phone', 'message', 'inquiry_status_id', 'replied_at'])]
class Inquiry extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory, UsesCipherSweet;

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('name')
            ->addField('email')
            ->addOptionalTextField('phone')
            ->addBlindIndex('email', new BlindIndex('inquiry_email_index'));
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inquiryStatus(): BelongsTo
    {
        return $this->belongsTo(InquiryStatus::class);
    }

    public function scopeNew($query)
    {
        return $query->whereHas('inquiryStatus', fn ($q) => $q->where('slug', 'new'));
    }

    public function scopeUnread($query)
    {
        return $query->whereHas('inquiryStatus', fn ($q) => $q->where('slug', 'new'));
    }
}
