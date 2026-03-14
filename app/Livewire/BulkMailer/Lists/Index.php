<?php

namespace App\Livewire\BulkMailer\Lists;

use App\Models\BulkMailerContactList;
use Illuminate\Support\Str;
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

    public ?int $editingId = null;
    public ?int $deleteId = null;

    public string $name = '';
    public string $description = '';
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
        $list = BulkMailerContactList::findOrFail($id);

        $this->editingId = $list->id;
        $this->name = $list->name;
        $this->description = $list->description ?? '';
        $this->is_active = (bool) $list->is_active;

        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bulk_mailer_contact_lists', 'name')->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'List name is required.',
            'name.unique' => 'This list name already exists.',
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'slug' => $this->generateUniqueSlug(trim($validated['name'])),
            'description' => $validated['description'] ?: null,
            'is_active' => (bool) $validated['is_active'],
        ];

        if ($this->editingId) {
            $list = BulkMailerContactList::findOrFail($this->editingId);

            $payload['slug'] = $list->slug;

            if ($list->name !== $payload['name']) {
                $payload['slug'] = $this->generateUniqueSlug($payload['name'], $list->id);
            }

            $list->update($payload);
            $message = 'List updated successfully.';
        } else {
            BulkMailerContactList::create($payload);
            $message = 'List created successfully.';
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
        $list = BulkMailerContactList::findOrFail($this->deleteId);
        $list->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;

        session()->flash('success', 'List deleted successfully.');
    }

    public function toggleStatus(int $id): void
    {
        $list = BulkMailerContactList::findOrFail($id);
        $list->update([
            'is_active' => ! $list->is_active,
        ]);

        session()->flash('success', 'List status updated.');
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
        $this->resetValidation();
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (
            BulkMailerContactList::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function getRowsProperty()
    {
        return BulkMailerContactList::query()
            ->withCount('contacts')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.bulk-mailer.lists.index')
            ->layout('layouts.app')
            ->title('Lists');
    }
}