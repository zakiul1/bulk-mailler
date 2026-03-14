<?php

namespace App\Livewire\BulkMailer\SmtpAnalytics;

use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerDeliveryEvent;
use App\Models\BulkMailerSmtpAccount;
use App\Models\BulkMailerSmtpDailyUsage;
use Livewire\Component;

class Index extends Component
{
    public function getRowsProperty()
    {
        return BulkMailerSmtpAccount::query()
            ->get()
            ->map(function (BulkMailerSmtpAccount $smtp) {
                $sent = BulkMailerCampaignRecipient::query()
                    ->where('bulk_mailer_smtp_account_id', $smtp->id)
                    ->where('status', 'sent')
                    ->count();

                $failed = BulkMailerCampaignRecipient::query()
                    ->where('bulk_mailer_smtp_account_id', $smtp->id)
                    ->where('status', 'failed')
                    ->count();

                $bounces = BulkMailerDeliveryEvent::query()
                    ->where('event_type', 'bounce')
                    ->whereHas('recipient', function ($query) use ($smtp) {
                        $query->where('bulk_mailer_smtp_account_id', $smtp->id);
                    })
                    ->count();

                $todayUsage = BulkMailerSmtpDailyUsage::query()
                    ->where('bulk_mailer_smtp_account_id', $smtp->id)
                    ->whereDate('usage_date', now()->toDateString())
                    ->value('emails_sent') ?? 0;

                return [
                    'smtp' => $smtp,
                    'sent' => $sent,
                    'failed' => $failed,
                    'bounces' => $bounces,
                    'today_usage' => $todayUsage,
                ];
            });
    }

    public function render()
    {
        return view('livewire.bulk-mailer.smtp-analytics.index')
            ->layout('layouts.app')
            ->title('SMTP Analytics');
    }
}