<?php

namespace App\Livewire\BulkMailer\Contacts;

use App\Enums\BulkMailerContactStatus;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerContactList;
use App\Services\BulkMailerContactCsvImportService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $listFilter = 'all';

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    public bool $showImportModal = false;

    public ?int $editingId = null;
    public ?int $deleteId = null;

    public string $email = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $status = 'active';
    public string $notes = '';
    public array $selected_lists = [];

    public $import_file = null;
    public array $import_list_ids = [];
    public array $lastImportSummary = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'listFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingListFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->import_file = null;
        $this->import_list_ids = [];
        $this->lastImportSummary = [];
        $this->resetValidation();
    }

    public function importCsv(BulkMailerContactCsvImportService $service): void
    {
        $validated = $this->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt'],
            'import_list_ids' => ['nullable', 'array'],
            'import_list_ids.*' => ['integer', 'exists:bulk_mailer_contact_lists,id'],
        ], [
            'import_file.required' => 'CSV file is required.',
            'import_file.file' => 'Upload a valid file.',
            'import_file.mimes' => 'Only CSV files are allowed.',
        ]);

        $summary = $service->import(
            $validated['import_file'],
            $validated['import_list_ids'] ?? []
        );

        $this->lastImportSummary = $summary;
        $this->showImportModal = false;
        $this->import_file = null;
        $this->import_list_ids = [];

        session()->flash(
            'success',
            "Import completed. Total: {$summary['total']}, Created: {$summary['created']}, Updated: {$summary['updated']}, Invalid: {$summary['invalid']}."
        );
    }

    public function edit(int $id): void
    {
        $contact = BulkMailerContact::with('lists')->findOrFail($id);

        $this->editingId = $contact->id;
        $this->email = $contact->email;
        $this->first_name = $contact->first_name ?? '';
        $this->last_name = $contact->last_name ?? '';
        $this->status = $contact->status?->value ?? 'active';
        $this->notes = $contact->notes ?? '';
        $this->selected_lists = $contact->lists->pluck('id')->map(fn ($id) => (string) $id)->all();

        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), $this->messages());

        $status = $validated['status'];

        $payload = [
            'email' => strtolower(trim($validated['email'])),
            'first_name' => $validated['first_name'] ?: null,
            'last_name' => $validated['last_name'] ?: null,
            'status' => $status,
            'notes' => $validated['notes'] ?: null,
            'unsubscribed_at' => $status === 'unsubscribed' ? now() : null,
            'bounced_at' => in_array($status, ['bounced', 'suppressed'], true) ? now() : null,
        ];

        if ($this->editingId) {
            $contact = BulkMailerContact::findOrFail($this->editingId);
            $contact->update($payload);
            $message = 'Contact updated successfully.';
        } else {
            $contact = BulkMailerContact::create($payload);
            $message = 'Contact created successfully.';
        }

        $contact->lists()->sync($validated['selected_lists'] ?? []);

        $this->showFormModal = false;
        $this->resetForm();

        session()->flash('success', $message);
    }

    public function markUnsubscribed(int $id): void
    {
        $contact = BulkMailerContact::findOrFail($id);

        $contact->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        session()->flash('success', 'Contact marked as unsubscribed.');
    }

    public function markBounced(int $id): void
    {
        $contact = BulkMailerContact::findOrFail($id);

        $contact->update([
            'status' => 'bounced',
            'bounced_at' => now(),
        ]);

        session()->flash('success', 'Contact marked as bounced.');
    }

    public function reactivate(int $id): void
    {
        $contact = BulkMailerContact::findOrFail($id);

        $contact->update([
            'status' => 'active',
            'unsubscribed_at' => null,
            'bounced_at' => null,
        ]);

        session()->flash('success', 'Contact reactivated.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $contact = BulkMailerContact::findOrFail($this->deleteId);
        $contact->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;

        session()->flash('success', 'Contact deleted successfully.');
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->showImportModal = false;
        $this->deleteId = null;
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->email = '';
        $this->first_name = '';
        $this->last_name = '';
        $this->status = 'active';
        $this->notes = '';
        $this->selected_lists = [];
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('bulk_mailer_contacts', 'email')->ignore($this->editingId),
            ],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_column(BulkMailerContactStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string'],
            'selected_lists' => ['nullable', 'array'],
            'selected_lists.*' => ['integer', 'exists:bulk_mailer_contact_lists,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email already exists.',
            'status.required' => 'Status is required.',
        ];
    }

    public function getRowsProperty()
    {
        return BulkMailerContact::query()
            ->with(['lists', 'verification'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('email', 'like', '%'.$this->search.'%')
                        ->orWhere('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->listFilter !== 'all', function ($query) {
                $query->whereHas('lists', fn ($subQuery) => $subQuery->where('bulk_mailer_contact_lists.id', $this->listFilter));
            })
            ->latest()
            ->paginate(10);
    }

    public function getListsProperty()
    {
        return BulkMailerContactList::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.bulk-mailer.contacts.index')
            ->layout('layouts.app')
            ->title('Contacts');
    }
}