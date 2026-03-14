<?php

namespace App\Services;

use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerDeliveryEvent;

class BulkMailerWebhookEventService
{
    public function ingest(string $provider, array $payload): BulkMailerDeliveryEvent
    {
        $normalized = $this->normalize($provider, $payload);

        $contact = BulkMailerContact::where('email', $normalized['email'])->first();

        $recipient = BulkMailerCampaignRecipient::query()
            ->where('email', $normalized['email'])
            ->latest()
            ->first();

        if (in_array($normalized['event_type'], ['bounce', 'complaint'], true) && $contact) {
            $contact->update([
                'status' => $normalized['event_type'] === 'bounce' ? 'bounced' : 'suppressed',
                'bounced_at' => now(),
                'suppression_reason' => ucfirst($normalized['event_type']).' webhook event',
            ]);
        }

        return BulkMailerDeliveryEvent::create([
            'bulk_mailer_campaign_id' => $recipient?->bulk_mailer_campaign_id,
            'bulk_mailer_contact_id' => $contact?->id,
            'bulk_mailer_campaign_recipient_id' => $recipient?->id,
            'email' => $normalized['email'],
            'event_type' => $normalized['event_type'],
            'provider' => $provider,
            'provider_event_id' => $normalized['provider_event_id'],
            'message' => $normalized['message'],
            'payload' => $payload,
            'event_at' => $normalized['event_at'],
        ]);
    }

    protected function normalize(string $provider, array $payload): array
    {
        $provider = strtolower($provider);

        return match ($provider) {
            'mailgun' => [
                'email' => strtolower((string) ($payload['recipient'] ?? '')),
                'event_type' => strtolower((string) ($payload['event'] ?? 'unknown')),
                'provider_event_id' => (string) ($payload['id'] ?? ''),
                'message' => (string) ($payload['reason'] ?? $payload['description'] ?? ''),
                'event_at' => now(),
            ],
            'postmark' => [
                'email' => strtolower((string) ($payload['Email'] ?? '')),
                'event_type' => $this->mapPostmarkType((string) ($payload['RecordType'] ?? '')),
                'provider_event_id' => (string) ($payload['MessageID'] ?? ''),
                'message' => (string) ($payload['Description'] ?? ''),
                'event_at' => now(),
            ],
            'ses' => [
                'email' => strtolower((string) data_get($payload, 'mail.destination.0', '')),
                'event_type' => $this->mapSesType((string) ($payload['eventType'] ?? '')),
                'provider_event_id' => (string) data_get($payload, 'mail.messageId', ''),
                'message' => (string) data_get($payload, 'bounce.bouncedRecipients.0.diagnosticCode', ''),
                'event_at' => now(),
            ],
            default => [
                'email' => strtolower((string) ($payload['email'] ?? '')),
                'event_type' => strtolower((string) ($payload['event_type'] ?? 'unknown')),
                'provider_event_id' => (string) ($payload['event_id'] ?? ''),
                'message' => (string) ($payload['message'] ?? ''),
                'event_at' => now(),
            ],
        };
    }

    protected function mapPostmarkType(string $type): string
    {
        return match (strtolower($type)) {
            'bounce' => 'bounce',
            'delivery' => 'delivered',
            'open' => 'open',
            'click' => 'click',
            'spamcomplaint' => 'complaint',
            default => strtolower($type ?: 'unknown'),
        };
    }

    protected function mapSesType(string $type): string
    {
        return match (strtolower($type)) {
            'bounce' => 'bounce',
            'complaint' => 'complaint',
            'delivery' => 'delivered',
            'open' => 'open',
            'click' => 'click',
            default => strtolower($type ?: 'unknown'),
        };
    }
}