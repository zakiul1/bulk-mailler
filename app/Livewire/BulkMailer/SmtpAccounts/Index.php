<?php

namespace App\Livewire\BulkMailer\SmtpAccounts;

use App\Enums\BulkMailerSmtpHealthStatus;
use App\Enums\BulkMailerTagType;
use App\Models\BulkMailerSmtpAccount;
use App\Models\BulkMailerTag;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    public bool $showTestModal = false;

    public ?int $editingId = null;
    public ?int $deleteId = null;
    public ?int $testId = null;

    public string $name = '';
    public string $host = '';
    public int|string $port = 587;
    public string $encryption = 'tls';
    public string $username = '';
    public string $password = '';
    public string $from_name = '';
    public string $from_email = '';
    public string $reply_to_email = '';
    public int|string $daily_limit = 500;
    public int|string $priority = 1;
    public bool $is_active = true;
    public string $notes = '';
    public string $tag_names = '';

    public string $test_email = '';

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
        $smtp = BulkMailerSmtpAccount::with('tags')->findOrFail($id);

        $this->editingId = $smtp->id;
        $this->name = $smtp->name;
        $this->host = $smtp->host;
        $this->port = $smtp->port;
        $this->encryption = $smtp->encryption ?? '';
        $this->username = $smtp->username;
        $this->password = '';
        $this->from_name = $smtp->from_name;
        $this->from_email = $smtp->from_email;
        $this->reply_to_email = $smtp->reply_to_email ?? '';
        $this->daily_limit = $smtp->daily_limit;
        $this->priority = $smtp->priority;
        $this->is_active = (bool) $smtp->is_active;
        $this->notes = $smtp->notes ?? '';
        $this->tag_names = $smtp->tags->pluck('name')->implode(', ');

        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), $this->messages());

        $payload = [
            'name' => $validated['name'],
            'host' => $validated['host'],
            'port' => (int) $validated['port'],
            'encryption' => $validated['encryption'] ?: null,
            'username' => $validated['username'],
            'from_name' => $validated['from_name'],
            'from_email' => $validated['from_email'],
            'reply_to_email' => $validated['reply_to_email'] ?: null,
            'daily_limit' => (int) $validated['daily_limit'],
            'priority' => (int) $validated['priority'],
            'is_active' => (bool) $validated['is_active'],
            'notes' => $validated['notes'] ?: null,
            'health_status' => BulkMailerSmtpHealthStatus::Unknown,
        ];

        if (filled($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        if ($this->editingId) {
            $smtp = BulkMailerSmtpAccount::findOrFail($this->editingId);
            $smtp->update($payload);
            $message = 'SMTP account updated successfully.';
        } else {
            $payload['password'] = $validated['password'];
            $smtp = BulkMailerSmtpAccount::create($payload);
            $message = 'SMTP account created successfully.';
        }

        $smtp->tags()->sync($this->syncTagIds());

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
        $smtp = BulkMailerSmtpAccount::findOrFail($this->deleteId);
        $smtp->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;

        session()->flash('success', 'SMTP account deleted successfully.');
    }

    public function toggleStatus(int $id): void
    {
        $smtp = BulkMailerSmtpAccount::findOrFail($id);
        $smtp->update([
            'is_active' => ! $smtp->is_active,
        ]);

        session()->flash('success', 'SMTP account status updated.');
    }

    public function openTestModal(int $id): void
    {
        $this->testId = $id;
        $this->test_email = auth()->user()->email ?? '';
        $this->resetValidation();
        $this->showTestModal = true;
    }

    public function sendTest(): void
    {
        $this->validate([
            'test_email' => ['required', 'email:rfc,dns'],
        ], [
            'test_email.required' => 'Test email is required.',
            'test_email.email' => 'Enter a valid test email address.',
        ]);

        $smtp = BulkMailerSmtpAccount::findOrFail($this->testId);

        try {
            Config::set('mail.mailers.bulk_mailer_test', [
                'transport' => 'smtp',
                'host' => $smtp->host,
                'port' => $smtp->port,
                'encryption' => blank($smtp->encryption) ? null : $smtp->encryption,
                'username' => $smtp->username,
                'password' => $smtp->decrypted_password,
                'timeout' => 30,
            ]);

            Mail::mailer('bulk_mailer_test')
                ->to($this->test_email)
                ->send(new \App\Mail\BulkMailerSmtpTestMail($smtp));

            $smtp->update([
                'health_status' => BulkMailerSmtpHealthStatus::Healthy,
            ]);

            $this->showTestModal = false;
            $this->testId = null;
            $this->test_email = '';

            session()->flash('success', 'Test email sent successfully.');
        } catch (\Throwable $e) {
            $smtp->update([
                'health_status' => BulkMailerSmtpHealthStatus::Failed,
            ]);

            $this->addError('test_email', 'Test email failed: '.$e->getMessage());
        }
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->showTestModal = false;
        $this->deleteId = null;
        $this->testId = null;
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->host = '';
        $this->port = 587;
        $this->encryption = 'tls';
        $this->username = '';
        $this->password = '';
        $this->from_name = '';
        $this->from_email = '';
        $this->reply_to_email = '';
        $this->daily_limit = 500;
        $this->priority = 1;
        $this->is_active = true;
        $this->notes = '';
        $this->tag_names = '';
        $this->resetValidation();
    }

    protected function rules(): array
    {
        $passwordRules = $this->editingId
            ? ['nullable', 'string', 'min:4']
            : ['required', 'string', 'min:4'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['nullable', Rule::in(['', 'tls', 'ssl'])],
            'username' => ['required', 'string', 'max:255'],
            'password' => $passwordRules,
            'from_name' => ['required', 'string', 'max:255'],
            'from_email' => ['required', 'email:rfc,dns', 'max:255'],
            'reply_to_email' => ['nullable', 'email:rfc,dns', 'max:255'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:1000000'],
            'priority' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'SMTP name is required.',
            'host.required' => 'SMTP host is required.',
            'port.required' => 'SMTP port is required.',
            'port.integer' => 'SMTP port must be a number.',
            'username.required' => 'SMTP username is required.',
            'password.required' => 'SMTP password is required.',
            'from_name.required' => 'From name is required.',
            'from_email.required' => 'From email is required.',
            'from_email.email' => 'From email must be valid.',
            'reply_to_email.email' => 'Reply-to email must be valid.',
            'daily_limit.required' => 'Daily limit is required.',
            'priority.required' => 'Priority is required.',
        ];
    }

    protected function syncTagIds(): array
    {
        $names = collect(explode(',', $this->tag_names))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->values();

        $ids = [];

        foreach ($names as $name) {
            $tag = BulkMailerTag::firstOrCreate(
                [
                    'type' => BulkMailerTagType::Smtp->value,
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                    'color' => null,
                ]
            );

            $ids[] = $tag->id;
        }

        return $ids;
    }

    public function getRowsProperty()
    {
        return BulkMailerSmtpAccount::query()
            ->with('tags')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('host', 'like', '%'.$this->search.'%')
                        ->orWhere('username', 'like', '%'.$this->search.'%')
                        ->orWhere('from_email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.bulk-mailer.smtp-accounts.index')
            ->layout('layouts.app')
            ->title('SMTP Accounts');
    }
}