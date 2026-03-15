<?php

namespace App\Livewire\BulkMailer\SmtpGroups;

use App\Models\BulkMailerSmtpAccount;
use App\Models\BulkMailerSmtpGroup;
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
    public string $rotation_mode = 'priority';
    public array $selected_smtp_accounts = [];

    public function create(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $group = BulkMailerSmtpGroup::with('smtpAccounts')->findOrFail($id);

        $this->editingId = $group->id;
        $this->name = $group->name;
        $this->description = $group->description ?? '';
        $this->is_active = $group->is_active;
        $this->rotation_mode = $group->rotation_mode ?: 'priority';
        $this->selected_smtp_accounts = $group->smtpAccounts->pluck('id')->map(fn ($id) => (string) $id)->all();

        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'rotation_mode' => ['required', 'in:priority,random,round_robin,least_used'],
            'selected_smtp_accounts' => ['required', 'array', 'min:1'],
            'selected_smtp_accounts.*' => ['integer', 'exists:bulk_mailer_smtp_accounts,id'],
        ], [
            'selected_smtp_accounts.required' => 'Select at least one SMTP account.',
            'selected_smtp_accounts.min' => 'Select at least one SMTP account.',
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?: null,
            'is_active' => (bool) $validated['is_active'],
            'rotation_mode' => $validated['rotation_mode'],
        ];

        if ($this->editingId) {
            $group = BulkMailerSmtpGroup::findOrFail($this->editingId);
            $group->update($payload);
            $message = 'SMTP group updated successfully.';
        } else {
            $group = BulkMailerSmtpGroup::create($payload);
            $message = 'SMTP group created successfully.';
        }

        $group->smtpAccounts()->sync($validated['selected_smtp_accounts']);

        if (! in_array($group->last_used_smtp_account_id, array_map('intval', $validated['selected_smtp_accounts']), true)) {
            $group->update([
                'last_used_smtp_account_id' => null,
            ]);
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
        BulkMailerSmtpGroup::findOrFail($this->deleteId)->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;

        session()->flash('success', 'SMTP group deleted successfully.');
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
        $this->rotation_mode = 'priority';
        $this->selected_smtp_accounts = [];
        $this->resetValidation();
    }

    public function getRowsProperty()
    {
        return BulkMailerSmtpGroup::query()
            ->with(['smtpAccounts', 'lastUsedSmtpAccount'])
            ->latest()
            ->paginate(10);
    }

    public function getSmtpAccountsProperty()
    {
        return BulkMailerSmtpAccount::query()
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.bulk-mailer.smtp-groups.index')
            ->layout('layouts.app')
            ->title('SMTP Groups');
    }
}