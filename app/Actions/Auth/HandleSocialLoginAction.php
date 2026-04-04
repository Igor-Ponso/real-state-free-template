<?php

namespace App\Actions\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class HandleSocialLoginAction
{
    /**
     * Handle a social login callback.
     *
     * Find or create the user, link the social account, and log them in.
     */
    public function handle(string $provider, SocialiteUser $socialiteUser): User
    {
        return DB::transaction(function () use ($provider, $socialiteUser): User {
            $socialAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($socialAccount) {
                $this->updateTokens($socialAccount, $socialiteUser);
                Auth::login($socialAccount->user, remember: true);

                return $socialAccount->user;
            }

            $user = User::whereBlind('email', 'email_index', $socialiteUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'name' => $this->resolveName($socialiteUser),
                    'email' => $socialiteUser->getEmail(),
                ]);

                $user->markEmailAsVerified();
            } elseif (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $socialiteUser->getId(),
                'provider_token' => $socialiteUser->token,
                'provider_refresh_token' => $socialiteUser->refreshToken ?? null,
            ]);

            Auth::login($user, remember: true);

            return $user;
        });
    }

    /**
     * Resolve the user's name from the social provider.
     *
     * Apple only sends the name on the first authorization.
     */
    private function resolveName(SocialiteUser $socialiteUser): string
    {
        return $socialiteUser->getName()
            ?? $socialiteUser->getNickname()
            ?? explode('@', $socialiteUser->getEmail())[0];
    }

    /**
     * Update the social account tokens.
     */
    private function updateTokens(SocialAccount $socialAccount, SocialiteUser $socialiteUser): void
    {
        $socialAccount->update([
            'provider_token' => $socialiteUser->token,
            'provider_refresh_token' => $socialiteUser->refreshToken ?? null,
        ]);
    }
}
