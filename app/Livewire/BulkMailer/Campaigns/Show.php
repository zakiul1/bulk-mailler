<?php

namespace App\Livewire\BulkMailer\Campaigns;

use App\Enums\BulkMailerCampaignRecipientStatus;
use App\Enums\BulkMailerCampaignStatus;
use App\Jobs\ProcessBulkMailerCampaign;
use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerDeliveryEvent;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public BulkMailerCampaign $campaign;

    public int $perPage = 50;

    public function mount(BulkMailerCampaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->refreshCampaign();
    }

    public function retryFailed(): void
    {
        $failedCount = $this->campaign->recipients()
            ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
            ->count();

        if ($failedCount === 0) {
            session()->flash('error', 'No failed emails found to retry.');

            return;
        }

        $this->campaign->recipients()
            ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
            ->update([
                'status' => BulkMailerCampaignRecipientStatus::Pending->value,
                'error_message' => null,
                'sent_at' => null,
            ]);

        $sentCount = $this->campaign->recipients()
            ->where('status', BulkMailerCampaignRecipientStatus::Sent->value)
            ->count();

        $remainingFailedCount = $this->campaign->recipients()
            ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
            ->count();

        $this->campaign->update([
            'status' => BulkMailerCampaignStatus::Processing,
            'sent_count' => $sentCount,
            'failed_count' => $remainingFailedCount,
            'completed_at' => null,
            'started_at' => $this->campaign->started_at ?? now(),
        ]);

        ProcessBulkMailerCampaign::dispatch($this->campaign->id);

        $this->resetPage();
        $this->refreshCampaign();

        session()->flash('success', "Retry started for {$failedCount} failed emails.");
    }

    public function getRecipientLogsProperty()
    {
        return BulkMailerCampaignRecipient::query()
            ->with([
                'contact.category',
                'smtpAccount',
            ])
            ->where('bulk_mailer_campaign_id', $this->campaign->id)
            ->latest('id')
            ->paginate($this->perPage);
    }

    public function getVariantStatsProperty(): array
    {
        $recipientQuery = $this->campaign->recipients();

        return [
            'A' => [
                'sent' => (clone $recipientQuery)
                    ->where('subject_variant', 'A')
                    ->where('status', BulkMailerCampaignRecipientStatus::Sent->value)
                    ->count(),
                'failed' => (clone $recipientQuery)
                    ->where('subject_variant', 'A')
                    ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
                    ->count(),
                'opens' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'open')
                    ->whereHas('recipient', fn($q) => $q->where('subject_variant', 'A'))
                    ->count(),
                'clicks' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'click')
                    ->whereHas('recipient', fn($q) => $q->where('subject_variant', 'A'))
                    ->count(),
                'delivered' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'delivered')
                    ->whereHas('recipient', fn($q) => $q->where('subject_variant', 'A'))
                    ->count(),
                'bounces' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'bounce')
                    ->whereHas('recipient', fn($q) => $q->where('subject_variant', 'A'))
                    ->count(),
            ],
            'B' => [
                'sent' => (clone $recipientQuery)
                    ->where('subject_variant', 'B')
                    ->where('status', BulkMailerCampaignRecipientStatus::Sent->value)
                    ->count(),
                'failed' => (clone $recipientQuery)
                    ->where('subject_variant', 'B')
                    ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
                    ->count(),
                'opens' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'open')
                    ->whereHas('recipient', fn($q) => $q->where('subject_variant', 'B'))
                    ->count(),
                'clicks' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'click')
                    ->whereHas('recipient', fn($q) => $q->where('subject_variant', 'B'))
                    ->count(),
                'delivered' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'delivered')
                    ->whereHas('recipient', fn($q) => $q->where('subject_variant', 'B'))
                    ->count(),
                'bounces' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'bounce')
                    ->whereHas('recipient', fn($q) => $q->where('subject_variant', 'B'))
                    ->count(),
            ],
        ];
    }

    public function getTotalsProperty(): array
    {
        return [
            'accepted' => $this->campaign->recipients()
                ->where('status', BulkMailerCampaignRecipientStatus::Sent->value)
                ->count(),
            'failed' => $this->campaign->recipients()
                ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
                ->count(),
            'opens' => BulkMailerDeliveryEvent::query()
                ->where('bulk_mailer_campaign_id', $this->campaign->id)
                ->where('event_type', 'open')
                ->count(),
            'clicks' => BulkMailerDeliveryEvent::query()
                ->where('bulk_mailer_campaign_id', $this->campaign->id)
                ->where('event_type', 'click')
                ->count(),
            'delivered' => BulkMailerDeliveryEvent::query()
                ->where('bulk_mailer_campaign_id', $this->campaign->id)
                ->where('event_type', 'delivered')
                ->count(),
            'bounces' => BulkMailerDeliveryEvent::query()
                ->where('bulk_mailer_campaign_id', $this->campaign->id)
                ->where('event_type', 'bounce')
                ->count(),
        ];
    }

    protected function refreshCampaign(): void
    {
        $this->campaign->refresh()->load([
            'template',
            'lists',
            'creator',
            'segment',
            'smtpGroup',
        ]);
    }

    public function render()
    {
        return view('livewire.bulk-mailer.campaigns.show', [
            'recipientLogs' => $this->recipientLogs,
        ])
            ->layout('layouts.app')
            ->title('Campaign Details');
    }
}