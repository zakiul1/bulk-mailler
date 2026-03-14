<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                    Bulk Mailer Dashboard
                </h1>

                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    Phase 1 foundation is ready. SMTP accounts, lists, templates, campaigns, verification,
                    queue processing, and rotation modules will be added step by step.
                </p>
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

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($this->stats as $stat)
                <div class="border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</div>
                    <div class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $stat['value'] }}</div>
                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $stat['description'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Phase roadmap</h2>

                <div class="mt-4 space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                    <div class="border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <span class="font-medium text-zinc-900 dark:text-white">Phase 1</span>
                        — foundation, migrations, models, routes, sidebar, dashboard
                    </div>
                    <div class="border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <span class="font-medium text-zinc-900 dark:text-white">Phase 2</span>
                        — SMTP accounts module
                    </div>
                    <div class="border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <span class="font-medium text-zinc-900 dark:text-white">Phase 3</span>
                        — contacts and lists
                    </div>
                    <div class="border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <span class="font-medium text-zinc-900 dark:text-white">Phase 4+</span>
                        — verification, templates, campaigns, queue processing, reports
                    </div>
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Recent campaigns</h2>

                <div class="mt-4">
                    @forelse ($this->recentCampaigns as $campaign)
                        <div class="flex items-center justify-between border-b border-zinc-100 py-3 last:border-0 dark:border-zinc-800">
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $campaign->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $campaign->status?->value ?? $campaign->status }}
                                </div>
                            </div>

                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $campaign->created_at?->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            No campaigns yet. Campaign builder will be added in a later phase.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>