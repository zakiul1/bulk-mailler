<?php

namespace App\Livewire\BulkMailer\Contacts;

use App\Jobs\ProcessBulkMailerContactImport;
use App\Models\BulkMailerContactImport;
use App\Models\BulkMailerContactList;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?int $bulk_mailer_contact_list_id = null;
    public string $emails_text = '';
    public $import_file = null;

    public ?int $latestImportId = null;
    public string $activeTab = 'text';

    protected $queryString = [
        'activeTab' => ['except' => 'text'],
    ];

    public function mount(): void
    {
        if (!in_array($this->activeTab, ['text', 'file'], true)) {
            $this->activeTab = 'text';
        }
    }

    public function switchTab(string $tab): void
    {
        if (!in_array($tab, ['text', 'file'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetValidation();

        if ($tab === 'text') {
            $this->import_file = null;
        }

        if ($tab === 'file') {
            $this->emails_text = '';
        }
    }

    public function save(): void
    {
        $rules = [
            'bulk_mailer_contact_list_id' => ['required', 'integer', 'exists:bulk_mailer_contact_lists,id'],
        ];

        if ($this->activeTab === 'text') {
            $rules['emails_text'] = ['required', 'string'];
            $rules['import_file'] = ['nullable'];
        } else {
            $rules['emails_text'] = ['nullable'];
            $rules['import_file'] = ['required', 'file', 'mimes:txt,csv'];
        }

        $validated = $this->validate($rules, [
            'bulk_mailer_contact_list_id.required' => 'Category is required.',
            'bulk_mailer_contact_list_id.exists' => 'Selected category is invalid.',
            'emails_text.required' => 'Paste emails is required.',
            'import_file.required' => 'Upload file is required.',
            'import_file.file' => 'Please upload a valid file.',
            'import_file.mimes' => 'Only TXT and CSV files are allowed.',
        ]);

        $sourceType = $this->activeTab === 'file' ? 'file' : 'text';
        $storedFilePath = null;
        $sourceName = null;

        if ($sourceType === 'file') {
            $storedFilePath = $validated['import_file']->store('bulk-mailer/contact-imports');
            $sourceName = $validated['import_file']->getClientOriginalName();
        } else {
            $sourceName = 'pasted-emails.txt';
            $storedFilePath = 'bulk-mailer/contact-imports/' . uniqid('text-import-', true) . '.txt';
            Storage::put($storedFilePath, $validated['emails_text']);
        }

        $import = BulkMailerContactImport::create([
            'bulk_mailer_contact_list_id' => (int) $validated['bulk_mailer_contact_list_id'],
            'source_type' => $sourceType,
            'source_name' => $sourceName,
            'stored_file_path' => $storedFilePath,
            'status' => 'processing',
            'created_by' => auth()->id(),
        ]);

        $this->latestImportId = $import->id;
        $this->emails_text = '';
        $this->import_file = null;
        $this->resetValidation();

        app(ProcessBulkMailerContactImport::class, [
            'importId' => $import->id,
        ])->handle();

        session()->flash('success', 'Import started successfully.');
        $this->dispatch(
            'notify',
            type: 'success',
            message: 'Import started successfully.'
        );
    }

    public function refreshImport(): void
    {
        if (!$this->latestImportId) {
            return;
        }

        $import = BulkMailerContactImport::find($this->latestImportId);

        if (!$import) {
            $this->latestImportId = null;
            return;
        }

        if (in_array($import->status, ['completed', 'failed'], true)) {
            $this->latestImportId = null;
        }
    }

    public function getListsProperty()
    {
        return BulkMailerContactList::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getLatestImportProperty(): ?BulkMailerContactImport
    {
        if ($this->latestImportId) {
            return BulkMailerContactImport::query()
                ->with('category')
                ->find($this->latestImportId);
        }

        return null;
    }

    public function getImportProgressPercentProperty(): int
    {
        return $this->latestImport?->progress_percent ?? 0;
    }

    public function render()
    {
        return view('livewire.bulk-mailer.contacts.create')
            ->layout('layouts.app')
            ->title('Add Contacts');
    }
}