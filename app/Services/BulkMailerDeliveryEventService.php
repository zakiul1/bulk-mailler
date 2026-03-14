<?php

namespace App\Services;

use App\Enums\BulkMailerCampaignRecipientStatus;
use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerDeliveryEvent;

class BulkMailerDeliveryEventService
{
    public function logBounce(
        string $email,
        ?string $message = null,
        ?array $payload = null
    ): BulkMailerDeliveryEvent {
        $contact = BulkMailerContact::where('email', strtolower(trim($email)))->first();

        $recipient = BulkMailerCampaignRecipient::query()
            ->where('email', strtolower(trim($email)))
            ->latest()
            ->first();

        if ($contact) {
            $contact->update([
                'status' => 'bounced',
                'bounced_at' => now(),
                'suppression_reason' => 'Bounce event',
            ]);
        }

        if ($recipient && $recipient->status !== BulkMailerCampaignRecipientStatus::Sent) {
            $recipient->update([
                'status' => BulkMailerCampaignRecipientStatus::Failed,
                'error_message' => $message ?: 'Bounce event recorded.',
            ]);
        }

        return BulkMailerDeliveryEvent::create([
            'bulk_mailer_campaign_id' => $recipient?->bulk_mailer_campaign_id,
            'bulk_mailer_contact_id' => $contact?->id,
            'bulk_mailer_campaign_recipient_id' => $recipient?->id,
            'email' => strtolower(trim($email)),
            'event_type' => 'bounce',
            'message' => $message,
            'payload' => $payload,
            'event_at' => now(),
        ]);
    }

    public function logSent(BulkMailerCampaignRecipient $recipient): BulkMailerDeliveryEvent
    {
        return BulkMailerDeliveryEvent::create([
            'bulk_mailer_campaign_id' => $recipient->bulk_mailer_campaign_id,
            'bulk_mailer_contact_id' => $recipient->bulk_mailer_contact_id,
            'bulk_mailer_campaign_recipient_id' => $recipient->id,
            'email' => $recipient->email,
            'event_type' => 'sent',
            'message' => 'Message sent successfully.',
            'payload' => null,
            'event_at' => now(),
        ]);
    }

    public function logFailed(BulkMailerCampaignRecipient $recipient, string $message): BulkMailerDeliveryEvent
    {
        return BulkMailerDeliveryEvent::create([
            'bulk_mailer_campaign_id' => $recipient->bulk_mailer_campaign_id,
            'bulk_mailer_contact_id' => $recipient->bulk_mailer_contact_id,
            'bulk_mailer_campaign_recipient_id' => $recipient->id,
            'email' => $recipient->email,
            'event_type' => 'failed',
            'message' => $message,
            'payload' => null,
            'event_at' => now(),
        ]);
    }
}