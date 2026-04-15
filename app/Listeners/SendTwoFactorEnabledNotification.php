<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\Security\TwoFactorEnabled as TwoFactorEnabledNotification;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;

/**
 * Fires after a user successfully confirms their 2FA setup with a valid TOTP code.
 *
 * We listen to `TwoFactorAuthenticationConfirmed` rather than `TwoFactorAuthenticationEnabled`
 * because the Enabled event fires as soon as the QR code is generated — before the user
 * actually proves they can produce valid codes. Confirming means 2FA is truly active.
 */
class SendTwoFactorEnabledNotification
{
    public function handle(TwoFactorAuthenticationConfirmed $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $user->notify(new TwoFactorEnabledNotification);
    }
}
