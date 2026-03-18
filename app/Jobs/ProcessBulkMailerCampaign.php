<?php

namespace App\Jobs;

use App\Enums\BulkMailerCampaignRecipientStatus;
use App\Enums\BulkMailerCampaignStatus;
use App\Mail\BulkMailerCampaignMail;
use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerSegment;
use App\Models\BulkMailerSmtpDailyUsage;
use App\Services\BulkMailerDeliveryEventService;
use App\Services\BulkMailerSegmentService;
use App\Services\BulkMailerSmtpHealthService;
use App\Services\BulkMailerSmtpRotationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\MailManager;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class ProcessBulkMailerCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public function __construct(public int $campaignId)
    {
    }

    public function handle(
        BulkMailerDeliveryEventService $eventService,
        BulkMailerSegmentService $segmentService,
        BulkMailerSmtpRotationService $rotationService,
        BulkMailerSmtpHealthService $smtpHealthService,
        MailManager $mailManager
    ): void {
        $campaign = BulkMailerCampaign::with(['template', 'lists', 'segment', 'smtpGroup.smtpAccounts'])->find($this->campaignId);

        if (!$campaign || !$campaign->template || !$campaign->smtpGroup) {
            return;
        }

        if ($campaign->status?->value === BulkMailerCampaignStatus::Cancelled->value) {
            return;
        }

        $campaign->update([
            'status' => BulkMailerCampaignStatus::Processing,
            'started_at' => $campaign->started_at ?: now(),
            'completed_at' => null,
        ]);

        $this->syncRecipients($campaign, $segmentService);
        $this->syncLiveCampaignCounts($campaign);

        $pendingRecipients = BulkMailerCampaignRecipient::query()
            ->with('contact')
            ->where('bulk_mailer_campaign_id', $campaign->id)
            ->where('status', BulkMailerCampaignRecipientStatus::Pending->value)
            ->get();

        if ($pendingRecipients->isEmpty()) {
            $this->refreshCampaignCountsAndStatus($campaign);

            return;
        }

        foreach ($pendingRecipients as $recipient) {
            $campaign->refresh();

            if ($campaign->status?->value === BulkMailerCampaignStatus::Paused->value) {
                $this->syncLiveCampaignCounts($campaign, BulkMailerCampaignStatus::Paused);

                return;
            }

            if ($campaign->status?->value === BulkMailerCampaignStatus::Cancelled->value) {
                $this->syncLiveCampaignCounts($campaign, BulkMailerCampaignStatus::Cancelled, true);

                return;
            }

            $smtp = $rotationService->resolveForCampaign($campaign->fresh('smtpGroup.smtpAccounts'));

            if (!$smtp) {
                $campaign->update([
                    'status' => BulkMailerCampaignStatus::Paused,
                ]);

                $this->syncLiveCampaignCounts($campaign, BulkMailerCampaignStatus::Paused);

                return;
            }

            try {
                if (!$recipient->contact) {
                    $recipient->update([
                        'bulk_mailer_smtp_account_id' => $smtp->id,
                        'status' => BulkMailerCampaignRecipientStatus::Failed,
                        'error_message' => 'Recipient contact record not found.',
                    ]);

                    $smtpHealthService->markFailure($smtp, 'Recipient contact record not found.');
                    $eventService->logFailed($recipient, 'Recipient contact record not found.');
                    $this->syncLiveCampaignCounts($campaign);

                    continue;
                }

                $normalizedRecipientEmail = $this->normalizeEmail($recipient->email ?: $recipient->contact->email);

                if (!$this->isValidEmail($normalizedRecipientEmail)) {
                    $recipient->update([
                        'bulk_mailer_smtp_account_id' => $smtp->id,
                        'status' => BulkMailerCampaignRecipientStatus::Failed,
                        'error_message' => 'Recipient email address is invalid.',
                    ]);

                    $smtpHealthService->markFailure($smtp, 'Recipient email address is invalid.');
                    $eventService->logFailed($recipient, 'Recipient email address is invalid.');
                    $this->syncLiveCampaignCounts($campaign);

                    continue;
                }

                $subjectTemplate = $campaign->subject ?: $campaign->template->subject;

                if ($campaign->ab_testing_enabled) {
                    $subjectTemplate = $recipient->subject_variant === 'B'
                        ? ($campaign->subject_b ?: $subjectTemplate)
                        : ($campaign->subject_a ?: $subjectTemplate);
                }

                $renderedSubject = $this->replaceVariables((string) $subjectTemplate, $recipient->contact);

                $htmlSource = $campaign->template->html_content;
                $textSource = $campaign->template->text_content;

                $renderedHtml = filled($htmlSource)
                    ? $this->replaceVariables($htmlSource, $recipient->contact)
                    : '';

                $renderedText = filled($textSource)
                    ? $this->replaceVariables($textSource, $recipient->contact)
                    : '';

                if (!filled($renderedHtml) && filled($renderedText)) {
                    $renderedHtml = nl2br(e($renderedText));
                }

                $this->configureCampaignMailer($smtp);

                $mailManager->purge('bulk_mailer_campaign');

                Mail::mailer('bulk_mailer_campaign')
                    ->to($normalizedRecipientEmail)
                    ->send(new BulkMailerCampaignMail(
                        smtp: $smtp,
                        campaign: $campaign,
                        contact: $recipient->contact,
                        subjectLine: $renderedSubject,
                        htmlBody: $renderedHtml,
                        textBody: filled($renderedText) ? $renderedText : null,
                    ));

                $recipient->update([
                    'email' => $normalizedRecipientEmail,
                    'bulk_mailer_smtp_account_id' => $smtp->id,
                    'status' => BulkMailerCampaignRecipientStatus::Sent,
                    'error_message' => null,
                    'sent_at' => now(),
                ]);

                $this->incrementDailyUsage($smtp->id);
                $smtpHealthService->markSuccess($smtp);
                $eventService->logSent($recipient);
                $this->syncLiveCampaignCounts($campaign);
            } catch (\Throwable $e) {
                $recipient->update([
                    'bulk_mailer_smtp_account_id' => $smtp->id,
                    'status' => BulkMailerCampaignRecipientStatus::Failed,
                    'error_message' => mb_substr($e->getMessage(), 0, 1000),
                ]);

                $smtpHealthService->markFailure($smtp, mb_substr($e->getMessage(), 0, 1000));
                $eventService->logFailed($recipient, mb_substr($e->getMessage(), 0, 1000));
                $this->syncLiveCampaignCounts($campaign);
            } finally {
                $mailManager->purge('bulk_mailer_campaign');
            }
        }

        $this->refreshCampaignCountsAndStatus($campaign);
    }

    protected function syncRecipients(BulkMailerCampaign $campaign, BulkMailerSegmentService $segmentService): void
    {
        $listIds = $campaign->lists->pluck('id')->all();

        $query = BulkMailerContact::query();

        if (!empty($listIds)) {
            $query->whereIn('bulk_mailer_contact_list_id', $listIds);
        }

        if ($campaign->bulk_mailer_segment_id) {
            $segment = BulkMailerSegment::find($campaign->bulk_mailer_segment_id);

            if ($segment) {
                $segmentService->applySegment($query, $segment);
            }
        }

        $contacts = $query
            ->whereNotNull('email')
            ->get()
            ->filter(function (BulkMailerContact $contact) {
                $email = $this->normalizeEmail($contact->email);

                if (!$this->isValidEmail($email)) {
                    return false;
                }

                if ($this->isContactSuppressed($contact)) {
                    return false;
                }

                return true;
            })
            ->unique('id')
            ->values();

        foreach ($contacts as $index => $contact) {
            $email = $this->normalizeEmail($contact->email);

            $existingRecipient = BulkMailerCampaignRecipient::query()
                ->where('bulk_mailer_campaign_id', $campaign->id)
                ->where('bulk_mailer_contact_id', $contact->id)
                ->first();

            $payload = [
                'email' => $email,
                'subject_variant' => $campaign->ab_testing_enabled
                    ? ($index % 2 === 0 ? 'A' : 'B')
                    : null,
            ];

            if (!$existingRecipient) {
                $payload['status'] = BulkMailerCampaignRecipientStatus::Pending->value;

                BulkMailerCampaignRecipient::create([
                    'bulk_mailer_campaign_id' => $campaign->id,
                    'bulk_mailer_contact_id' => $contact->id,
                    ...$payload,
                ]);

                continue;
            }

            if ($existingRecipient->status === BulkMailerCampaignRecipientStatus::Pending->value) {
                $existingRecipient->update($payload);
            }
        }

        $campaign->update([
            'total_recipients' => BulkMailerCampaignRecipient::query()
                ->where('bulk_mailer_campaign_id', $campaign->id)
                ->count(),
        ]);
    }

    protected function incrementDailyUsage(int $smtpId): void
    {
        $usage = BulkMailerSmtpDailyUsage::firstOrCreate(
            [
                'bulk_mailer_smtp_account_id' => $smtpId,
                'usage_date' => now()->toDateString(),
            ],
            [
                'emails_sent' => 0,
            ]
        );

        $usage->increment('emails_sent');
    }

    protected function replaceVariables(string $content, BulkMailerContact $contact): string
    {
        $firstName = $this->getContactAttribute($contact, 'first_name');
        $lastName = $this->getContactAttribute($contact, 'last_name');

        $fullName = trim(implode(' ', array_filter([$firstName, $lastName])));
        $name = filled($fullName) ? $fullName : $contact->email;

        return strtr($content, [
            '{{name}}' => $name,
            '{{email}}' => (string) $contact->email,
            '{{first_name}}' => $firstName,
            '{{last_name}}' => $lastName,
        ]);
    }

    protected function getContactAttribute(BulkMailerContact $contact, string $key): string
    {
        $value = data_get($contact, $key);

        return is_string($value) ? $value : '';
    }

    protected function configureCampaignMailer($smtp): void
    {
        Config::set('mail.mailers.bulk_mailer_campaign', [
            'transport' => 'smtp',
            'host' => $this->normalizeHost($smtp->host),
            'port' => $smtp->port,
            'encryption' => $this->mapEncryption($smtp->encryption),
            'username' => $smtp->username,
            'password' => $smtp->decrypted_password,
            'timeout' => 90,
            'local_domain' => $this->resolveLocalDomain($smtp),
        ]);
    }

    protected function resolveLocalDomain($smtp = null): string
    {
        $configuredEhloDomain = (string) config('mail.ehlo_domain');

        if (filled($configuredEhloDomain) && !$this->isLocalhostHost($configuredEhloDomain)) {
            return $configuredEhloDomain;
        }

        $appHost = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        if (filled($appHost) && !$this->isLocalhostHost($appHost)) {
            return $appHost;
        }

        if ($smtp && filled($smtp->host) && !$this->isLocalhostHost((string) $smtp->host)) {
            return (string) $smtp->host;
        }

        return 'mail.example.com';
    }

    protected function refreshCampaignCountsAndStatus(BulkMailerCampaign $campaign): void
    {
        $campaign->refresh();

        $hasPending = BulkMailerCampaignRecipient::query()
            ->where('bulk_mailer_campaign_id', $campaign->id)
            ->where('status', BulkMailerCampaignRecipientStatus::Pending->value)
            ->exists();

        if ($campaign->status?->value === BulkMailerCampaignStatus::Cancelled->value) {
            $this->syncLiveCampaignCounts($campaign, BulkMailerCampaignStatus::Cancelled, true);

            return;
        }

        if ($campaign->status?->value === BulkMailerCampaignStatus::Paused->value && $hasPending) {
            $this->syncLiveCampaignCounts($campaign, BulkMailerCampaignStatus::Paused);

            return;
        }

        $this->syncLiveCampaignCounts(
            $campaign,
            $hasPending ? BulkMailerCampaignStatus::Paused : BulkMailerCampaignStatus::Completed,
            !$hasPending
        );
    }

    protected function syncLiveCampaignCounts(
        BulkMailerCampaign $campaign,
        ?BulkMailerCampaignStatus $status = null,
        bool $markCompleted = false
    ): void {
        $sentCount = BulkMailerCampaignRecipient::query()
            ->where('bulk_mailer_campaign_id', $campaign->id)
            ->where('status', BulkMailerCampaignRecipientStatus::Sent->value)
            ->count();

        $failedCount = BulkMailerCampaignRecipient::query()
            ->where('bulk_mailer_campaign_id', $campaign->id)
            ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
            ->count();

        $totalRecipients = BulkMailerCampaignRecipient::query()
            ->where('bulk_mailer_campaign_id', $campaign->id)
            ->count();

        $updatePayload = [
            'total_recipients' => $totalRecipients,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ];

        if ($status) {
            $updatePayload['status'] = $status;
        }

        $updatePayload['completed_at'] = $markCompleted ? now() : null;

        $campaign->update($updatePayload);
    }

    protected function normalizeEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }

    protected function normalizeHost(?string $host): string
    {
        $host = mb_strtolower(trim((string) $host));
        $host = preg_replace('#^https?://#i', '', $host);
        $host = trim($host, "/ \t\n\r\0\x0B");

        return $host;
    }

    protected function mapEncryption(?string $encryption): ?string
    {
        $encryption = mb_strtolower(trim((string) $encryption));

        return match ($encryption) {
            '', 'none' => null,
            'starttls' => 'tls',
            'ssl', 'ssl/tls' => 'ssl',
            default => $encryption,
        };
    }

    protected function isValidEmail(?string $email): bool
    {
        if (!filled($email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function isContactSuppressed(BulkMailerContact $contact): bool
    {
        if (method_exists($contact, 'isSuppressed')) {
            return (bool) $contact->isSuppressed();
        }

        if (filled(data_get($contact, 'unsubscribed_at'))) {
            return true;
        }

        if (filled(data_get($contact, 'bounced_at'))) {
            return true;
        }

        $suppressionReason = data_get($contact, 'suppression_reason');

        return filled($suppressionReason);
    }

    protected function isLocalhostHost(string $host): bool
    {
        $host = mb_strtolower(trim($host));

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}