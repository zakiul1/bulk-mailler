<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">SMTP Analytics</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Review SMTP performance, delivery failures, bounce counts, and daily usage.
            </p>
        </div>

        <div class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">SMTP</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Sent</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Failed</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Bounces</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Today Usage</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Remaining</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($this->rows as $row)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $row['smtp']->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row['smtp']->host }}:{{ $row['smtp']->port }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($row['sent']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($row['failed']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($row['bounces']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($row['today_usage']) }}</td>
                                <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">{{ number_format($row['smtp']->remaining_today) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No SMTP analytics data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>