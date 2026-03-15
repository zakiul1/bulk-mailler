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

    public bool $showDeleteModal = false;

    public ?int $deleteId = null;

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

    public function closeModals(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->resetValidation();
    }

    public function getRowsProperty()
    {
        return BulkMailerTemplate::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.bulk-mailer.templates.index')
            ->layout('layouts.app')
            ->title('Templates');
    }
}