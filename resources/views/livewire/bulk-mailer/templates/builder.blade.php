<div class="px-4 py-6 lg:px-6">
    <div class="flex flex-col gap-6">
        <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Template Builder</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Choose a preset and copy the generated content into your Templates module.
            </p>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Presets</h2>

                <div class="mt-4 flex flex-col gap-3">
                    <button type="button" wire:click="applyPreset('newsletter')" class="border border-zinc-300 px-4 py-2 text-left text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                        Newsletter
                    </button>

                    <button type="button" wire:click="applyPreset('announcement')" class="border border-zinc-300 px-4 py-2 text-left text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                        Announcement
                    </button>

                    <button type="button" wire:click="applyPreset('promotion')" class="border border-zinc-300 px-4 py-2 text-left text-sm text-zinc-900 hover:bg-zinc-100 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                        Promotion
                    </button>
                </div>
            </div>

            <div class="xl:col-span-2 flex flex-col gap-6">
                <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Generated Subject</h2>
                    <pre class="mt-3 whitespace-pre-wrap border border-zinc-200 p-4 text-sm text-zinc-900 dark:border-zinc-700 dark:text-white">{{ $generated_subject }}</pre>
                </div>

                <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Generated HTML</h2>
                    <pre class="mt-3 whitespace-pre-wrap border border-zinc-200 p-4 text-sm text-zinc-900 dark:border-zinc-700 dark:text-white">{{ $generated_html }}</pre>
                </div>

                <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Preview</h2>
                    <div class="mt-3 border border-zinc-200 p-4 dark:border-zinc-700">
                        {!! $generated_html !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>