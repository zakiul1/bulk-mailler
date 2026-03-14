<?php

namespace App\Livewire\BulkMailer\Campaigns;

use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerDeliveryEvent;
use Livewire\Component;

class Show extends Component
{
    public BulkMailerCampaign $campaign;

    public function mount(BulkMailerCampaign $campaign): void
    {
        $this->campaign = $campaign->load([
            'template',
            'lists',
            'creator',
            'segment',
            'recipients.contact',
            'recipients.smtpAccount',
        ]);
    }

    public function getVariantStatsProperty(): array
    {
        $recipientQuery = $this->campaign->recipients();

        return [
            'A' => [
                'sent' => (clone $recipientQuery)->where('subject_variant', 'A')->where('status', 'sent')->count(),
                'failed' => (clone $recipientQuery)->where('subject_variant', 'A')->where('status', 'failed')->count(),
                'opens' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'open')
                    ->whereHas('recipient', fn ($q) => $q->where('subject_variant', 'A'))
                    ->count(),
                'clicks' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'click')
                    ->whereHas('recipient', fn ($q) => $q->where('subject_variant', 'A'))
                    ->count(),
            ],
            'B' => [
                'sent' => (clone $recipientQuery)->where('subject_variant', 'B')->where('status', 'sent')->count(),
                'failed' => (clone $recipientQuery)->where('subject_variant', 'B')->where('status', 'failed')->count(),
                'opens' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'open')
                    ->whereHas('recipient', fn ($q) => $q->where('subject_variant', 'B'))
                    ->count(),
                'clicks' => BulkMailerDeliveryEvent::query()
                    ->where('bulk_mailer_campaign_id', $this->campaign->id)
                    ->where('event_type', 'click')
                    ->whereHas('recipient', fn ($q) => $q->where('subject_variant', 'B'))
                    ->count(),
            ],
        ];
    }

    public function getTotalsProperty(): array
    {
        return [
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

    public function render()
    {
        return view('livewire.bulk-mailer.campaigns.show')
            ->layout('layouts.app')
            ->title('Campaign Details');
    }
}