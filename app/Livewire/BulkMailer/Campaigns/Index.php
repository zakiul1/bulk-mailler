<?php

namespace App\Livewire\BulkMailer\Campaigns;

use App\Enums\BulkMailerCampaignStatus;
use App\Jobs\ProcessBulkMailerCampaign;
use App\Mail\BulkMailerCampaignMail;
use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerCampaignRecipient;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerContactList;
use App\Models\BulkMailerSegment;
use App\Models\BulkMailerSmtpGroup;
use App\Models\BulkMailerTemplate;
use App\Services\BulkMailerSegmentService;
use App\Services\BulkMailerSmtpRotationService;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    public bool $showTestModal = false;
    public bool $showRescheduleModal = false;

    public ?int $editingId = null;
    public ?int $deleteId = null;
    public ?int $testId = null;
    public ?int $rescheduleId = null;

    public string $name = '';
    public string $subject = '';
    public string $subject_a = '';
    public string $subject_b = '';
    public bool $ab_testing_enabled = false;
    public string $status = 'draft';
    public string $scheduled_at = '';
    public string $bulk_mailer_template_id = '';
    public string $bulk_mailer_segment_id = '';
    public string $bulk_mailer_smtp_group_id = '';
    public array $selected_lists = [];
    public string $listSearch = '';

    public string $test_email = '';
    public string $reschedule_at = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedBulkMailerTemplateId($value): void
    {
        if ($this->editingId || blank($value) || filled($this->subject)) {
            return;
        }

        $template = BulkMailerTemplate::find($value);

        if ($template) {
            $this->subject = $template->subject;
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $campaign = BulkMailerCampaign::with('lists')->findOrFail($id);

        $this->editingId = $campaign->id;
        $this->name = $campaign->name;
        $this->subject = $campaign->subject ?? '';
        $this->subject_a = $campaign->subject_a ?? '';
        $this->subject_b = $campaign->subject_b ?? '';
        $this->ab_testing_enabled = (bool) $campaign->ab_testing_enabled;
        $this->status = $campaign->status?->value ?? 'draft';
        $this->scheduled_at = $campaign->scheduled_at?->format('Y-m-d\TH:i') ?? '';
        $this->bulk_mailer_template_id = (string) ($campaign->bulk_mailer_template_id ?? '');
        $this->bulk_mailer_segment_id = (string) ($campaign->bulk_mailer_segment_id ?? '');
        $this->bulk_mailer_smtp_group_id = (string) ($campaign->bulk_mailer_smtp_group_id ?? '');
        $this->selected_lists = $campaign->lists->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->listSearch = '';

        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function save(BulkMailerSegmentService $segmentService): void
    {
        $validated = $this->validate($this->rules(), $this->messages());

        $recipientCount = $this->calculateRecipientCount(
            $validated['selected_lists'] ?? [],
            $validated['bulk_mailer_segment_id'] ? (int) $validated['bulk_mailer_segment_id'] : null,
            $segmentService
        );

        $payload = [
            'name' => trim($validated['name']),
            'subject' => $validated['subject'] ? trim($validated['subject']) : null,
            'subject_a' => $validated['subject_a'] ? trim($validated['subject_a']) : null,
            'subject_b' => $validated['subject_b'] ? trim($validated['subject_b']) : null,
            'ab_testing_enabled' => (bool) $validated['ab_testing_enabled'],
            'status' => $validated['status'],
            'bulk_mailer_template_id' => $validated['bulk_mailer_template_id'] ?: null,
            'bulk_mailer_segment_id' => $validated['bulk_mailer_segment_id'] ?: null,
            'bulk_mailer_smtp_group_id' => $validated['bulk_mailer_smtp_group_id'] ?: null,
            'scheduled_at' => $validated['status'] === 'scheduled' ? $validated['scheduled_at'] : null,
            'total_recipients' => $recipientCount,
        ];

        if ($this->editingId) {
            $campaign = BulkMailerCampaign::findOrFail($this->editingId);
            $campaign->update($payload);
            $message = 'Campaign updated successfully.';
        } else {
            $payload['created_by'] = auth()->id();
            $payload['sent_count'] = 0;
            $payload['failed_count'] = 0;

            $campaign = BulkMailerCampaign::create($payload);
            $message = 'Campaign created successfully.';
        }

        $campaign->lists()->sync($validated['selected_lists'] ?? []);

        $this->showFormModal = false;
        $this->resetForm();
        $this->resetPage();

        session()->flash('success', $message);
    }

    public function duplicate(int $id): void
    {
        $campaign = BulkMailerCampaign::with('lists')->findOrFail($id);

        $clone = BulkMailerCampaign::create([
            'name' => $campaign->name . ' Copy',
            'subject' => $campaign->subject,
            'subject_a' => $campaign->subject_a,
            'subject_b' => $campaign->subject_b,
            'ab_testing_enabled' => $campaign->ab_testing_enabled,
            'status' => BulkMailerCampaignStatus::Draft,
            'bulk_mailer_template_id' => $campaign->bulk_mailer_template_id,
            'bulk_mailer_segment_id' => $campaign->bulk_mailer_segment_id,
            'bulk_mailer_smtp_group_id' => $campaign->bulk_mailer_smtp_group_id,
            'scheduled_at' => null,
            'total_recipients' => $campaign->total_recipients,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_by' => auth()->id(),
        ]);

        $clone->lists()->sync($campaign->lists->pluck('id')->all());

        $this->resetPage();

        session()->flash('success', 'Campaign duplicated successfully.');
    }

    public function openRescheduleModal(int $id): void
    {
        $campaign = BulkMailerCampaign::findOrFail($id);

        $this->rescheduleId = $campaign->id;
        $this->reschedule_at = $campaign->scheduled_at?->format('Y-m-d\TH:i') ?? now()->addHour()->format('Y-m-d\TH:i');
        $this->resetValidation();
        $this->showRescheduleModal = true;
    }

    public function reschedule(): void
    {
        $validated = $this->validate([
            'reschedule_at' => ['required', 'date'],
        ]);

        $campaign = BulkMailerCampaign::findOrFail($this->rescheduleId);

        $campaign->update([
            'scheduled_at' => $validated['reschedule_at'],
            'status' => BulkMailerCampaignStatus::Scheduled,
            'completed_at' => null,
            'started_at' => null,
        ]);

        $this->showRescheduleModal = false;
        $this->rescheduleId = null;
        $this->reschedule_at = '';
        $this->resetPage();

        session()->flash('success', 'Campaign rescheduled successfully.');
    }

    public function openTestModal(int $id): void
    {
        $this->testId = $id;
        $this->test_email = auth()->user()->email ?? '';
        $this->resetValidation();
        $this->showTestModal = true;
    }

    public function sendTest(BulkMailerSmtpRotationService $rotationService, MailManager $mailManager): void
    {
        $this->validate([
            'test_email' => ['required', 'email:rfc,dns'],
        ]);

        $campaign = BulkMailerCampaign::with(['template', 'smtpGroup.smtpAccounts'])->findOrFail($this->testId);

        if (! $campaign->template) {
            $this->addError('test_email', 'This campaign has no template.');

            return;
        }

        $smtp = $rotationService->resolveForCampaign($campaign);

        if (! $smtp) {
            $this->addError('test_email', 'No available SMTP account was found for this campaign.');

            return;
        }

        try {
            $this->configureCampaignTestMailer($smtp);
            $mailManager->purge('bulk_mailer_campaign_test');

            $sampleData = [
                '{{name}}' => $this->test_email,
                '{{email}}' => $this->test_email,
                '{{first_name}}' => 'Test',
                '{{last_name}}' => 'Recipient',
            ];

            $subjectTemplate = $campaign->subject ?: $campaign->template->subject;

            if ($campaign->ab_testing_enabled) {
                $subjectTemplate = filled($campaign->subject_a)
                    ? $campaign->subject_a
                    : $subjectTemplate;
            }

            $subjectLine = strtr((string) $subjectTemplate, $sampleData);

            $htmlSource = $campaign->template->html_content;
            $textSource = $campaign->template->text_content;

            $htmlBody = filled($htmlSource)
                ? strtr($htmlSource, $sampleData)
                : '';

            $textBody = filled($textSource)
                ? strtr($textSource, $sampleData)
                : '';

            if (! filled($htmlBody) && filled($textBody)) {
                $htmlBody = nl2br(e($textBody));
            }

            $testContact = new BulkMailerContact([
                'id' => 0,
                'email' => $this->test_email,
                'first_name' => 'Test',
                'last_name' => 'Recipient',
            ]);

            $testContact->exists = false;

            Mail::mailer('bulk_mailer_campaign_test')
                ->to($this->test_email)
                ->send(new BulkMailerCampaignMail(
                    smtp: $smtp,
                    campaign: $campaign,
                    contact: $testContact,
                    subjectLine: $subjectLine,
                    htmlBody: $htmlBody,
                    textBody: filled($textBody) ? $textBody : null,
                ));

            $this->showTestModal = false;
            $this->testId = null;
            $this->test_email = '';

            session()->flash('success', 'Campaign test email sent successfully.');
        } catch (\Throwable $e) {
            $this->addError('test_email', 'Test send failed: ' . $e->getMessage());
        } finally {
            $mailManager->purge('bulk_mailer_campaign_test');
        }
    }

    public function launch(int $id, BulkMailerSmtpRotationService $rotationService): void
    {
        $campaign = BulkMailerCampaign::with(['template', 'lists', 'smtpGroup.smtpAccounts'])->findOrFail($id);

        if (! $campaign->template) {
            session()->flash('error', 'Campaign must have a template before launch.');

            return;
        }

        if (! $campaign->bulk_mailer_smtp_group_id) {
            session()->flash('error', 'Campaign must have an SMTP group before launch.');

            return;
        }

        if (! $rotationService->resolveForCampaign($campaign)) {
            session()->flash('error', 'No available SMTP account was found in the selected group.');

            return;
        }

        $isScheduled = $campaign->status?->value === 'scheduled' && $campaign->scheduled_at;

        $campaign->update([
            'status' => $isScheduled ? BulkMailerCampaignStatus::Scheduled : BulkMailerCampaignStatus::Processing,
            'started_at' => $isScheduled ? null : now(),
            'completed_at' => null,
        ]);

        if ($isScheduled) {
            $this->resetPage();
            session()->flash('success', 'Campaign is scheduled and will be processed automatically.');

            return;
        }

        ProcessBulkMailerCampaign::dispatch($campaign->id);

        $this->resetPage();
        session()->flash('success', 'Campaign launched successfully.');
    }

    public function launchNow(int $id): void
    {
        $campaign = BulkMailerCampaign::findOrFail($id);

        $campaign->update([
            'status' => BulkMailerCampaignStatus::Processing,
            'scheduled_at' => null,
            'started_at' => now(),
            'completed_at' => null,
        ]);

        ProcessBulkMailerCampaign::dispatch($campaign->id);

        $this->resetPage();
        session()->flash('success', 'Scheduled campaign launched immediately.');
    }

    public function pause(int $id): void
    {
        BulkMailerCampaign::findOrFail($id)->update([
            'status' => BulkMailerCampaignStatus::Paused,
        ]);

        $this->resetPage();
        session()->flash('success', 'Campaign paused successfully.');
    }

    public function resume(int $id): void
    {
        $campaign = BulkMailerCampaign::findOrFail($id);

        BulkMailerCampaignRecipient::query()
            ->where('bulk_mailer_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->update(['error_message' => null]);

        $campaign->update([
            'status' => BulkMailerCampaignStatus::Processing,
            'completed_at' => null,
        ]);

        ProcessBulkMailerCampaign::dispatch($campaign->id);

        $this->resetPage();
        session()->flash('success', 'Campaign resumed successfully.');
    }

    public function cancelCampaign(int $id): void
    {
        BulkMailerCampaign::findOrFail($id)->update([
            'status' => BulkMailerCampaignStatus::Cancelled,
            'completed_at' => now(),
        ]);

        $this->resetPage();
        session()->flash('success', 'Campaign cancelled successfully.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        BulkMailerCampaign::findOrFail($this->deleteId)->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->resetPage();

        session()->flash('success', 'Campaign deleted successfully.');
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->showTestModal = false;
        $this->showRescheduleModal = false;
        $this->deleteId = null;
        $this->testId = null;
        $this->rescheduleId = null;
        $this->listSearch = '';
        $this->resetValidation();
    }

    public function refreshRows(): void
    {
        // Used by wire:poll in the Blade view to refresh campaign progress.
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->subject = '';
        $this->subject_a = '';
        $this->subject_b = '';
        $this->ab_testing_enabled = false;
        $this->status = 'draft';
        $this->scheduled_at = '';
        $this->bulk_mailer_template_id = '';
        $this->bulk_mailer_segment_id = '';
        $this->bulk_mailer_smtp_group_id = '';
        $this->selected_lists = [];
        $this->listSearch = '';
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required_unless:ab_testing_enabled,1', 'nullable', 'string', 'max:255'],
            'subject_a' => ['nullable', 'string', 'max:255'],
            'subject_b' => ['nullable', 'string', 'max:255'],
            'ab_testing_enabled' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'paused', 'cancelled'])],
            'scheduled_at' => [Rule::requiredIf($this->status === 'scheduled'), 'nullable', 'date'],
            'bulk_mailer_template_id' => ['nullable', 'integer', 'exists:bulk_mailer_templates,id'],
            'bulk_mailer_segment_id' => ['nullable', 'integer', 'exists:bulk_mailer_segments,id'],
            'bulk_mailer_smtp_group_id' => ['required', 'integer', 'exists:bulk_mailer_smtp_groups,id'],
            'selected_lists' => ['nullable', 'array'],
            'selected_lists.*' => ['integer', 'exists:bulk_mailer_contact_lists,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Campaign name is required.',
            'subject.required_unless' => 'Campaign subject is required when A/B testing is disabled.',
            'status.required' => 'Campaign status is required.',
            'scheduled_at.required' => 'Schedule time is required for scheduled campaigns.',
            'bulk_mailer_smtp_group_id.required' => 'SMTP group is required.',
        ];
    }

    protected function calculateRecipientCount(array $listIds, ?int $segmentId, BulkMailerSegmentService $segmentService): int
    {
        $query = BulkMailerContact::query();

        if (! empty($listIds)) {
            $query->whereIn('bulk_mailer_contact_list_id', array_map('intval', $listIds));
        }

        if ($segmentId) {
            $segment = BulkMailerSegment::find($segmentId);

            if ($segment) {
                $segmentService->applySegment($query, $segment);
            }
        }

        return $query
            ->whereNotNull('email')
            ->distinct('bulk_mailer_contacts.id')
            ->count('bulk_mailer_contacts.id');
    }

    protected function configureCampaignTestMailer($smtp): void
    {
        Config::set('mail.mailers.bulk_mailer_campaign_test', [
            'transport' => 'smtp',
            'host' => $smtp->host,
            'port' => $smtp->port,
            'encryption' => blank($smtp->encryption) ? null : $smtp->encryption,
            'username' => $smtp->username,
            'password' => $smtp->decrypted_password,
            'timeout' => 90,
            'local_domain' => $this->resolveLocalDomain($smtp),
        ]);
    }

    protected function resolveLocalDomain($smtp = null): string
    {
        $configuredEhloDomain = (string) config('mail.ehlo_domain');

        if (filled($configuredEhloDomain) && ! $this->isLocalhostHost($configuredEhloDomain)) {
            return $configuredEhloDomain;
        }

        $appHost = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        if (filled($appHost) && ! $this->isLocalhostHost($appHost)) {
            return $appHost;
        }

        if ($smtp && filled($smtp->host) && ! $this->isLocalhostHost((string) $smtp->host)) {
            return (string) $smtp->host;
        }

        return 'mail.example.com';
    }

    protected function isLocalhostHost(string $host): bool
    {
        $host = mb_strtolower(trim($host));

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    public function getEstimatedRecipientsProperty(): int
    {
        return $this->calculateRecipientCount(
            array_map('intval', $this->selected_lists),
            filled($this->bulk_mailer_segment_id) ? (int) $this->bulk_mailer_segment_id : null,
            app(BulkMailerSegmentService::class)
        );
    }

    public function getShouldPollProperty(): bool
    {
        return BulkMailerCampaign::query()
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
            ->whereIn('status', [
                BulkMailerCampaignStatus::Processing->value,
                BulkMailerCampaignStatus::Scheduled->value,
            ])
            ->exists();
    }

    public function getPollIntervalProperty(): int
    {
        return 2000;
    }

    public function getRowsProperty()
    {
        return BulkMailerCampaign::query()
            ->with(['template', 'lists', 'segment', 'smtpGroup'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('subject', 'like', '%' . $this->search . '%')
                        ->orWhere('subject_a', 'like', '%' . $this->search . '%')
                        ->orWhere('subject_b', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);
    }

    public function getTemplatesProperty()
    {
        return BulkMailerTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getListsProperty()
    {
        return BulkMailerContactList::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getFilteredListsProperty()
    {
        return BulkMailerContactList::query()
            ->where('is_active', true)
            ->when($this->listSearch !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->listSearch . '%');
            })
            ->orderBy('name')
            ->get();
    }

    public function getSegmentsProperty()
    {
        return BulkMailerSegment::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getSmtpGroupsProperty()
    {
        return BulkMailerSmtpGroup::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.bulk-mailer.campaigns.index', [
            'shouldPoll' => $this->shouldPoll,
            'pollInterval' => $this->pollInterval,
        ])
            ->layout('layouts.app')
            ->title('Campaigns');
    }
}