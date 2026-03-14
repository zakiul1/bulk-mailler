<?php

namespace App\Livewire\BulkMailer\Verifications;

use App\Models\BulkMailerContact;
use App\Services\BulkMailerEmailVerificationService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function verify(int $id, BulkMailerEmailVerificationService $service): void
    {
        $contact = BulkMailerContact::findOrFail($id);
        $service->verifyContact($contact);

        session()->flash('success', 'Email verified successfully.');
    }

    public function verifyPage(BulkMailerEmailVerificationService $service): void
    {
        $contacts = $this->rows->getCollection();

        foreach ($contacts as $contact) {
            $service->verifyContact($contact);
        }

        session()->flash('success', 'Visible contacts verified successfully.');
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
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->whereHas('verification', function ($subQuery) {
                    $subQuery->where('status', $this->statusFilter);
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.bulk-mailer.verifications.index')
            ->layout('layouts.app')
            ->title('Verifications');
    }
}