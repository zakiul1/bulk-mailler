<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-zinc-900 dark:bg-zinc-950 dark:text-white">
    <div class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-10">
        <div class="w-full border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-2xl font-semibold">Unsubscribe</h1>

            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                Campaign: {{ $campaign->name }}
            </p>

            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                Email: {{ $contact->email }}
            </p>

            @if (session('success'))
                <div class="mt-6 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if ($contact->unsubscribed_at)
                <div class="mt-6 border border-zinc-200 p-4 text-sm text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                    This email address is already unsubscribed.
                </div>
            @else
                <div class="mt-6 border border-zinc-200 p-4 text-sm text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                    Click the button below to unsubscribe from future campaign emails.
                </div>

                <form method="POST" action="{{ route('bulk-mailer.public.unsubscribe.store', ['campaign' => $campaign, 'contact' => $contact, 'signature' => request('signature'), 'expires' => request('expires')]) }}" class="mt-6">
                    @csrf

                    <button
                        type="submit"
                        class="border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 dark:border-white dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Unsubscribe
                    </button>
                </form>
            @endif
        </div>
    </div>
</body>
</html>