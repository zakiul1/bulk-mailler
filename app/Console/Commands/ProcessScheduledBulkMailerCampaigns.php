<?php

namespace App\Console\Commands;

use App\Enums\BulkMailerCampaignStatus;
use App\Jobs\ProcessBulkMailerCampaign;
use App\Models\BulkMailerCampaign;
use Illuminate\Console\Command;

class ProcessScheduledBulkMailerCampaigns extends Command
{
    protected $signature = 'bulk-mailer:process-scheduled-campaigns';

    protected $description = 'Dispatch due scheduled bulk mailer campaigns';

    public function handle(): int
    {
        $campaigns = BulkMailerCampaign::query()
            ->where('status', BulkMailerCampaignStatus::Scheduled->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($campaigns as $campaign) {
            $campaign->update([
                'status' => BulkMailerCampaignStatus::Processing,
                'started_at' => $campaign->started_at ?: now(),
                'completed_at' => null,
            ]);

            ProcessBulkMailerCampaign::dispatch($campaign->id);
            $count++;
        }

        $this->info("Dispatched {$count} scheduled campaign(s).");

        return self::SUCCESS;
    }
}