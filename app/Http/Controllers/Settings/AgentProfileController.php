<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateAgentProfileRequest;
use App\Models\AgentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Settings page for agents to manage their public profile: bio, contact,
 * license, specializations, social links, and profile photo.
 *
 * If the user is an agent without an AgentProfile row yet (edge case —
 * user was manually assigned the role without a profile created), an
 * empty profile is auto-instantiated on first visit to avoid a 404.
 */
class AgentProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin|agent'),
        ];
    }

    public function edit(Request $request): Response
    {
        $profile = $this->resolveProfile($request);

        return Inertia::render('settings/AgentProfile', [
            'profile' => [
                'id' => $profile->id,
                'slug' => $profile->slug,
                'bio' => $profile->bio,
                'phone' => $profile->phone,
                'license_number' => $profile->license_number,
                'specializations' => $profile->specializations ?? [],
                'social_links' => (object) ($profile->social_links ?? []),
                'photo_url' => $profile->getFirstMediaUrl('profile_photo', 'card') ?: null,
                'public_url' => $profile->slug ? route('agents.show', ['agentProfile' => $profile->slug]) : null,
            ],
        ]);
    }

    public function update(UpdateAgentProfileRequest $request): RedirectResponse
    {
        $profile = $this->resolveProfile($request);

        $profile->fill($request->validated())->save();

        return back()->with('success', 'Profile updated.');
    }

    /**
     * Upload or replace the agent's profile photo.
     */
    public function uploadPhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => [
                'required',
                File::image()
                    ->max(4 * 1024)
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(200)
                            ->minHeight(200)
                            ->maxWidth(4000)
                            ->maxHeight(4000)
                    ),
                'mimes:jpeg,png,webp',
            ],
        ]);

        $profile = $this->resolveProfile($request);

        $profile->addMediaFromRequest('photo')->toMediaCollection('profile_photo');

        return back()->with('success', 'Photo updated.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $profile = $this->resolveProfile($request);
        $profile->clearMediaCollection('profile_photo');

        return back()->with('success', 'Photo removed.');
    }

    /**
     * Return the authenticated user's AgentProfile, creating a blank one
     * if the user has the agent role but no profile yet.
     */
    private function resolveProfile(Request $request): AgentProfile
    {
        $user = $request->user();

        return AgentProfile::firstOrCreate(['user_id' => $user->id]);
    }
}
