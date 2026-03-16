<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Campaigns</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Create campaigns, connect templates, choose categories, assign SMTP groups, test delivery, and launch sending.
                </p>
            </div>

            <button
                type="button"
                wire:click="create"
                class="border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
            >
                Add Campaign
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
                    placeholder="Search by campaign name or subject"
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
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="paused">Paused</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Campaign</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Template</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Categories</th>
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
                                        {{ $row->subject ?: ($row->subject_a ?: 'No subject') }}
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        Recipients: {{ number_format($row->total_recipients) }} |
                                        Sent: {{ number_format($row->sent_count) }} |
                                        Failed: {{ number_format($row->failed_count) }}
                                    </div>

                                    @if ($row->ab_testing_enabled)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            A/B Testing: Enabled
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        {{ $row->template?->name ?: 'No template' }}
                                    </div>

                                    @if ($row->smtpGroup)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            SMTP Group: {{ $row->smtpGroup->name }}
                                        </div>
                                    @endif

                                    @if ($row->segment)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Segment: {{ $row->segment->name }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($row->lists as $list)
                                            <span class="border border-zinc-300 px-2 py-1 text-xs text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                                                {{ $list->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">No categories</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        {{ $row->status?->label() ?? $row->status }}
                                    </div>

                                    @if ($row->scheduled_at)
                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $row->scheduled_at->format('Y-m-d h:i A') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a
                                            href="{{ route('bulk-mailer.campaigns.show', $row) }}"
                                            wire:navigate
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Details
                                        </a>

                                        <button
                                            type="button"
                                            wire:click="duplicate({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Duplicate
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="openRescheduleModal({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Reschedule
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="openTestModal({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Test
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="launch({{ $row->id }})"
                                            class="border border-zinc-900 bg-zinc-900 px-3 py-1.5 text-xs text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                                        >
                                            Launch
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="launchNow({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Launch Now
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="pause({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Pause
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="resume({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Resume
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="cancelCampaign({{ $row->id }})"
                                            class="border border-zinc-300 px-3 py-1.5 text-xs text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                                        >
                                            Cancel
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
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No campaigns found.
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
                <div class="flex max-h-[90vh] w-full max-w-5xl flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $editingId ? 'Edit Campaign' : 'Create Campaign' }}
                        </h2>

                        <button type="button" wire:click="closeModals" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">
                            Close
                        </button>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <div class="grid gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Campaign Name</label>
                                <input
                                    type="text"
                                    wire:model.defer="name"
                                    class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                >
                                @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Template</label>
                                    <select
                                        wire:model.live="bulk_mailer_template_id"
                                        class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                        <option value="">No template</option>
                                        @foreach ($this->templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bulk_mailer_template_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <div class="mb-2 flex items-center justify-between">
                                        <label class="block text-sm font-medium text-zinc-900 dark:text-white">Select Categories</label>

                                        <a
                                            href="{{ route('bulk-mailer.lists.index') }}"
                                            wire:navigate
                                            class="text-xs text-zinc-600 underline dark:text-zinc-300"
                                        >
                                            Manage categories
                                        </a>
                                    </div>

                                    <div class="border border-zinc-300 dark:border-zinc-700">
                                        <div class="border-b border-zinc-200 p-3 dark:border-zinc-700">
                                            <input
                                                type="text"
                                                wire:model.live.debounce.300ms="listSearch"
                                                placeholder="Search categories"
                                                class="w-full border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                            >
                                            <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                                Selected: {{ count($selected_lists) }}
                                            </div>
                                        </div>

                                        <div class="max-h-64 overflow-y-auto">
                                            @forelse ($this->filteredLists as $list)
                                                <label class="flex cursor-pointer items-center gap-3 border-b border-zinc-200 px-3 py-2 text-sm last:border-b-0 dark:border-zinc-800">
                                                    <input
                                                        type="checkbox"
                                                        wire:model.live="selected_lists"
                                                        value="{{ $list->id }}"
                                                        class="h-4 w-4 border border-zinc-300 dark:border-zinc-700"
                                                    >
                                                    <span class="text-zinc-900 dark:text-white">{{ $list->name }}</span>
                                                </label>
                                            @empty
                                                <div class="px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                                    No categories found.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    @error('selected_lists') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    @error('selected_lists.*') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Segment</label>
                                    <select
                                        wire:model.live="bulk_mailer_segment_id"
                                        class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                        <option value="">No segment</option>
                                        @foreach ($this->segments as $segment)
                                            <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bulk_mailer_segment_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">SMTP Group</label>
                                    <select
                                        wire:model.live="bulk_mailer_smtp_group_id"
                                        class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                        <option value="">Select SMTP Group</option>
                                        @foreach ($this->smtpGroups as $smtpGroup)
                                            <option value="{{ $smtpGroup->id }}">{{ $smtpGroup->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bulk_mailer_smtp_group_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Status</label>
                                    <select
                                        wire:model.live="status"
                                        class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                        <option value="draft">Draft</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="paused">Paused</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    @error('status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                                <label class="flex items-center gap-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    <input type="checkbox" wire:model.live="ab_testing_enabled" class="h-4 w-4 border border-zinc-300 dark:border-zinc-700">
                                    <span>Enable A/B Subject Testing</span>
                                </label>
                            </div>

                            @if ($ab_testing_enabled)
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Subject A</label>
                                        <input
                                            type="text"
                                            wire:model.defer="subject_a"
                                            class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                        >
                                        @error('subject_a') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Subject B</label>
                                        <input
                                            type="text"
                                            wire:model.defer="subject_b"
                                            class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                        >
                                        @error('subject_b') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            @else
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Subject</label>
                                    <input
                                        type="text"
                                        wire:model.defer="subject"
                                        class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                    @error('subject') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Schedule Time</label>
                                    <input
                                        type="datetime-local"
                                        wire:model.defer="scheduled_at"
                                        class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                    >
                                    @error('scheduled_at') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Estimated Recipients</div>
                                    <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                        {{ number_format($this->estimatedRecipients) }}
                                    </div>
                                    <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        Count is based on selected categories and optional segment rules.
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

    @if ($showTestModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-center justify-center px-4 py-6">
                <div class="flex max-h-[90vh] w-full max-w-lg flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Send Test Campaign Email</h2>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Test Email Address</label>
                        <input
                            type="email"
                            wire:model.defer="test_email"
                            class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                        >
                        @error('test_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
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
                            wire:click="sendTest"
                            class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            Send Test
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showRescheduleModal)
        <div class="fixed inset-0 z-40 overflow-y-auto bg-black/50">
            <div class="flex min-h-full items-center justify-center px-4 py-6">
                <div class="flex max-h-[90vh] w-full max-w-lg flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Reschedule Campaign</h2>
                    </div>

                    <div class="overflow-y-auto p-6">
                        <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">New Schedule Time</label>
                        <input
                            type="datetime-local"
                            wire:model.defer="reschedule_at"
                            class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                        >
                        @error('reschedule_at') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
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
                            wire:click="reschedule"
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
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Delete Campaign</h2>
                    </div>

                    <div class="p-6 text-sm text-zinc-600 dark:text-zinc-300">
                        Are you sure you want to delete this campaign? This action cannot be undone.
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
</div>