<?php

namespace App\Http\Resources\Admin;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin inquiry resource — includes decrypted PII fields.
 *
 * CipherSweet auto-decrypts name, email, phone when the model is loaded.
 *
 * @mixin Inquiry
 */
class InquiryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'status' => $this->whenLoaded('inquiryStatus', fn () => $this->inquiryStatus->name),
            'status_slug' => $this->whenLoaded('inquiryStatus', fn () => $this->inquiryStatus->slug),
            'inquiry_status_id' => $this->inquiry_status_id,
            'property_title' => $this->whenLoaded('property', fn () => $this->property->title),
            'property_slug' => $this->whenLoaded('property', fn () => $this->property->slug),
            'replied_at' => $this->replied_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
