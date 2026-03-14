<?php

namespace App\Livewire\BulkMailer\Segments;

use App\Models\BulkMailerContactList;
use App\Models\BulkMailerSegment;
use App\Services\BulkMailerSegmentService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingId = null;
    public ?int $deleteId = null;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public bool $only_active = true;
    public bool $require_verified_valid = true;
    public bool $exclude_unsubscribed = true;
    public bool $exclude_bounced = true;
    public array $list_ids = [];

    public function create(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $segment = BulkMailerSegment::findOrFail($id);
        $rules = $segment->rules ?? [];

        $this->editingId = $segment->id;
        $this->name = $segment->name;
        $this->description = $segment->description ?? '';
        $this->is_active = (bool) $segment->is_active;
        $this->only_active = (bool) ($rules['only_active'] ?? true);
        $this->require_verified_valid = (bool) ($rules['require_verified_valid'] ?? true);
        $this->exclude_unsubscribed = (bool) ($rules['exclude_unsubscribed'] ?? true);
        $this->exclude_bounced = (bool) ($rules['exclude_bounced'] ?? true);
        $this->list_ids = array_map('strval', $rules['list_ids'] ?? []);

        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'only_active' => ['required', 'boolean'],
            'require_verified_valid' => ['required', 'boolean'],
            'exclude_unsubscribed' => ['required', 'boolean'],
            'exclude_bounced' => ['required', 'boolean'],
            'list_ids' => ['nullable', 'array'],
            'list_ids.*' => ['integer', 'exists:bulk_mailer_contact_lists,id'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?: null,
            'is_active' => (bool) $validated['is_active'],
            'rules' => [
                'only_active' => (bool) $validated['only_active'],
                'require_verified_valid' => (bool) $validated['require_verified_valid'],
                'exclude_unsubscribed' => (bool) $validated['exclude_unsubscribed'],
                'exclude_bounced' => (bool) $validated['exclude_bounced'],
                'list_ids' => array_map('intval', $validated['list_ids'] ?? []),
            ],
        ];

        if ($this->editingId) {
            BulkMailerSegment::findOrFail($this->editingId)->update($payload);
            $message = 'Segment updated successfully.';
        } else {
            BulkMailerSegment::create($payload);
            $message = 'Segment created successfully.';
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
        BulkMailerSegment::findOrFail($this->deleteId)->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;

        session()->flash('success', 'Segment deleted successfully.');
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->only_active = true;
        $this->require_verified_valid = true;
        $this->exclude_unsubscribed = true;
        $this->exclude_bounced = true;
        $this->list_ids = [];
        $this->resetValidation();
    }

    public function getRowsProperty()
    {
        return BulkMailerSegment::query()
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

    public function segmentCount(int $id, BulkMailerSegmentService $service): int
    {
        $segment = BulkMailerSegment::find($id);

        return $service->countForSegment($segment);
    }

    public function render()
    {
        return view('livewire.bulk-mailer.segments.index')
            ->layout('layouts.app')
            ->title('Segments');
    }
}