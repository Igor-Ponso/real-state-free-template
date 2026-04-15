<?php

namespace App\Http\Resources;

use App\Models\AgentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public agent profile payload for /agents/{slug}.
 *
 * Photo URL resolves to the uploaded profile_photo card conversion; when no photo
 * exists we fall back to a deterministic pravatar seed so the demo never shows
 * a broken image. `photo_is_placeholder` lets the frontend optionally dim or
 * overlay a "sample photo" hint.
 *
 * @mixin AgentProfile
 */
class AgentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $photoUrl = $this->getFirstMediaUrl('profile_photo', 'card') ?: null;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
            'email' => $this->whenLoaded('user', fn () => $this->user->email),
            'phone' => $this->phone,
            'bio' => $this->bio,
            'license_number' => $this->license_number,
            'specializations' => $this->specializations ?? [],
            'social_links' => (object) ($this->social_links ?? []),
            'photo_url' => $photoUrl ?? "https://i.pravatar.cc/400?img={$this->id}",
            'photo_is_placeholder' => $photoUrl === null,
        ];
    }
}
