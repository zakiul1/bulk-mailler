<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">SMTP Accounts</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Create, edit, test, enable, disable, monitor health, and organize SMTP accounts for campaign rotation.
                </p>
            </div>

            <button
                type="button"
                wire:click="create"
                class="border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
            >
                Add SMTP
            </button>
        </div>

        @if (session('success'))
            <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name, host, username, from email"
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                >
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Status</label>
                <select
                    wire:model.live="statusFilter"
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                >
                    <option value="all">All</option>
                    <option value="active">Active only</option>
                    <option value="inactive">Inactive only</option>
                </select>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm font-medium text-zinc-900 dark:text-white">Quick summary</div>
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                    Total: {{ $this->rows->total() }}
                </div>
            </div>
        </div>

        <div class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">SMTP</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">From</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Daily Limit</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Tags</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->rows as $row)
                            <tr>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $row->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row->host }}:{{ $row->port }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row->username }}</div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">{{ $row->from_name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row->from_email }}</div>
                                    @if ($row->reply_to_email)
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Reply-To: {{ $row->reply_to_email }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">{{ number_format($row->daily_limit) }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Sent today: {{ number_format($row->sent_today) }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Remaining: {{ number_format($row->remaining_today) }}</div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">{{ $row->is_active ? 'Active' : 'Inactive' }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Health: {{ $row->health_status?->value ?? $row->health_status }}
                                    </div>
                                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        Failures: {{ number_format($row->failure_count ?? 0) }} |
                                        Consecutive: {{ number_format($row->consecutive_failures ?? 0) }}
                                    </div>

                                    @if ($row->cooldown_until)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Cooldown Until: {{ $row->cooldown_until->format('Y-m-d h:i A') }}
                                        </div>
                                    @endif

                                    @if ($row->last_failed_at)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Last Failed: {{ $row->last_failed_at->format('Y-m-d h:i A') }}
                                        </div>
                                    @endif

                                    @if ($row->last_success_at)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Last Success: {{ $row->last_success_at->format('Y-m-d h:i A') }}
                                        </div>
                                    @endif

                                    @if ($row->auto_disabled_at)
                                        <div class="mt-1 text-xs text-red-600 dark:text-red-400">
                                            Auto Disabled: {{ $row->auto_disabled_at->format('Y-m-d h:i A') }}
                                        </div>
                                    @endif

                                    @if ($row->auto_disabled_reason)
                                        <div class="mt-1 text-xs text-red-600 dark:text-red-400">
                                            Reason: {{ $row->auto_disabled_reason }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($row->tags as $tag)
                                            <span class="border border-zinc-300 px-2 py-1 text-xs text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                                                {{ $tag->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">No tags</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="openTestModal({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Test
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="resetHealth({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Reset Health
                                        </button>

                                        @if ($row->auto_disabled_at)
                                            <button
                                                type="button"
                                                wire:click="reEnable({{ $row->id }})"
                                                class="border border-emerald-300 px-3 py-1.5 text-xs text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-300 dark:hover:bg-emerald-950"
                                            >
                                                Re-Enable
                                            </button>
                                        @endif

                                        <button
                                            type="button"
                                            wire:click="toggleStatus({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            {{ $row->is_active ? 'Disable' : 'Enable' }}
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="edit({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $row->id }})"
                                            class="border border-red-300 px-3 py-1.5 text-xs text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No SMTP accounts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $this->rows->links() }}
            </div>
        </div>
    </div>

    @if ($showFormModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-start justify-center px-4 py-6">
                <div class="flex max-h-[90vh] w-full max-w-4xl flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $editingId ? 'Edit SMTP Account' : 'Create SMTP Account' }}
                        </h2>

                        <button type="button" wire:click="closeModals" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">
                            Close
                        </button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Name</label>
                                <input type="text" wire:model.defer="name" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Host</label>
                                <input type="text" wire:model.defer="host" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('host') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Port</label>
                                <input type="number" wire:model.defer="port" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('port') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Encryption</label>
                                <select wire:model.defer="encryption" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                    <option value="">None</option>
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                                @error('encryption') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Username</label>
                                <input type="text" wire:model.defer="username" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('username') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">
                                    Password {{ $editingId ? '(leave blank to keep existing)' : '' }}
                                </label>
                                <input type="password" wire:model.defer="password" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('password') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">From Name</label>
                                <input type="text" wire:model.defer="from_name" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('from_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">From Email</label>
                                <input type="email" wire:model.defer="from_email" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('from_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Reply-To Email</label>
                                <input type="email" wire:model.defer="reply_to_email" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('reply_to_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Daily Limit</label>
                                <input type="number" wire:model.defer="daily_limit" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('daily_limit') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="flex items-center gap-3 pt-8">
                                <input id="is_active" type="checkbox" wire:model.defer="is_active" class="h-4 w-4 border border-zinc-300 dark:border-zinc-700">
                                <label for="is_active" class="text-sm text-zinc-900 dark:text-white">Active</label>
                                @error('is_active') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Tags</label>
                                <input type="text" wire:model.defer="tag_names" placeholder="marketing, backup, warmup" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Separate tags with commas.</div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Notes</label>
                                <textarea wire:model.defer="notes" rows="4" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></textarea>
                                @error('notes') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button type="button" wire:click="closeModals" class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                            Cancel
                        </button>

                        <button type="button" wire:click="save" class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-center justify-center px-4 py-6">
                <div class="w-full max-w-md border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Delete SMTP Account</h2>
                    </div>

                    <div class="p-6 text-sm text-zinc-600 dark:text-zinc-300">
                        Are you sure you want to delete this SMTP account? This action cannot be undone.
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button type="button" wire:click="closeModals" class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                            Cancel
                        </button>

                        <button type="button" wire:click="delete" class="border border-red-700 bg-red-700 px-4 py-2 text-sm text-white hover:bg-red-800">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showTestModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-center justify-center px-4 py-6">
                <div class="flex max-h-[90vh] w-full max-w-lg flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Send Test Email</h2>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Test Email Address</label>
                        <input type="email" wire:model.defer="test_email" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        @error('test_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button type="button" wire:click="closeModals" class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                            Cancel
                        </button>

                        <button type="button" wire:click="sendTest" class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            Send Test
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>