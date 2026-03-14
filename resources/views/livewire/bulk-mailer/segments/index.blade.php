<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Segments</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Save reusable audience filters and use them in campaigns.
                </p>
            </div>

            <button
                type="button"
                wire:click="create"
                class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
            >
                Add Segment
            </button>
        </div>

        @if (session('success'))
            <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Segment</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Preview Count</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->rows as $row)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $row->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row->description }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $row->is_active ? 'Active' : 'Inactive' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ number_format($this->segmentCount($row->id)) }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" wire:click="edit({{ $row->id }})" class="border border-zinc-300 px-3 py-1.5 text-xs dark:border-zinc-700">
                                            Edit
                                        </button>
                                        <button type="button" wire:click="confirmDelete({{ $row->id }})" class="border border-red-300 px-3 py-1.5 text-xs text-red-700 dark:border-red-800 dark:text-red-300">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No segments found.
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
                            {{ $editingId ? 'Edit Segment' : 'Create Segment' }}
                        </h2>
                        <button type="button" wire:click="closeModals" class="text-sm text-zinc-500">Close</button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Name</label>
                                <input type="text" wire:model.defer="name" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Description</label>
                                <textarea wire:model.defer="description" rows="3" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></textarea>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                <label class="flex items-center gap-3 border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700">
                                    <input type="checkbox" wire:model.defer="is_active">
                                    <span>Active</span>
                                </label>

                                <label class="flex items-center gap-3 border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700">
                                    <input type="checkbox" wire:model.defer="only_active">
                                    <span>Only active contacts</span>
                                </label>

                                <label class="flex items-center gap-3 border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700">
                                    <input type="checkbox" wire:model.defer="require_verified_valid">
                                    <span>Require verified valid</span>
                                </label>

                                <label class="flex items-center gap-3 border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700">
                                    <input type="checkbox" wire:model.defer="exclude_unsubscribed">
                                    <span>Exclude unsubscribed</span>
                                </label>

                                <label class="flex items-center gap-3 border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 md:col-span-2">
                                    <input type="checkbox" wire:model.defer="exclude_bounced">
                                    <span>Exclude bounced</span>
                                </label>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Limit To Lists</label>
                                <div class="grid gap-2 md:grid-cols-2">
                                    @foreach ($this->lists as $list)
                                        <label class="flex items-center gap-3 border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700">
                                            <input type="checkbox" wire:model.defer="list_ids" value="{{ $list->id }}">
                                            <span>{{ $list->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button type="button" wire:click="closeModals" class="border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-700">Cancel</button>
                        <button type="button" wire:click="save" class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white dark:border-white dark:bg-white dark:text-zinc-900">
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
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Delete Segment</h2>
                    </div>

                    <div class="p-6 text-sm text-zinc-600 dark:text-zinc-300">
                        Are you sure you want to delete this segment?
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button type="button" wire:click="closeModals" class="border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-700">Cancel</button>
                        <button type="button" wire:click="delete" class="border border-red-700 bg-red-700 px-4 py-2 text-sm text-white">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>