<?php

namespace App\Services;

use App\Enums\BulkMailerCampaignRecipientStatus;
use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerDeliveryEvent;
use Carbon\Carbon;

class BulkMailerWebhookEventService
{
    public function ingest(string $provider, array $payload): BulkMailerDeliveryEvent
    {
        $normalized = $this->normalize($provider, $payload);

        $contact = BulkMailerContact::query()
            ->whereRaw('LOWER(email) = ?', [$normalized['email']])
            ->first();

        $recipient = BulkMailerCampaignRecipient::query()
            ->whereRaw('LOWER(email) = ?', [$normalized['email']])
            ->latest()
            ->first();

        if ($contact && in_array($normalized['event_type'], ['bounce', 'complaint'], true)) {
            $contact->update([
                'status' => $normalized['event_type'] === 'bounce' ? 'bounced' : 'suppressed',
                'bounced_at' => now(),
                'suppression_reason' => $normalized['message'] ?: ucfirst($normalized['event_type']) . ' webhook event',
            ]);
        }

        if ($recipient) {
            if (in_array($normalized['event_type'], ['bounce', 'complaint'], true)) {
                $recipient->update([
                    'status' => BulkMailerCampaignRecipientStatus::Failed,
                    'error_message' => $normalized['message'] ?: ucfirst($normalized['event_type']) . ' webhook event',
                ]);
            }

            if ($normalized['event_type'] === 'delivered' && ! $recipient->sent_at) {
                $recipient->update([
                    'sent_at' => now(),
                ]);
            }
        }

        return BulkMailerDeliveryEvent::create([
            'bulk_mailer_campaign_id' => $recipient?->bulk_mailer_campaign_id,
            'bulk_mailer_contact_id' => $contact?->id,
            'bulk_mailer_campaign_recipient_id' => $recipient?->id,
            'email' => $normalized['email'],
            'event_type' => $normalized['event_type'],
            'provider' => strtolower($provider),
            'provider_event_id' => $normalized['provider_event_id'],
            'message' => $normalized['message'],
            'payload' => $payload,
            'event_at' => $normalized['event_at'],
        ]);
    }

    protected function normalize(string $provider, array $payload): array
    {
        $provider = strtolower(trim($provider));

        return match ($provider) {
            'mailgun' => [
                'email' => strtolower(trim((string) ($payload['recipient'] ?? ''))),
                'event_type' => $this->mapMailgunType((string) ($payload['event'] ?? 'unknown')),
                'provider_event_id' => (string) ($payload['id'] ?? ''),
                'message' => (string) ($payload['reason'] ?? $payload['description'] ?? ''),
                'event_at' => $this->parseEventAt($payload['timestamp'] ?? null),
            ],
            'postmark' => [
                'email' => strtolower(trim((string) ($payload['Email'] ?? ''))),
                'event_type' => $this->mapPostmarkType((string) ($payload['RecordType'] ?? '')),
                'provider_event_id' => (string) ($payload['MessageID'] ?? ''),
                'message' => (string) ($payload['Description'] ?? ''),
                'event_at' => $this->parseEventAt($payload['ReceivedAt'] ?? null),
            ],
            'ses' => [
                'email' => strtolower(trim((string) data_get($payload, 'mail.destination.0', ''))),
                'event_type' => $this->mapSesType((string) ($payload['eventType'] ?? '')),
                'provider_event_id' => (string) data_get($payload, 'mail.messageId', ''),
                'message' => (string) (
                    data_get($payload, 'bounce.bouncedRecipients.0.diagnosticCode')
                    ?: data_get($payload, 'complaint.complainedRecipients.0.emailAddress')
                    ?: ''
                ),
                'event_at' => $this->parseEventAt(
                    data_get($payload, 'mail.timestamp')
                    ?: data_get($payload, 'delivery.timestamp')
                    ?: data_get($payload, 'bounce.timestamp')
                    ?: data_get($payload, 'complaint.timestamp')
                ),
            ],
            default => [
                'email' => strtolower(trim((string) ($payload['email'] ?? ''))),
                'event_type' => strtolower((string) ($payload['event_type'] ?? 'unknown')),
                'provider_event_id' => (string) ($payload['event_id'] ?? ''),
                'message' => (string) ($payload['message'] ?? ''),
                'event_at' => $this->parseEventAt($payload['event_at'] ?? null),
            ],
        };
    }

    protected function mapMailgunType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'accepted' => 'accepted',
            'delivered' => 'delivered',
            'failed' => 'failed',
            'rejected' => 'failed',
            'bounced', 'permanent_fail', 'temporary_fail' => 'bounce',
            'complained' => 'complaint',
            'opened' => 'open',
            'clicked' => 'click',
            default => strtolower(trim($type ?: 'unknown')),
        };
    }

    protected function mapPostmarkType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'bounce' => 'bounce',
            'delivery' => 'delivered',
            'open' => 'open',
            'click' => 'click',
            'spamcomplaint' => 'complaint',
            default => strtolower(trim($type ?: 'unknown')),
        };
    }

    protected function mapSesType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'send' => 'accepted',
            'reject' => 'failed',
            'bounce' => 'bounce',
            'complaint' => 'complaint',
            'delivery' => 'delivered',
            'open' => 'open',
            'click' => 'click',
            default => strtolower(trim($type ?: 'unknown')),
        };
    }

    protected function parseEventAt(mixed $value): Carbon
    {
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                // fall through
            }
        }

        return now();
    }
}