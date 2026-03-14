<?php

namespace App\Livewire\BulkMailer\Operations;

use App\Enums\BulkMailerCampaignRecipientStatus;
use App\Enums\BulkMailerCampaignStatus;
use App\Jobs\ProcessBulkMailerCampaign;
use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerDeliveryEvent;
use App\Services\BulkMailerDeliveryEventService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class QueueMonitor extends Component
{
    use WithPagination;
    use WithFileUploads;

    public bool $showBounceImportModal = false;
    public $bounce_file = null;

    public function retryCampaign(int $campaignId): void
    {
        $campaign = BulkMailerCampaign::findOrFail($campaignId);

        $campaign->update([
            'status' => BulkMailerCampaignStatus::Paused,
            'completed_at' => null,
        ]);

        BulkMailerCampaignRecipient::query()
            ->where('bulk_mailer_campaign_id', $campaignId)
            ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
            ->update([
                'status' => BulkMailerCampaignRecipientStatus::Pending,
                'error_message' => null,
            ]);

        ProcessBulkMailerCampaign::dispatch($campaignId);

        session()->flash('success', 'Campaign retry queued successfully.');
    }

    public function retryRecipient(int $recipientId): void
    {
        $recipient = BulkMailerCampaignRecipient::findOrFail($recipientId);

        $recipient->update([
            'status' => BulkMailerCampaignRecipientStatus::Pending,
            'error_message' => null,
        ]);

        ProcessBulkMailerCampaign::dispatch($recipient->bulk_mailer_campaign_id);

        session()->flash('success', 'Recipient retry queued successfully.');
    }

    public function openBounceImportModal(): void
    {
        $this->showBounceImportModal = true;
        $this->bounce_file = null;
        $this->resetValidation();
    }

    public function importBounces(BulkMailerDeliveryEventService $service): void
    {
        $validated = $this->validate([
            'bounce_file' => ['required', 'file', 'mimes:csv,txt'],
        ], [
            'bounce_file.required' => 'Bounce CSV file is required.',
            'bounce_file.mimes' => 'Only CSV files are allowed.',
        ]);

        $handle = fopen($validated['bounce_file']->getRealPath(), 'r');

        if (! $handle) {
            session()->flash('error', 'Unable to read the bounce file.');
            return;
        }

        $header = fgetcsv($handle);
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $email = strtolower(trim((string) ($row[0] ?? '')));

            if (blank($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $message = trim((string) ($row[1] ?? 'Imported bounce'));

            $service->logBounce($email, $message, [
                'source' => 'csv_import',
            ]);

            $count++;
        }

        fclose($handle);

        $this->showBounceImportModal = false;
        $this->bounce_file = null;

        session()->flash('success', "Bounce import completed. Processed: {$count}.");
    }

    public function closeModals(): void
    {
        $this->showBounceImportModal = false;
        $this->resetValidation();
    }

    public function getCampaignsProperty()
    {
        return BulkMailerCampaign::query()
            ->latest()
            ->take(10)
            ->get();
    }

    public function getFailedRecipientsProperty()
    {
        return BulkMailerCampaignRecipient::query()
            ->with(['campaign', 'contact'])
            ->where('status', BulkMailerCampaignRecipientStatus::Failed->value)
            ->latest()
            ->paginate(10);
    }

    public function getRecentEventsProperty()
    {
        return BulkMailerDeliveryEvent::query()
            ->latest('event_at')
            ->take(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.bulk-mailer.operations.queue-monitor')
            ->layout('layouts.app')
            ->title('Operations');
    }
}