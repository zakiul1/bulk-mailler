<?php

namespace App\Livewire\BulkMailer\CampaignCalendar;

use App\Models\BulkMailerCampaign;
use Livewire\Component;

class Index extends Component
{
    public function getUpcomingCampaignsProperty()
    {
        return BulkMailerCampaign::query()
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.bulk-mailer.campaign-calendar.index')
            ->layout('layouts.app')
            ->title('Campaign Calendar');
    }
}