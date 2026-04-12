<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifies the inquirer when the agent replies to their inquiry.
 *
 * Sent as an on-demand notification to the inquiry's email address,
 * since the inquirer may not be a registered User.
 */
class InquiryReplied extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Inquiry $inquiry,
        public readonly string $agentName,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Re: Your inquiry about '.$this->inquiry->property?->title)
            ->markdown('mail.inquiry.replied', [
                'inquiry' => $this->inquiry,
                'property' => $this->inquiry->property,
                'agentName' => $this->agentName,
                'propertyUrl' => url('/properties/'.$this->inquiry->property?->slug),
            ]);
    }
}
