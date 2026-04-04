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
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class WelcomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'featuredProperties' => fn () => FeaturedPropertyResource::collection(
                Property::with(['city', 'listingType', 'propertyType'])
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
            'stats' => fn () => [
                'properties_sold' => Property::whereHas('propertyStatus', fn ($q) => $q->where('slug', 'sold'))->count(),
                'clients' => User::whereHas('roles', fn ($q) => $q->where('name', 'client'))->count(),
                'agents' => AgentProfile::count(),
                'cities' => City::featured()->count(),
            ],
        ]);
    }
}
