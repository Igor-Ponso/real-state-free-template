<?php

namespace App\Http\Resources;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a City model into the data structure used by the landing page neighborhood carousel.
 *
 * Includes the published properties count (via `whenCounted`) so the frontend can display
 * how many listings are available in each featured city.
 *
 * @mixin City
 */
class CityResource extends JsonResource
{
    /**
     * Transform the City model into the neighborhood carousel payload.
     *
     * @return array{
     *     id: int,
     *     name: string,
     *     state: string,
     *     slug: string,
     *     image: string|null,
     *     properties_count: int|null,
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'state' => $this->state,
            'slug' => $this->slug,
            'image' => $this->image_path,
            'properties_count' => $this->whenCounted('properties'),
        ];
    }
}
