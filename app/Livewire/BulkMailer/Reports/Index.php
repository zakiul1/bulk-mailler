<?php

namespace App\Livewire\BulkMailer\Reports;

use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerDeliveryEvent;
use App\Models\BulkMailerSmtpAccount;
use App\Models\BulkMailerSmtpDailyUsage;
use Livewire\Component;

class Index extends Component
{
    public function getStatsProperty(): array
    {
        return [
            [
                'label' => 'Total Campaigns',
                'value' => BulkMailerCampaign::count(),
            ],
            [
                'label' => 'Recipients Sent',
                'value' => BulkMailerCampaignRecipient::where('status', 'sent')->count(),
            ],
            [
                'label' => 'Recipients Failed',
                'value' => BulkMailerCampaignRecipient::where('status', 'failed')->count(),
            ],
            [
                'label' => 'Open Events',
                'value' => BulkMailerDeliveryEvent::where('event_type', 'open')->count(),
            ],
            [
                'label' => 'Click Events',
                'value' => BulkMailerDeliveryEvent::where('event_type', 'click')->count(),
            ],
            [
                'label' => 'Active SMTP Accounts',
                'value' => BulkMailerSmtpAccount::where('is_active', true)->count(),
            ],
        ];
    }

    public function getRecentCampaignsProperty()
    {
        return BulkMailerCampaign::query()
            ->with('segment')
            ->latest()
            ->take(8)
            ->get();
    }

    public function getSmtpUsageProperty()
    {
        return BulkMailerSmtpDailyUsage::query()
            ->with('smtpAccount')
            ->latest('usage_date')
            ->take(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.bulk-mailer.reports.index')
            ->layout('layouts.app')
            ->title('Reports');
    }
}