<?php

namespace App\Http\Middleware;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'socialProviders' => array_values(array_filter(
                ['google', 'github', 'facebook', 'apple'],
                fn (string $provider): bool => ! empty(config("services.{$provider}.client_id")),
            )),
            'unreadInquiriesCount' => fn () => $request->user()?->hasAnyRole(['admin', 'agent'])
                ? Inquiry::new()
                    ->when($request->user()->hasRole('agent'), fn ($q) => $q->whereHas('property', fn ($q) => $q->where('user_id', $request->user()->id)))
                    ->count()
                : 0,
        ];
    }
}
