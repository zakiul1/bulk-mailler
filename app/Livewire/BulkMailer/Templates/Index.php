<?php

namespace App\Livewire\BulkMailer\Templates;

use App\Models\BulkMailerTemplate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    public bool $showPreviewModal = false;

    public ?int $editingId = null;
    public ?int $deleteId = null;
    public ?int $previewId = null;

    public string $name = '';
    public string $subject = '';
    public string $html_content = '';
    public string $text_content = '';
    public bool $is_active = true;

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

    public function create(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $template = BulkMailerTemplate::findOrFail($id);

        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->subject = $template->subject;
        $this->html_content = $template->html_content ?? '';
        $this->text_content = $template->text_content ?? '';
        $this->is_active = (bool) $template->is_active;

        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'text_content' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Template name is required.',
            'subject.required' => 'Email subject is required.',
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'subject' => trim($validated['subject']),
            'html_content' => $validated['html_content'] ?: null,
            'text_content' => $validated['text_content'] ?: null,
            'is_active' => (bool) $validated['is_active'],
        ];

        if ($this->editingId) {
            $template = BulkMailerTemplate::findOrFail($this->editingId);
            $template->update($payload);
            $message = 'Template updated successfully.';
        } else {
            BulkMailerTemplate::create($payload);
            $message = 'Template created successfully.';
        }

        $this->showFormModal = false;
        $this->resetForm();

        session()->flash('success', $message);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $template = BulkMailerTemplate::findOrFail($this->deleteId);
        $template->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;

        session()->flash('success', 'Template deleted successfully.');
    }

    public function toggleStatus(int $id): void
    {
        $template = BulkMailerTemplate::findOrFail($id);
        $template->update([
            'is_active' => ! $template->is_active,
        ]);

        session()->flash('success', 'Template status updated.');
    }

    public function openPreview(int $id): void
    {
        $this->previewId = $id;
        $this->showPreviewModal = true;
    }

    public function getPreviewTemplateProperty(): ?BulkMailerTemplate
    {
        if (! $this->previewId) {
            return null;
        }

        return BulkMailerTemplate::find($this->previewId);
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->showPreviewModal = false;
        $this->deleteId = null;
        $this->previewId = null;
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->subject = '';
        $this->html_content = '';
        $this->text_content = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function getRowsProperty()
    {
        return BulkMailerTemplate::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('subject', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(10);
    }

    public function placeholderExamples(): array
    {
        return [
            '{{name}}' => 'Full recipient name',
            '{{email}}' => 'Recipient email',
            '{{first_name}}' => 'Recipient first name',
            '{{last_name}}' => 'Recipient last name',
        ];
    }

    public function render()
    {
        return view('livewire.bulk-mailer.templates.index')
            ->layout('layouts.app')
            ->title('Templates');
    }
}