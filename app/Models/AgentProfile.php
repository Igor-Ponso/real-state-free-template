<?php

namespace App\Models;

use Database\Factories\AgentProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

#[Fillable(['user_id', 'phone', 'bio', 'license_number', 'specializations', 'social_links', 'is_featured'])]
class AgentProfile extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<AgentProfileFactory> */
    use HasFactory, UsesCipherSweet;

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'social_links' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addOptionalTextField('phone');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
