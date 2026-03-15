<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="flex items-start justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Template Preview</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $template->name }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('bulk-mailer.templates.index') }}"
                    wire:navigate
                    class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Back
                </a>

                <a
                    href="{{ route('bulk-mailer.templates.index') }}"
                    wire:navigate
                    class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Templates
                </a>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-4">
            <div class="xl:col-span-3 border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="grid gap-4 lg:grid-cols-2 lg:items-start">
                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Subject</div>
                            <div class="mt-2 border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                {{ $this->renderedSubject ?: 'No subject' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Preview Device</div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    wire:click="setDevice('desktop')"
                                    class="border px-3 py-1.5 text-xs {{ $device === 'desktop' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                                >
                                    Desktop
                                </button>

                                <button
                                    type="button"
                                    wire:click="setDevice('tablet')"
                                    class="border px-3 py-1.5 text-xs {{ $device === 'tablet' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                                >
                                    Tablet
                                </button>

                                <button
                                    type="button"
                                    wire:click="setDevice('mobile')"
                                    class="border px-3 py-1.5 text-xs {{ $device === 'mobile' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                                >
                                    Mobile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="setTab('rendered')"
                            class="border px-3 py-1.5 text-xs {{ $tab === 'rendered' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                        >
                            Rendered Preview
                        </button>

                        <button
                            type="button"
                            wire:click="setTab('html')"
                            class="border px-3 py-1.5 text-xs {{ $tab === 'html' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                        >
                            HTML Source
                        </button>

                        <button
                            type="button"
                            wire:click="setTab('text')"
                            class="border px-3 py-1.5 text-xs {{ $tab === 'text' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                        >
                            Text Version
                        </button>
                    </div>
                </div>

                <div class="bg-zinc-100 p-4 dark:bg-zinc-950">
                    @if ($tab === 'rendered')
                        <div class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                            This area simulates how the email content looks on the selected device.
                        </div>

                        <div class="mx-auto {{ $this->previewWidthClass }} border border-zinc-300 bg-white p-6">
                            {!! $this->renderedHtml !!}
                        </div>
                    @elseif ($tab === 'html')
                        <div class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                            Raw HTML template source.
                        </div>

                        <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <pre class="overflow-x-auto whitespace-pre-wrap text-xs text-zinc-800 dark:text-zinc-200">{{ $template->html_content ?: 'No HTML content available.' }}</pre>
                        </div>
                    @else
                        <div class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                            Rendered plain text version with sample values.
                        </div>

                        <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <pre class="overflow-x-auto whitespace-pre-wrap text-xs text-zinc-800 dark:text-zinc-200">{{ $this->renderedText ?: 'No text content available.' }}</pre>
                        </div>
                    @endif
                </div>
            </div>

            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Preview Data</h2>

                <div class="mt-4 space-y-3 text-sm">
                    <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Name</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">Md Jakiul Islam</div>
                    </div>

                    <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Email</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">islamzakiul1@gmail.com</div>
                    </div>

                    <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">First Name</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">Jakiul</div>
                    </div>

                    <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Last Name</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">Islam</div>
                    </div>

                    <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Template Type</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            {{ filled($template->html_content) ? 'HTML + Text' : 'Text Only' }}
                        </div>
                    </div>

                    <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Current Device</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">{{ ucfirst($device) }}</div>
                    </div>

                    <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Current View</div>
                        <div class="mt-1 text-zinc-900 dark:text-white">
                            @if ($tab === 'rendered')
                                Rendered Preview
                            @elseif ($tab === 'html')
                                HTML Source
                            @else
                                Text Version
                            @endif
                        </div>
                    </div>

                <div class="border border-zinc-200 p-3 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
    Available variables:
    <div class="mt-2 space-y-1">
        <div>@{{name}} = Siatex BD LTD</div>
        <div>@{{email}} = </div>
        <div>@{{first_name}} = </div>
        <div>@{{last_name}} = </div>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>
</div>