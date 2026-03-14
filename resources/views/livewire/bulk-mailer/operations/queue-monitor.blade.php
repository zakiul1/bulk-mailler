<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Operations</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Retry failed deliveries, import bounce lists, and review delivery events.
                </p>
            </div>

            <button
                type="button"
                wire:click="openBounceImportModal"
                class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
            >
                Import Bounces
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

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Recent Campaigns</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Campaign</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Retry</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse ($this->campaigns as $campaign)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $campaign->name }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $campaign->subject }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                        {{ $campaign->status?->label() ?? $campaign->status }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <button
                                            type="button"
                                            wire:click="retryCampaign({{ $campaign->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Retry
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        No campaigns found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Recent Delivery Events</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Event</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Message</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse ($this->recentEvents as $event)
                                <tr>
                                    <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ $event->email }}</td>
                                    <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ ucfirst($event->event_type) }}</td>
                                    <td class="px-4 py-4 text-xs text-zinc-500 dark:text-zinc-400">{{ $event->message ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        No delivery events found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Failed Recipients</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Recipient</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Campaign</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Error</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Retry</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->failedRecipients as $recipient)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ $recipient->contact?->full_name ?: $recipient->email }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $recipient->email }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $recipient->campaign?->name ?: 'N/A' }}
                                </td>
                                <td class="px-4 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $recipient->error_message ?: '-' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <button
                                        type="button"
                                        wire:click="retryRecipient({{ $recipient->id }})"
                                        class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                    >
                                        Retry
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No failed recipients found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $this->failedRecipients->links() }}
            </div>
        </div>
    </div>

    @if ($showBounceImportModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-start justify-center px-4 py-6">
                <div class="flex max-h-[90vh] w-full max-w-2xl flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Import Bounce CSV</h2>

                        <button type="button" wire:click="closeModals" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">
                            Close
                        </button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Bounce CSV File</label>
                                <input type="file" wire:model="bounce_file" class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                @error('bounce_file') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror

                                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    CSV format: first column = email, second column = message
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button type="button" wire:click="closeModals" class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                            Cancel
                        </button>

                        <button type="button" wire:click="importBounces" class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            Import
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>