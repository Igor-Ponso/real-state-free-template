<?php

namespace App\Actions\Inquiry;

use App\Models\Inquiry;
use App\Models\InquiryStatus;
use App\Models\User;
use App\Notifications\InquiryReplied;
use Illuminate\Support\Facades\Notification;

/**
 * Handles agent reply to a property inquiry.
 *
 * Saves the reply text, updates status to 'replied', sets replied_at timestamp,
 * and sends an email notification to the inquirer.
 */
class ReplyToInquiryAction
{
    public function execute(Inquiry $inquiry, string $reply, User $agent): Inquiry
    {
        $repliedStatus = InquiryStatus::where('slug', 'replied')->firstOrFail();

        $inquiry->update([
            'reply' => $reply,
            'inquiry_status_id' => $repliedStatus->id,
            'replied_at' => $inquiry->replied_at ?? now(),
        ]);

        $inquiry->load('property');

        // Send reply notification to the inquirer's email (on-demand notification
        // since the inquirer might not be a registered User)
        Notification::route('mail', $inquiry->email)
            ->notify(new InquiryReplied($inquiry, $agent->name));

        return $inquiry;
    }
}
