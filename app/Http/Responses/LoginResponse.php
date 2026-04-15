<?php

namespace App\Http\Responses;

use Illuminate\Http\Response;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Routes authenticated users to the dashboard appropriate for their role.
 *
 * Admins and agents land on the admin dashboard (stats, inquiries, property
 * management). Clients land on their personal dashboard (saved properties,
 * inquiry history, recent views).
 */
class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Response
    {
        $user = $request->user();

        if ($user?->hasAnyRole(['admin', 'agent'])) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}
