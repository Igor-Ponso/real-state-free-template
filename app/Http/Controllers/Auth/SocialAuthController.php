<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleSocialLoginAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class SocialAuthController extends Controller
{
    /**
     * Redirect to the social provider's OAuth page.
     */
    public function redirect(string $provider): SymfonyRedirect
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the social provider.
     */
    public function callback(string $provider, HandleSocialLoginAction $action): RedirectResponse
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::warning('Social login failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->with('status', 'Unable to authenticate with '.ucfirst($provider).'. Please try again.');
        }

        $action->handle($provider, $socialiteUser);

        return redirect()->intended(config('fortify.home'));
    }
}
