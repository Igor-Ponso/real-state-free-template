<?php

namespace App\Http\Controllers;

use App\Http\Resources\CityResource;
use App\Http\Resources\FeaturedPropertyResource;
use App\Http\Resources\TeamMemberResource;
use App\Models\AgentProfile;
use App\Models\City;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Single-action controller that serves the public landing page.
 *
 * Aggregates featured properties, neighborhood cities, team members, and site-wide
 * statistics into a single Inertia response rendered by the Welcome Vue page.
 */
class WelcomeController extends Controller
{
    /**
     * Render the landing page with all section data.
     *
     * Returns an Inertia response containing:
     * - **canRegister** — whether user registration is enabled (Fortify feature flag)
     * - **featuredProperties** — latest 3 published+featured properties (via FeaturedPropertyResource)
     * - **neighborhoods** — featured cities with published property counts (via CityResource)
     * - **teamMembers** — featured agent profiles with user details (via TeamMemberResource)
     * - **stats** — aggregate counts: properties sold, clients, agents, featured cities
     *
     * All collection props use lazy closures (`fn () =>`) for deferred evaluation
     * and call `->resolve()` to avoid Inertia's `{ data: [...] }` wrapper.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'featuredProperties' => fn () => FeaturedPropertyResource::collection(
                Property::with(['city', 'listingType', 'propertyType', 'media'])
                    ->published()
                    ->featured()
                    ->latest('published_at')
                    ->take(3)
                    ->get(),
            )->resolve(),
            'neighborhoods' => fn () => CityResource::collection(
                City::withCount(['properties' => fn ($query) => $query->published()])
                    ->featured()
                    ->ordered()
                    ->get(),
            )->resolve(),
            'teamMembers' => fn () => TeamMemberResource::collection(
                AgentProfile::with('user')
                    ->featured()
                    ->get(),
            )->resolve(),
            'stats' => fn () => Cache::flexible('home_stats', [1800, 3600], fn () => [
                'properties_sold' => Property::whereHas('propertyStatus', fn ($q) => $q->where('slug', 'sold'))->count(),
                'clients' => User::whereHas('roles', fn ($q) => $q->where('name', 'client'))->count(),
                'agents' => AgentProfile::count(),
                'cities' => City::featured()->count(),
            ]),
        ]);
    }
}
