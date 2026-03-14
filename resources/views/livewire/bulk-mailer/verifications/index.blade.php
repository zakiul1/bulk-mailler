<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Email Verifications</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Verify contacts before campaign sending. Only verified valid contacts will be counted for delivery.
                </p>
            </div>

            <button
                type="button"
                wire:click="verifyPage"
                class="border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
            >
                Verify Visible Contacts
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
                    placeholder="Search by email or name"
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                >
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Verification Status</label>
                <select
                    wire:model.live="statusFilter"
                    class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                >
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="valid">Valid</option>
                    <option value="invalid">Invalid</option>
                    <option value="risky">Risky</option>
                    <option value="unknown">Unknown</option>
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

                                    @if ($row->verification?->reason)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $row->verification->reason }}
                                        </div>
                                    @endif

                                    @if ($row->verification?->checked_at)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Checked {{ $row->verification->checked_at->diffForHumans() }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex justify-end">
                                        <button
                                            type="button"
                                            wire:click="verify({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Verify
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
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
</div>