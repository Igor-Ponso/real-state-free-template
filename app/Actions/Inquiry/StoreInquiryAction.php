<?php

namespace App\Actions\Inquiry;

use App\Models\Inquiry;
use App\Models\InquiryStatus;

/**
 * Creates a new property inquiry with the default "new" status.
 *
 * Handles honeypot detection to silently reject spam submissions
 * while returning a successful-looking response to the bot.
 */
class StoreInquiryAction
{
    /**
     * Execute the inquiry creation.
     *
     * @param  array{property_id: int, name: string, email: string, phone: ?string, message: string, honeypot: ?string}  $data
     * @return array{created: bool, message: string}
     */
    public function execute(array $data, ?int $userId = null): array
    {
        if (! empty($data['honeypot'])) {
            return ['created' => false, 'message' => 'Thank you for your inquiry.'];
        }

        $newStatus = InquiryStatus::where('slug', 'new')->firstOrFail();

        Inquiry::create([
            'property_id' => $data['property_id'],
            'user_id' => $userId,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'inquiry_status_id' => $newStatus->id,
        ]);

        return ['created' => true, 'message' => 'Thank you for your inquiry. We will get back to you shortly.'];
    }
}
