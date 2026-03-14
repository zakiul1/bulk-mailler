<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Templates</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Create and manage reusable email templates for campaigns.
                </p>
            </div>

            <button
                type="button"
                wire:click="create"
                class="border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
            >
                Add Template
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
            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by template name or subject"
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
        </div>

        <div class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Template</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->rows as $row)
                            <tr>
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $row->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Updated {{ $row->updated_at?->diffForHumans() }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">{{ $row->subject }}</div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        {{ $row->is_active ? 'Active' : 'Inactive' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="openPreview({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Preview
                                        </button>

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
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No templates found.
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
                <div class="flex max-h-[90vh] w-full max-w-6xl flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $editingId ? 'Edit Template' : 'Create Template' }}
                        </h2>

                        <button type="button" wire:click="closeModals" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">
                            Close
                        </button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-6 xl:grid-cols-3">
                            <div class="xl:col-span-2">
                                <div class="grid gap-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Template Name</label>
                                        <input
                                            type="text"
                                            wire:model.defer="name"
                                            class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                        >
                                        @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Subject</label>
                                        <input
                                            type="text"
                                            wire:model.defer="subject"
                                            class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                        >
                                        @error('subject') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                 <div>
    <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">HTML Content</label>
    <textarea
        wire:model.defer="html_content"
        rows="16"
        class="w-full border border-zinc-300 px-3 py-2 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
        placeholder="<h1>Hello @{{first_name}}</h1>"
    ></textarea>
    @error('html_content') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
</div>

<div>
    <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Text Content</label>
    <textarea
        wire:model.defer="text_content"
        rows="8"
        class="w-full border border-zinc-300 px-3 py-2 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
        placeholder="Hello @{{first_name}}"
    ></textarea>
    @error('text_content') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
</div>

                                    <div class="flex items-center gap-3">
                                        <input
                                            id="template_is_active"
                                            type="checkbox"
                                            wire:model.defer="is_active"
                                            class="h-4 w-4 border border-zinc-300 dark:border-zinc-700"
                                        >
                                        <label for="template_is_active" class="text-sm text-zinc-900 dark:text-white">Active</label>
                                        @error('is_active') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Placeholders</h3>

                                    <div class="mt-3 space-y-3">
                                        @foreach ($this->placeholderExamples() as $key => $label)
                                            <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                                                <div class="font-mono text-xs text-zinc-900 dark:text-white">{{ $key }}</div>
                                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4 border border-zinc-200 p-4 dark:border-zinc-700">
                                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Tips</h3>

                                    <div class="mt-3 space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        <div>Use HTML Content for rich email design.</div>
                                        <div>Use Text Content as fallback.</div>
                                        <div>Keep subject lines short and clear.</div>
                                        <div>Do not paste full page scripts here.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            wire:click="save"
                            class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
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
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Delete Template</h2>
                    </div>

                    <div class="p-6 text-sm text-zinc-600 dark:text-zinc-300">
                        Are you sure you want to delete this template? This action cannot be undone.
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
                            class="border border-red-700 bg-red-700 px-4 py-2 text-sm text-white hover:bg-red-800"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showPreviewModal && $this->previewTemplate)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-start justify-center px-4 py-6">
                <div class="flex max-h-[90vh] w-full max-w-5xl flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Template Preview</h2>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $this->previewTemplate->name }}
                            </div>
                        </div>

                        <button type="button" wire:click="closeModals" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">
                            Close
                        </button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">Subject</div>
                                <div class="border border-zinc-200 p-3 text-sm text-zinc-900 dark:border-zinc-700 dark:text-white">
                                    {{ $this->previewTemplate->subject }}
                                </div>

                                <div class="mt-4 mb-3 text-sm font-semibold text-zinc-900 dark:text-white">HTML Preview</div>
                                <div class="min-h-[300px] border border-zinc-200 p-4 dark:border-zinc-700">
                                    {!! $this->previewTemplate->html_content ?: '<div class="text-sm text-zinc-500">No HTML content.</div>' !!}
                                </div>
                            </div>

                            <div class="border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">Text Version</div>
                                <pre class="min-h-[300px] whitespace-pre-wrap border border-zinc-200 p-4 text-sm text-zinc-900 dark:border-zinc-700 dark:text-white">{{ $this->previewTemplate->text_content ?: 'No text content.' }}</pre>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>