<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

/**
 * Newsletter subscriber with double-opt-in confirmation.
 *
 * Email is encrypted at rest via CipherSweet with a blind index
 * (`newsletter_email_index`) for duplicate-check lookups.
 *
 * Lifecycle:
 *   1. User submits email → row created with confirmation_token, confirmed_at=null
 *   2. Confirmation email sent (on-demand via Notifiable::notify + routeNotificationForMail)
 *   3. User clicks link → confirmed_at set, welcome email sent
 *   4. User clicks unsubscribe → unsubscribed_at set, excluded from future digests
 *
 * @property int $id
 * @property string $email Encrypted at rest (CipherSweet)
 * @property string $confirmation_token 64-char opaque token for confirm/unsubscribe links
 * @property Carbon|null $confirmed_at Non-null once user confirms via email link
 * @property Carbon|null $unsubscribed_at Non-null once user unsubscribes
 * @property string|null $ip_address Origin IP for abuse tracking
 */
#[Fillable(['email', 'confirmation_token', 'confirmed_at', 'unsubscribed_at', 'ip_address'])]
class NewsletterSubscriber extends Model implements CipherSweetEncrypted
{
    use Notifiable, UsesCipherSweet;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('email')
            ->addBlindIndex('email', new BlindIndex('newsletter_email_index'));
    }

    /**
     * Route Illuminate\Notifications\Notifiable mail to the subscriber's email.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    /**
     * Active subscribers: confirmed their email and have not unsubscribed.
     *
     * @param  Builder<NewsletterSubscriber>  $query
     * @return Builder<NewsletterSubscriber>
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }
}
