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
        }
    }"
    x-on:notify.window="showToast($event.detail.type, $event.detail.message)"
    class="px-4 py-6 lg:px-6"
>
    <div class="flex flex-col gap-6">
        @if ($this->latestImport)
            <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900" wire:poll.3s>
                <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Import Progress</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                            Real-time upload status and report for the latest import.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="refreshImport"
                        class="border border-zinc-900 bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Refresh
                    </button>
                </div>

                <div class="space-y-4 p-4">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Category</div>
                            <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->latestImport->category?->name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Source</div>
                            <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->latestImport->source_name ?: '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Status</div>
                            <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ ucfirst($this->latestImport->status) }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>Progress</span>
                            <span>{{ $this->importProgressPercent }}%</span>
                        </div>

                        <div class="h-2 w-full overflow-hidden border border-zinc-300 dark:border-zinc-700">
                            <div
                                class="h-full bg-zinc-900 dark:bg-white"
                                style="width: {{ $this->importProgressPercent }}%;"
                            ></div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Read</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ $this->latestImport->total_read }}</div>
                        </div>

                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Processed</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ $this->latestImport->processed_count }}</div>
                        </div>

                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Valid</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ $this->latestImport->valid_count }}</div>
                        </div>

                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Invalid</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ $this->latestImport->invalid_count }}</div>
                        </div>

                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Duplicate</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ $this->latestImport->duplicate_count }}</div>
                        </div>

                        <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Inserted</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ $this->latestImport->inserted_count }}</div>
                        </div>
                    </div>

                    @if ($this->latestImport->error_message)
                        <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                            {{ $this->latestImport->error_message }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Add Contacts</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Upload contact emails by category using pasted text or TXT/CSV file.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('bulk-mailer.contacts.index') }}"
                    wire:navigate
                    class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Back to Contacts
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

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Upload Emails</h2>
                    </div>

                    <div class="grid gap-6 p-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Category</label>
                            <select
                                wire:model="bulk_mailer_contact_list_id"
                                class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                            >
                                <option value="">Select category</option>
                                @foreach ($this->lists as $list)
                                    <option value="{{ $list->id }}">{{ $list->name }}</option>
                                @endforeach
                            </select>
                            @error('bulk_mailer_contact_list_id')
                                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                     <div class="border border-zinc-200 dark:border-zinc-700">
    <div class="flex border-b border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-950">
        <button
            type="button"
            wire:click="switchTab('text')"
            @class([
                'px-4 py-3 text-sm font-medium transition-colors',
                'bg-black text-white' => $activeTab === 'text',
                'text-zinc-700 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-800' => $activeTab !== 'text',
            ])
        >
            Text Area
        </button>

        <button
            type="button"
            wire:click="switchTab('file')"
            @class([
                'px-4 py-3 text-sm font-medium transition-colors',
                'bg-black text-white' => $activeTab === 'file',
                'text-zinc-700 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-800' => $activeTab !== 'file',
            ])
        >
            File Upload
        </button>
    </div>

    <div class="p-4">
        @if ($activeTab === 'text')
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Paste Emails</label>
                <textarea
                    wire:model.defer="emails_text"
                    rows="14"
                    placeholder="Paste emails here. Supports line break, comma, semicolon, or mixed format."
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                ></textarea>
                @error('emails_text')
                    <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                @enderror

                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Supported separators: new line, comma, semicolon, and mixed pasted text.
                </div>
            </div>
        @endif

        @if ($activeTab === 'file')
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Upload TXT or CSV File</label>
                <input
                    type="file"
                    wire:model="import_file"
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                >
                @error('import_file')
                    <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                @enderror

                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    File can contain one email per line, comma-separated emails, or CSV rows.
                </div>
            </div>
        @endif
    </div>
</div>

                        <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                            <a
                                href="{{ route('bulk-mailer.contacts.index') }}"
                                wire:navigate
                                class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                            >
                                Cancel
                            </a>

                            <button
                                type="button"
                                wire:click="save"
                                wire:loading.attr="disabled"
                                class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-60 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                            >
                                <span wire:loading.remove wire:target="save">Queue Import</span>
                                <span wire:loading wire:target="save">Queueing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Rules</h2>
                    </div>

                    <div class="space-y-3 p-4 text-sm text-zinc-600 dark:text-zinc-300">
                        <div>Category is required.</div>
                        <div>Same email is allowed in another category.</div>
                        <div>Same email will not be inserted twice in the same category.</div>
                        <div>Invalid emails are skipped and counted in the report.</div>
                        <div>Large files are processed in the queue in chunks.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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