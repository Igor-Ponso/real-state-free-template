<?php

namespace App\Actions\Inquiry;

use App\Models\Inquiry;
use App\Models\InquiryStatus;

/**
 * Updates an inquiry's status and sets replied_at timestamp
 * when transitioning to the "replied" status for the first time.
 */
class UpdateInquiryStatusAction
{
    /**
     * Execute the status update.
     */
    public function execute(Inquiry $inquiry, int $statusId): Inquiry
    {
        $isReplied = InquiryStatus::where('id', $statusId)->where('slug', 'replied')->exists();

        $inquiry->update([
            'inquiry_status_id' => $statusId,
            'replied_at' => $isReplied ? ($inquiry->replied_at ?? now()) : $inquiry->replied_at,
        ]);

        return $inquiry;
    }
}
