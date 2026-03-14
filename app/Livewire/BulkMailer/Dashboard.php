<?php

namespace App\Livewire\BulkMailer;

use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerContactList;
use App\Models\BulkMailerSmtpAccount;
use App\Models\BulkMailerTemplate;
use Livewire\Component;

class Dashboard extends Component
{
    public function getStatsProperty(): array
    {
        return [
            [
                'label' => 'SMTP Accounts',
                'value' => BulkMailerSmtpAccount::count(),
                'description' => 'Configured sending servers',
            ],
            [
                'label' => 'Lists',
                'value' => BulkMailerContactList::count(),
                'description' => 'Recipient groups',
            ],
            [
                'label' => 'Templates',
                'value' => BulkMailerTemplate::count(),
                'description' => 'Reusable email templates',
            ],
            [
                'label' => 'Campaigns',
                'value' => BulkMailerCampaign::count(),
                'description' => 'Created campaigns',
            ],
        ];
    }

    public function getRecentCampaignsProperty()
    {
        return BulkMailerCampaign::query()
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.bulk-mailer.dashboard')
            ->layout('layouts.app')
            ->title('Bulk Mailer');
    }
}