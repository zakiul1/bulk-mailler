<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        @if (session('success'))
            <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-start justify-between border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Create Template</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                    Create a reusable email template with tab-based editing and live preview.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('bulk-mailer.templates.index') }}"
                    wire:navigate
                    class="border border-zinc-300 px-4 py-2 text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800"
                >
                    Back
                </a>

                <button
                    type="button"
                    wire:click="save"
                    class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Save
                </button>
            </div>
        </div>

        <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="setEditorTab('details')"
                        class="border px-3 py-1.5 text-xs {{ $editorTab === 'details' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                    >
                        Details
                    </button>

                    <button
                        type="button"
                        wire:click="setEditorTab('html')"
                        class="border px-3 py-1.5 text-xs {{ $editorTab === 'html' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                    >
                        HTML Content
                    </button>

                    <button
                        type="button"
                        wire:click="setEditorTab('text')"
                        class="border px-3 py-1.5 text-xs {{ $editorTab === 'text' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                    >
                        Text Content
                    </button>

                    <button
                        type="button"
                        wire:click="setEditorTab('preview')"
                        class="border px-3 py-1.5 text-xs {{ $editorTab === 'preview' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                    >
                        Live Preview
                    </button>
                </div>
            </div>

            <div class="p-4">
                @if ($editorTab === 'details')
                    <div class="grid gap-4 xl:grid-cols-3">
                        <div class="xl:col-span-2 grid gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Template Name</label>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="name"
                                    class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                >
                                @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Subject</label>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="subject"
                                    class="w-full border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                                >
                                @error('subject') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="border border-zinc-200 p-3 dark:border-zinc-700">
                                <label class="flex items-center gap-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    <input
                                        type="checkbox"
                                        wire:model.live="is_active"
                                        class="h-4 w-4 border border-zinc-300 dark:border-zinc-700"
                                    >
                                    <span>Active</span>
                                </label>
                                @error('is_active') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="border border-zinc-200 bg-white p-4 text-xs text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">Available Variables</div>

                            <div class="mt-3 space-y-1">
                                <div><span class="text-zinc-700 dark:text-zinc-300">@{{name}}</span> = Siatex BD LTD</div>
                                <div><span class="text-zinc-700 dark:text-zinc-300">@{{email}}</span> = info@siatex.com</div>
                                <div><span class="text-zinc-700 dark:text-zinc-300">@{{first_name}}</span> = Siatex</div>
                                <div><span class="text-zinc-700 dark:text-zinc-300">@{{last_name}}</span> = BD LTD</div>
                                <div><span class="text-zinc-700 dark:text-zinc-300">@{{unsubscribe_url}}</span> = #</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($editorTab === 'html')
                    <div class="grid gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">HTML Content</label>
                            <textarea
                                wire:model.live.debounce.300ms="html_content"
                                rows="28"
                                class="w-full border border-zinc-300 px-3 py-2 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                            ></textarea>
                            @error('html_content') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                @if ($editorTab === 'text')
                    <div class="grid gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-900 dark:text-white">Text Content</label>
                            <textarea
                                wire:model.live.debounce.300ms="text_content"
                                rows="24"
                                class="w-full border border-zinc-300 px-3 py-2 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                            ></textarea>
                            @error('text_content') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                @if ($editorTab === 'preview')
                    <div class="grid gap-4">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <div class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">Subject</div>
                                <div class="border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                                    {{ $this->renderedSubject ?: 'No subject' }}
                                </div>
                            </div>

                            <div>
                                <div class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">Preview Device</div>
                                <div class="flex flex-wrap gap-2">
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

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="setPreviewTab('preview')"
                                class="border px-3 py-1.5 text-xs {{ $previewTab === 'preview' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                            >
                                Preview
                            </button>

                            <button
                                type="button"
                                wire:click="setPreviewTab('html')"
                                class="border px-3 py-1.5 text-xs {{ $previewTab === 'html' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                            >
                                HTML
                            </button>

                            <button
                                type="button"
                                wire:click="setPreviewTab('text')"
                                class="border px-3 py-1.5 text-xs {{ $previewTab === 'text' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-300 text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800' }}"
                            >
                                Text
                            </button>
                        </div>

                        <div class="bg-zinc-100 p-4 dark:bg-zinc-950">
                            @if ($previewTab === 'preview')
                                <div class="mx-auto {{ $this->previewWidthClass }} border border-zinc-300 bg-white p-6">
                                    {!! $this->renderedHtml !!}
                                </div>
                            @elseif ($previewTab === 'html')
                                <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                    <pre class="overflow-x-auto whitespace-pre-wrap text-xs text-zinc-800 dark:text-zinc-200">{{ $html_content ?: 'No HTML content yet.' }}</pre>
                                </div>
                            @else
                                <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                    <pre class="overflow-x-auto whitespace-pre-wrap text-xs text-zinc-800 dark:text-zinc-200">{{ $this->renderedText ?: 'No text content yet.' }}</pre>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>