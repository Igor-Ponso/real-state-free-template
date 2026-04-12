<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifies the property agent when a new inquiry is submitted.
 *
 * Sent to the User who listed the property (via property.agent relationship).
 * Uses a Markdown template for branded email rendering.
 */
class InquiryReceived extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Inquiry $inquiry,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Inquiry: '.$this->inquiry->property?->title)
            ->markdown('mail.inquiry.received', [
                'inquiry' => $this->inquiry,
                'property' => $this->inquiry->property,
                'adminUrl' => url('/admin/inquiries/'.$this->inquiry->id),
            ]);
    }
}
