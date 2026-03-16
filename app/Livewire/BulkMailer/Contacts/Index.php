<?php

namespace App\Livewire\BulkMailer\Contacts;

use App\Jobs\ProcessBulkMailerContactDelete;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerContactDeleteJob;
use App\Models\BulkMailerContactList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $listFilter = 'all';

    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;

    public ?int $deleteId = null;

    public array $selected = [];
    public bool $selectAllMatching = false;

    public string $copiedContactsText = '';
    public ?int $latestDeleteJobId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'listFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        $this->latestDeleteJobId = null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingListFilter(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAllMatching(bool $value): void
    {
        if (! $value) {
            $this->selected = [];
            return;
        }

        $this->selected = $this->filteredQuery()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $contact = BulkMailerContact::findOrFail($this->deleteId);

        $deleteJob = BulkMailerContactDeleteJob::create([
            'bulk_mailer_contact_list_id' => $contact->bulk_mailer_contact_list_id,
            'status' => 'queued',
            'selection_type' => 'selected',
            'filters' => [
                'ids' => [$contact->id],
                'search' => $this->search,
                'list_id' => $this->listFilter,
            ],
            'created_by' => auth()->id(),
        ]);

        $this->latestDeleteJobId = $deleteJob->id;

        ProcessBulkMailerContactDelete::dispatch($deleteJob->id);

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->resetSelection();
        $this->resetPage();

        session()->flash('success', 'Delete started.');
        $this->dispatch('notify', type: 'success', message: 'Delete started.');
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            session()->flash('error', 'Please select at least one contact.');
            $this->dispatch('notify', type: 'error', message: 'Please select at least one contact.');
            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        $ids = collect($this->selected)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            $this->showBulkDeleteModal = false;
            session()->flash('error', 'No valid contacts selected.');
            $this->dispatch('notify', type: 'error', message: 'No valid contacts selected.');
            return;
        }

        $deleteJob = BulkMailerContactDeleteJob::create([
            'bulk_mailer_contact_list_id' => $this->listFilter !== 'all' ? (int) $this->listFilter : null,
            'status' => 'queued',
            'selection_type' => 'selected',
            'filters' => [
                'ids' => $ids,
                'search' => $this->search,
                'list_id' => $this->listFilter,
            ],
            'created_by' => auth()->id(),
        ]);

        $this->latestDeleteJobId = $deleteJob->id;

        ProcessBulkMailerContactDelete::dispatch($deleteJob->id);

        $this->showBulkDeleteModal = false;
        $this->resetSelection();
        $this->resetPage();

        session()->flash('success', 'Bulk delete started.');
        $this->dispatch('notify', type: 'success', message: 'Bulk delete started.');
    }

    public function copySelected(): void
    {
        $ids = collect($this->selected)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            session()->flash('error', 'Please select at least one contact to copy.');
            $this->dispatch('notify', type: 'error', message: 'Please select at least one contact to copy.');
            return;
        }

        $emails = BulkMailerContact::query()
            ->whereIn('id', $ids)
            ->orderBy('email')
            ->pluck('email')
            ->all();

        $this->copiedContactsText = implode(PHP_EOL, $emails);

        $this->dispatch('copy-contacts', text: $this->copiedContactsText);
        $this->dispatch('notify', type: 'success', message: count($emails) . ' contact(s) copied.');
    }

    public function pollDeleteJob(): void
    {
        // Keep section visible during polling.
        // Do not clear completed/failed job automatically.
    }

    public function clearDeleteJob(): void
    {
        if (! $this->latestDeleteJobId) {
            return;
        }

        $job = BulkMailerContactDeleteJob::find($this->latestDeleteJobId);

        if (! $job) {
            $this->latestDeleteJobId = null;
            return;
        }

        if (in_array($job->status, ['completed', 'failed'], true)) {
            $this->latestDeleteJobId = null;
        }
    }

    public function closeModals(): void
    {
        $this->showDeleteModal = false;
        $this->showBulkDeleteModal = false;
        $this->deleteId = null;
    }

    protected function resetSelection(): void
    {
        $this->selected = [];
        $this->selectAllMatching = false;
    }

    protected function filteredQuery(): Builder
    {
        return BulkMailerContact::query()
            ->when($this->search !== '', function (Builder $query) {
                $query->where('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->listFilter !== 'all', function (Builder $query) {
                $query->where('bulk_mailer_contact_list_id', (int) $this->listFilter);
            });
    }

    public function getRowsProperty()
    {
        return $this->filteredQuery()
            ->with('category')
            ->latest()
            ->paginate(20);
    }

    public function getListsProperty()
    {
        return BulkMailerContactList::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getSelectedCountProperty(): int
    {
        return count($this->selected);
    }

    public function getLatestDeleteJobProperty(): ?BulkMailerContactDeleteJob
    {
        if ($this->latestDeleteJobId) {
            return BulkMailerContactDeleteJob::query()
                ->with('category')
                ->find($this->latestDeleteJobId);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.bulk-mailer.contacts.index')
            ->layout('layouts.app')
            ->title('Contacts');
    }
}