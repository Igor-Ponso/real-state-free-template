<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    /**
     * Unique avatar seeds per agent — each produces a distinct face via pravatar.cc.
     * In production, replace with actual profile photos via MediaLibrary.
     */
    private const AVATAR_SEEDS = [3, 5, 8, 12, 16, 25, 32, 36, 41, 49];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
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
