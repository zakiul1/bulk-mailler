<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Campaign Calendar</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Review upcoming, overdue, and scheduled campaigns in one place.
            </p>
        </div>

        <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Campaign</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Scheduled Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Recipients</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">View</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->upcomingCampaigns as $campaign)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $campaign->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $campaign->subject }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $campaign->scheduled_at?->format('Y-m-d h:i A') ?: 'N/A' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ $campaign->status?->label() ?? $campaign->status }}
                                    @if ($campaign->scheduled_at && $campaign->scheduled_at->isPast() && ($campaign->status?->value ?? $campaign->status) === 'scheduled')
                                        <div class="mt-1 text-xs text-red-600">Overdue</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                    {{ number_format($campaign->total_recipients) }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a
                                        href="{{ route('bulk-mailer.campaigns.show', $campaign) }}"
                                        wire:navigate
                                        class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                    >
                                        Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No scheduled campaigns found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>