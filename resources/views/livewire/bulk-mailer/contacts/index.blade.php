<div
    x-data="{
        toast: { show: false, type: 'success', message: '' },

        showToast(type, message) {
            this.toast.type = type;
            this.toast.message = message;
            this.toast.show = true;

            setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        },

        copyContacts(text) {
            navigator.clipboard.writeText(text || '')
                .then(() => this.showToast('success', 'Contacts copied successfully.'))
                .catch(() => this.showToast('error', 'Failed to copy contacts.'));
        }
    }"
    x-on:notify.window="showToast($event.detail.type, $event.detail.message)"
    x-on:copy-contacts.window="copyContacts($event.detail.text)"
    class="px-4 py-6 lg:px-6"
>
    <div class="flex flex-col gap-6">
        @if ($this->latestDeleteJob)
            <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900" wire:poll.2s="pollDeleteJob">
                <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Delete Progress</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                            Real-time delete progress for the latest delete action.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="clearDeleteJob"
                        class="border border-zinc-900 bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Refresh
                    </button>
                </div>

                <div class="space-y-4 p-4">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Status</div>
                            <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ ucfirst($this->latestDeleteJob->status) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Category</div>
                            <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->latestDeleteJob->category?->name ?? 'All / Mixed' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Selection Type</div>
                            <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ ucfirst($this->latestDeleteJob->selection_type) }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>Progress</span>
                            <span>{{ $this->latestDeleteJob->progress_percent }}%</span>
                        </div>

                        <div class="h-2 w-full overflow-hidden border border-zinc-300 dark:border-zinc-700">
                            <div
                                class="h-full bg-red-600 dark:bg-red-500"
                                style="width: {{ $this->latestDeleteJob->progress_percent }}%;"
                            ></div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $this->latestDeleteJob->total_count }}
                            </div>
                        </div>

                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Processed</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $this->latestDeleteJob->processed_count }}
                            </div>
                        </div>

                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Deleted</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $this->latestDeleteJob->deleted_count }}
                            </div>
                        </div>
                    </div>

                    @if ($this->latestDeleteJob->error_message)
                        <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                            {{ $this->latestDeleteJob->error_message }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Contacts</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Manage category-wise contact emails with organized search, bulk actions, and clean selection tools.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('bulk-mailer.contacts.create') }}"
                    wire:navigate
                    class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Add Contacts
                </a>

                <a
                    href="{{ route('bulk-mailer.lists.index') }}"
                    wire:navigate
                    class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Manage Categories
                </a>
            </div>
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

        <div class="grid gap-4 lg:grid-cols-4">
            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-3">
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by email"
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                >
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Category</label>
                <select
                    wire:model.live="listFilter"
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                >
                    <option value="all">All categories</option>
                    @foreach ($this->lists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input
                            type="checkbox"
                            wire:model.live="selectAllMatching"
                            class="h-4 w-4 border border-zinc-300 dark:border-zinc-700"
                        >
                        <span>
                            {{ $listFilter !== 'all' ? 'Select all in selected category' : 'Select all matching results' }}
                        </span>
                    </label>

                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $this->selectedCount }} selected
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        wire:click="copySelected"
                        class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Copy Selected
                    </button>

                    <button
                        type="button"
                        wire:click="confirmBulkDelete"
                        class="border border-red-600 bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 dark:border-red-500 dark:bg-red-500 dark:hover:bg-red-600"
                    >
                        Delete Selected
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <span class="sr-only">Select</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->rows as $row)
                            <tr>
                                <td class="px-4 py-4 align-top">
                                    <input
                                        type="checkbox"
                                        wire:model.live="selected"
                                        value="{{ $row->id }}"
                                        class="h-4 w-4 border border-zinc-300 dark:border-zinc-700"
                                    >
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ $row->email }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        {{ $row->category?->name ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $row->id }})"
                                            class="border border-red-600 bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 dark:border-red-500 dark:bg-red-500 dark:hover:bg-red-600"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No contacts found.
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

    @if ($showDeleteModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-center justify-center px-4 py-6">
                <div class="w-full max-w-md border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Delete Contact</h2>
                    </div>

                    <div class="p-6 text-sm text-zinc-600 dark:text-zinc-300">
                        Are you sure you want to delete this contact? This action cannot be undone.
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            wire:click="delete"
                            class="border border-red-600 bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 dark:border-red-500 dark:bg-red-500 dark:hover:bg-red-600"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showBulkDeleteModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-center justify-center px-4 py-6">
                <div class="w-full max-w-md border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Delete Selected Contacts</h2>
                    </div>

                    <div class="p-6 text-sm text-zinc-600 dark:text-zinc-300">
                        Are you sure you want to delete the selected contacts? This action cannot be undone.
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            wire:click="bulkDelete"
                            class="border border-red-600 bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 dark:border-red-500 dark:bg-red-500 dark:hover:bg-red-600"
                        >
                            Delete Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div
        x-cloak
        x-show="toast.show"
        x-transition
        class="fixed bottom-4 right-4 z-[60] w-full max-w-sm"
    >
        <div
            class="border px-4 py-3 shadow-lg"
            :class="toast.type === 'success'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                : 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300'"
        >
            <div class="text-sm font-medium" x-text="toast.message"></div>
        </div>
    </div>
</div>