<?php

namespace App\Http\Resources;

use App\Models\AgentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Landing page Team section payload.
 *
 * Prefers the agent's uploaded MediaLibrary photo (card conversion) and falls
 * back to a deterministic pravatar.cc seed so the demo keeps working without
 * real uploads. `image_is_placeholder` tells the frontend when it's showing
 * a placeholder so it can optionally render a subtle hint.
 *
 * The `slug` is included so the team card can deep-link to /agents/{slug}.
 *
 * @mixin AgentProfile
 */
class TeamMemberResource extends JsonResource
{
    /**
     * Placeholder avatar seeds — each produces a distinct face via pravatar.cc.
     *
     * @var int[]
     */
    private const AVATAR_SEEDS = [3, 5, 8, 12, 16, 25, 32, 36, 41, 49];

    public function toArray(Request $request): array
    {
        $uploaded = $this->getFirstMediaUrl('profile_photo', 'card') ?: null;
        $fallbackSeed = self::AVATAR_SEEDS[$this->id % count(self::AVATAR_SEEDS)];

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
            'role' => $this->specializations[0] ?? 'Real Estate Agent',
            'bio' => $this->bio,
            'image' => $uploaded ?? "https://i.pravatar.cc/400?img={$fallbackSeed}",
            'image_is_placeholder' => $uploaded === null,
            'email' => $this->whenLoaded('user', fn () => $this->user->email),
            'social_links' => $this->social_links,
        ];
    }
}
