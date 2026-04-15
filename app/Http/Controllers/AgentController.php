<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgentResource;
use App\Http\Resources\FeaturedPropertyResource;
use App\Models\AgentProfile;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Public-facing agent profile page at /agents/{slug}.
 *
 * Shows the agent's photo, bio, license, specializations, social links,
 * and their currently-published listings. Route model binding uses the
 * slug (see AgentProfile::getRouteKeyName).
 *
 * Only agents with at least one published property are meaningful to
 * surface — but we don't 404 for empty profiles; the page shows an
 * "no current listings" state so agents without inventory still have
 * a functional public page.
 */
class AgentController extends Controller
{
    public function show(AgentProfile $agentProfile): Response
    {
        $agentProfile->load('user');

        // Properties are eager-scoped via PublishedScope (attribute on Property model),
        // so unpublished/sold listings are automatically excluded here.
        $properties = $agentProfile->user
            ->properties()
            ->with(['city', 'propertyType', 'listingType', 'media'])
            ->latest('published_at')
            ->take(12)
            ->get();

        return Inertia::render('Agents/Show', [
            'agent' => (new AgentResource($agentProfile))->resolve(),
            'listings' => FeaturedPropertyResource::collection($properties)->resolve(),
            'canonicalUrl' => route('agents.show', ['agentProfile' => $agentProfile->slug]),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
