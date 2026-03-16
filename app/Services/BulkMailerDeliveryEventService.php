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
        $normalizedEmail = strtolower(trim($email));

        $contact = BulkMailerContact::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        $recipient = BulkMailerCampaignRecipient::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->latest()
            ->first();

        if ($contact) {
            $contact->update([
                'status' => 'bounced',
                'bounced_at' => now(),
                'suppression_reason' => $message ?: 'Bounce event',
            ]);
        }

        if ($recipient) {
            $recipient->update([
                'status' => BulkMailerCampaignRecipientStatus::Failed,
                'error_message' => $message ?: 'Bounce event recorded.',
            ]);
        }

        return BulkMailerDeliveryEvent::create([
            'bulk_mailer_campaign_id' => $recipient?->bulk_mailer_campaign_id,
            'bulk_mailer_contact_id' => $contact?->id,
            'bulk_mailer_campaign_recipient_id' => $recipient?->id,
            'email' => $normalizedEmail,
            'event_type' => 'bounce',
            'message' => $message ?: 'Bounce event recorded.',
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
            'email' => strtolower(trim((string) $recipient->email)),
            'event_type' => 'sent',
            'message' => 'Message accepted by SMTP server.',
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
            'email' => strtolower(trim((string) $recipient->email)),
            'event_type' => 'failed',
            'message' => mb_substr($message, 0, 1000),
            'payload' => null,
            'event_at' => now(),
        ]);
    }
}