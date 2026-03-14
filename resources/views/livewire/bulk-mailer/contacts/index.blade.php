<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Contacts</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Manage recipients, import/export CSV, unsubscribe contacts, and suppress bounced emails.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('bulk-mailer.contacts.export') }}"
                    class="border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Export CSV
                </a>

                <button
                    type="button"
                    wire:click="openImportModal"
                    class="border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Import CSV
                </button>

                <a
                    href="{{ route('bulk-mailer.lists.index') }}"
                    wire:navigate
                    class="border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Manage Lists
                </a>

                <button
                    type="button"
                    wire:click="create"
                    class="border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Add Contact
                </button>
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

        <div class="grid gap-4 md:grid-cols-4">
            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by email or name"
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
                    <option value="active">Active</option>
                    <option value="unsubscribed">Unsubscribed</option>
                    <option value="bounced">Bounced</option>
                    <option value="suppressed">Suppressed</option>
                </select>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">List</label>
                <select
                    wire:model.live="listFilter"
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                >
                    <option value="all">All lists</option>
                    @foreach ($this->lists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Lists</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Verification</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Delivery State</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->rows as $row)
                            <tr>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ $row->full_name ?: 'No name' }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row->email }}</div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($row->lists as $list)
                                            <span class="border border-zinc-300 px-2 py-1 text-xs text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                                                {{ $list->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">No lists</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        {{ $row->verification?->status?->label() ?? ucfirst($row->verification_status) }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="space-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        <div>Status: {{ $row->status?->label() ?? $row->status }}</div>
                                        <div>Unsubscribed: {{ $row->unsubscribed_at ? $row->unsubscribed_at->format('Y-m-d H:i') : 'No' }}</div>
                                        <div>Bounced: {{ $row->bounced_at ? $row->bounced_at->format('Y-m-d H:i') : 'No' }}</div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button type="button" wire:click="edit({{ $row->id }})" class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                                            Edit
                                        </button>

                                        <button type="button" wire:click="markUnsubscribed({{ $row->id }})" class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                                            Unsubscribe
                                        </button>

                                        <button type="button" wire:click="markBounced({{ $row->id }})" class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                                            Bounce
                                        </button>

                                        <button type="button" wire:click="reactivate({{ $row->id }})" class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                                            Reactivate
                                        </button>

                                        <button type="button" wire:click="confirmDelete({{ $row->id }})" class="border border-red-300 px-3 py-1.5 text-xs text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
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

    @if ($showFormModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-start justify-center px-4 py-6">
                <div class="flex max-h-[90vh] w-full max-w-3xl flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $editingId ? 'Edit Contact' : 'Create Contact' }}
                        </h2>

                        <button type="button" wire:click="closeModals" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">
                            Close
                        </button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Email</label>
                                <input type="email" wire:model.defer="email" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">First Name</label>
                                <input type="text" wire:model.defer="first_name" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('first_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Last Name</label>
                                <input type="text" wire:model.defer="last_name" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('last_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Status</label>
                                <select wire:model.defer="status" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                    <option value="active">Active</option>
                                    <option value="unsubscribed">Unsubscribed</option>
                                    <option value="bounced">Bounced</option>
                                    <option value="suppressed">Suppressed</option>
                                </select>
                                @error('status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <div class="mb-2 flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-900 dark:text-white">Assign Lists</label>

                                    <a
                                        href="{{ route('bulk-mailer.lists.index') }}"
                                        wire:navigate
                                        class="text-xs text-zinc-600 underline dark:text-zinc-300"
                                    >
                                        Manage lists
                                    </a>
                                </div>

                                <div class="grid gap-2 md:grid-cols-2">
                                    @forelse ($this->lists as $list)
                                        <label class="flex items-center gap-3 border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700">
                                            <input type="checkbox" wire:model.defer="selected_lists" value="{{ $list->id }}" class="h-4 w-4 border border-zinc-300 dark:border-zinc-700">
                                            <span class="text-zinc-900 dark:text-white">{{ $list->name }}</span>
                                        </label>
                                    @empty
                                        <div class="border border-zinc-300 px-3 py-3 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400 md:col-span-2">
                                            No active lists found. Create a list first.
                                        </div>
                                    @endforelse
                                </div>

                                @error('selected_lists') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                @error('selected_lists.*') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
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

    @if ($showImportModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-start justify-center px-4 py-6">
                <div class="flex max-h-[90vh] w-full max-w-3xl flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Import Contacts CSV</h2>

                        <button type="button" wire:click="closeModals" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">
                            Close
                        </button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">CSV File</label>
                                <input type="file" wire:model="import_file" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('import_file') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror

                                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    Supported columns: email, first_name, last_name, status, notes
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Assign Imported Contacts To Lists</label>
                                <div class="grid gap-2 md:grid-cols-2">
                                    @forelse ($this->lists as $list)
                                        <label class="flex items-center gap-3 border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700">
                                            <input type="checkbox" wire:model="import_list_ids" value="{{ $list->id }}" class="h-4 w-4 border border-zinc-300 dark:border-zinc-700">
                                            <span class="text-zinc-900 dark:text-white">{{ $list->name }}</span>
                                        </label>
                                    @empty
                                        <div class="border border-zinc-300 px-3 py-3 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400 md:col-span-2">
                                            No active lists found.
                                        </div>
                                    @endforelse
                                </div>
                                @error('import_list_ids') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                @error('import_list_ids.*') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button type="button" wire:click="closeModals" class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                            Cancel
                        </button>

                        <button type="button" wire:click="importCsv" class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            Import
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
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Delete Contact</h2>
                    </div>

                    <div class="p-6 text-sm text-zinc-600 dark:text-zinc-300">
                        Are you sure you want to delete this contact? This action cannot be undone.
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
</div>