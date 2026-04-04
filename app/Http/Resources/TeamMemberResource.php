<?php

namespace App\Http\Resources;

use App\Models\AgentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms an AgentProfile model into the data structure used by the landing page team section.
 *
 * Resolves the related User (via `whenLoaded`) for name and email, picks a deterministic
 * placeholder avatar from pravatar.cc based on the agent ID, and surfaces the first
 * specialization as the display role.
 *
 * @mixin AgentProfile
 */
class TeamMemberResource extends JsonResource
{
    /**
     * Unique avatar seeds per agent — each produces a distinct face via pravatar.cc.
     * In production, replace with actual profile photos via MediaLibrary.
     *
     * @var int[]
     */
    private const AVATAR_SEEDS = [3, 5, 8, 12, 16, 25, 32, 36, 41, 49];

    /**
     * Transform the AgentProfile model into the team member payload.
     *
     * @return array{
     *     id: int,
     *     name: string|null,
     *     role: string,
     *     bio: string|null,
     *     image: string,
     *     email: string|null,
     *     social_links: array<string, string>|null,
     * }
     */
    public function toArray(Request $request): array
    {
        $seed = self::AVATAR_SEEDS[$this->id % count(self::AVATAR_SEEDS)];

        return [
            'id' => $this->id,
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
            'role' => $this->specializations[0] ?? 'Real Estate Agent',
            'bio' => $this->bio,
            'image' => "https://i.pravatar.cc/400?img={$seed}",
            'email' => $this->whenLoaded('user', fn () => $this->user->email),
            'social_links' => $this->social_links,
        ];
    }
}
