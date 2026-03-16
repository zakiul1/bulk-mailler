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

        if (! $campaign || ! $campaign->template || ! $campaign->smtpGroup) {
            return;
        }

        $campaign->update([
            'status' => BulkMailerCampaignStatus::Processing,
        ]);

        $this->syncRecipients($campaign, $segmentService);

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
            $smtp = $rotationService->resolveForCampaign($campaign->fresh('smtpGroup.smtpAccounts'));

            if (! $smtp) {
                $campaign->update([
                    'status' => BulkMailerCampaignStatus::Paused,
                ]);

                return;
            }

            try {
                if (! $recipient->contact) {
                    $recipient->update([
                        'bulk_mailer_smtp_account_id' => $smtp->id,
                        'status' => BulkMailerCampaignRecipientStatus::Failed,
                        'error_message' => 'Recipient contact record not found.',
                    ]);

                    $smtpHealthService->markFailure($smtp, 'Recipient contact record not found.');
                    $eventService->logFailed($recipient, 'Recipient contact record not found.');

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

                if (! filled($renderedHtml) && filled($renderedText)) {
                    $renderedHtml = nl2br(e($renderedText));
                }

                $this->configureCampaignMailer($smtp);

                $mailManager->purge('bulk_mailer_campaign');

                Mail::mailer('bulk_mailer_campaign')
                    ->to($recipient->email)
                    ->send(new BulkMailerCampaignMail(
                        smtp: $smtp,
                        campaign: $campaign,
                        contact: $recipient->contact,
                        subjectLine: $renderedSubject,
                        htmlBody: $renderedHtml,
                        textBody: filled($renderedText) ? $renderedText : null,
                    ));

                $recipient->update([
                    'bulk_mailer_smtp_account_id' => $smtp->id,
                    'status' => BulkMailerCampaignRecipientStatus::Sent,
                    'error_message' => null,
                    'sent_at' => now(),
                ]);

                $this->incrementDailyUsage($smtp->id);
                $smtpHealthService->markSuccess($smtp);
                $eventService->logSent($recipient);
            } catch (\Throwable $e) {
                $recipient->update([
                    'bulk_mailer_smtp_account_id' => $smtp->id,
                    'status' => BulkMailerCampaignRecipientStatus::Failed,
                    'error_message' => mb_substr($e->getMessage(), 0, 1000),
                ]);

                $smtpHealthService->markFailure($smtp, mb_substr($e->getMessage(), 0, 1000));
                $eventService->logFailed($recipient, mb_substr($e->getMessage(), 0, 1000));
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

        if (! empty($listIds)) {
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
            ->filter(fn (BulkMailerContact $contact) => filled($contact->email))
            ->unique('id')
            ->values();

        foreach ($contacts as $index => $contact) {
            BulkMailerCampaignRecipient::updateOrCreate(
                [
                    'bulk_mailer_campaign_id' => $campaign->id,
                    'bulk_mailer_contact_id' => $contact->id,
                ],
                [
                    'email' => $contact->email,
                    'subject_variant' => $campaign->ab_testing_enabled
                        ? ($index % 2 === 0 ? 'A' : 'B')
                        : null,
                    'status' => BulkMailerCampaignRecipientStatus::Pending->value,
                ]
            );
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
            'host' => $smtp->host,
            'port' => $smtp->port,
            'encryption' => blank($smtp->encryption) ? null : $smtp->encryption,
            'username' => $smtp->username,
            'password' => $smtp->decrypted_password,
            'timeout' => 90,
            'local_domain' => $this->resolveLocalDomain(),
        ]);
    }

    protected function resolveLocalDomain(): string
    {
        $ehloDomain = env('MAIL_EHLO_DOMAIN');

        if (filled($ehloDomain)) {
            return $ehloDomain;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (filled($appHost)) {
            return $appHost;
        }

        return 'localhost';
    }

    protected function refreshCampaignCountsAndStatus(BulkMailerCampaign $campaign): void
    {
        $hasPending = BulkMailerCampaignRecipient::query()
            ->where('bulk_mailer_campaign_id', $campaign->id)
            ->where('status', BulkMailerCampaignRecipientStatus::Pending->value)
            ->exists();

        $campaign->update([
            'sent_count' => BulkMailerCampaignRecipient::query()
                ->where('bulk_mailer_campaign_id', $campaign->id)
                ->where('status', BulkMailerCampaignRecipientStatus::Sent->value)
                ->count(),
            'failed_count' => BulkMailerCampaignRecipient::query()
                ->where('bulk_mailer_campaign_id', $campaign->id)
                ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
                ->count(),
            'status' => $hasPending ? BulkMailerCampaignStatus::Paused : BulkMailerCampaignStatus::Completed,
            'completed_at' => $hasPending ? null : now(),
        ]);
    }
}