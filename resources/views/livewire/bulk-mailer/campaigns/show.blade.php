<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-start justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $campaign->name }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    {{ $campaign->subject ?: ($campaign->subject_a ?: 'No subject') }}
                </p>
            </div>

            <a
                href="{{ route('bulk-mailer.campaigns.index') }}"
                wire:navigate
                class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
            >
                Back
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Status</div>
                <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ $campaign->status?->label() ?? $campaign->status }}
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Recipients</div>
                <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ number_format($campaign->total_recipients) }}
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Sent</div>
                <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ number_format($campaign->sent_count) }}
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Failed</div>
                <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ number_format($campaign->failed_count) }}
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Delivered Events</div>
                <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ number_format($this->totals['delivered']) }}
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Open Events</div>
                <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ number_format($this->totals['opens']) }}
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Click Events</div>
                <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ number_format($this->totals['clicks']) }}
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Bounce Events</div>
                <div class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ number_format($this->totals['bounces']) }}
                </div>
            </div>
        </div>

        @if ($campaign->ab_testing_enabled)
            <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">A/B Subject Performance</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Variant</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Subject</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Sent</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Failed</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Opens</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Clicks</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">A</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ $campaign->subject_a ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($this->variantStats['A']['sent']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($this->variantStats['A']['failed']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($this->variantStats['A']['opens']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($this->variantStats['A']['clicks']) }}</td>
                            </tr>

                            <tr>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">B</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ $campaign->subject_b ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($this->variantStats['B']['sent']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($this->variantStats['B']['failed']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($this->variantStats['B']['opens']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($this->variantStats['B']['clicks']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-1 border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Campaign Info</h2>

                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Template</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            {{ $campaign->template?->name ?: 'No template' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">SMTP Group</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            {{ $campaign->smtpGroup?->name ?: 'No SMTP group' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Segment</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            {{ $campaign->segment?->name ?: 'No segment' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">A/B Testing</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            {{ $campaign->ab_testing_enabled ? 'Enabled' : 'Disabled' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Created By</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            {{ $campaign->creator?->name ?: 'Unknown' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Started At</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            {{ $campaign->started_at?->format('Y-m-d h:i A') ?: 'N/A' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Completed At</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            {{ $campaign->completed_at?->format('Y-m-d h:i A') ?: 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Lists</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse ($campaign->lists as $list)
                            <span class="border border-zinc-300 px-2 py-1 text-xs text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                                {{ $list->name }}
                            </span>
                        @empty
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">No lists</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2 border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Recipient Logs</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Recipient</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Variant</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">SMTP</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Sent At</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Error</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse ($campaign->recipients as $recipient)
                                <tr>
                                    <td class="px-4 py-4 align-top">
                                        <div class="font-medium text-zinc-900 dark:text-white">
                                            {{ $recipient->contact?->full_name ?: $recipient->email }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $recipient->email }}</div>
                                    </td>

                                    <td class="px-4 py-4 align-top text-sm text-zinc-900 dark:text-white">
                                        {{ $recipient->subject_variant ?: '-' }}
                                    </td>

                                    <td class="px-4 py-4 align-top text-sm text-zinc-900 dark:text-white">
                                        {{ $recipient->smtpAccount?->name ?: 'N/A' }}
                                    </td>

                                    <td class="px-4 py-4 align-top text-sm text-zinc-900 dark:text-white">
                                        {{ $recipient->status?->label() ?? $recipient->status }}
                                    </td>

                                    <td class="px-4 py-4 align-top text-sm text-zinc-900 dark:text-white">
                                        {{ $recipient->sent_at?->format('Y-m-d h:i A') ?: 'N/A' }}
                                    </td>

                                    <td class="px-4 py-4 align-top text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $recipient->error_message ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        No recipient logs yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>