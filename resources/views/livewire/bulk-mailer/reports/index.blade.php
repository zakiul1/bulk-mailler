<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Reports</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Overview of campaigns, recipients, SMTP usage, opens, clicks, and A/B activity.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->stats as $stat)
                <div class="border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</div>
                    <div class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($stat['value']) }}
                    </div>
                </div>
            @endforeach
        </div>

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
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">A/B</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">View</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse ($this->recentCampaigns as $campaign)
                                <tr>
                                    <td class="px-4 py-4 align-top">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $campaign->name }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $campaign->subject ?: ($campaign->subject_a ?: 'No subject') }}
                                        </div>

                                        @if ($campaign->segment)
                                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                Segment: {{ $campaign->segment->name }}
                                            </div>
                                        @endif

                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Total: {{ number_format($campaign->total_recipients) }} |
                                            Sent: {{ number_format($campaign->sent_count) }} |
                                            Failed: {{ number_format($campaign->failed_count) }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <div class="text-sm text-zinc-900 dark:text-white">
                                            {{ $campaign->status?->label() ?? $campaign->status }}
                                        </div>

                                        @if ($campaign->scheduled_at)
                                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $campaign->scheduled_at->format('Y-m-d h:i A') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 align-top text-sm text-zinc-900 dark:text-white">
                                        {{ $campaign->ab_testing_enabled ? 'Enabled' : 'No' }}
                                    </td>

                                    <td class="px-4 py-4 text-right align-top">
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
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        No campaign data yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">SMTP Usage</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-950">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">SMTP</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Emails Sent</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse ($this->smtpUsage as $usage)
                                <tr>
                                    <td class="px-4 py-4 align-top">
                                        <div class="text-sm text-zinc-900 dark:text-white">
                                            {{ $usage->smtpAccount?->name ?: 'Deleted SMTP' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top text-sm text-zinc-900 dark:text-white">
                                        {{ $usage->usage_date?->format('Y-m-d') }}
                                    </td>
                                    <td class="px-4 py-4 align-top text-sm text-zinc-900 dark:text-white">
                                        {{ number_format($usage->emails_sent) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        No SMTP usage data yet.
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