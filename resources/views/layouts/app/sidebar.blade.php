<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('bulk-mailer.dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="space-y-2">
                <div class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                    Main
                </div>

                <flux:sidebar.item icon="home" :href="route('bulk-mailer.dashboard')" :current="request()->routeIs('bulk-mailer.dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="megaphone" :href="route('bulk-mailer.campaigns.index')" :current="request()->routeIs('bulk-mailer.campaigns.*') && !request()->routeIs('bulk-mailer.campaigns.calendar')" wire:navigate>
                    {{ __('Campaigns') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="users" :href="route('bulk-mailer.contacts.index')" :current="request()->routeIs('bulk-mailer.contacts.*')" wire:navigate>
                    {{ __('Contacts') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="document-text" :href="route('bulk-mailer.templates.index')" :current="request()->routeIs('bulk-mailer.templates.*')" wire:navigate>
                    {{ __('Templates') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-bar" :href="route('bulk-mailer.reports.index')" :current="request()->routeIs('bulk-mailer.reports.*')" wire:navigate>
                    {{ __('Reports') }}
                </flux:sidebar.item>

                <div
                    x-data="{ open: {{ request()->routeIs('bulk-mailer.lists.*') || request()->routeIs('bulk-mailer.segments.*') ? 'true' : 'false' }} }"
                    class="border-t border-zinc-200 pt-3 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full cursor-pointer items-center justify-between px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 transition hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    >
                        <span>Audience</span>
                        <span x-text="open ? '−' : '+'"></span>
                    </button>

                    <div x-show="open" x-collapse class="mt-1 space-y-1">
                        <flux:sidebar.item icon="list-bullet" :href="route('bulk-mailer.lists.index')" :current="request()->routeIs('bulk-mailer.lists.*')" wire:navigate>
                            {{ __('Lists') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="funnel" :href="route('bulk-mailer.segments.index')" :current="request()->routeIs('bulk-mailer.segments.*')" wire:navigate>
                            {{ __('Segments') }}
                        </flux:sidebar.item>
                    </div>
                </div>

                <div
                    x-data="{ open: {{ request()->routeIs('bulk-mailer.campaigns.calendar') || request()->routeIs('bulk-mailer.smtp-accounts.*') || request()->routeIs('bulk-mailer.smtp-groups.*') || request()->routeIs('bulk-mailer.smtp-analytics.*') || request()->routeIs('bulk-mailer.operations.*') ? 'true' : 'false' }} }"
                    class="border-t border-zinc-200 pt-3 dark:border-zinc-800"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full cursor-pointer items-center justify-between px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 transition hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    >
                        <span>Advanced</span>
                        <span x-text="open ? '−' : '+'"></span>
                    </button>

                    <div x-show="open" x-collapse class="mt-1 space-y-1">
                        <flux:sidebar.item icon="calendar-days" :href="route('bulk-mailer.campaigns.calendar')" :current="request()->routeIs('bulk-mailer.campaigns.calendar')" wire:navigate>
                            {{ __('Campaign Calendar') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="server-stack" :href="route('bulk-mailer.smtp-accounts.index')" :current="request()->routeIs('bulk-mailer.smtp-accounts.*')" wire:navigate>
                            {{ __('SMTP Accounts') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="squares-2x2" :href="route('bulk-mailer.smtp-groups.index')" :current="request()->routeIs('bulk-mailer.smtp-groups.*')" wire:navigate>
                            {{ __('SMTP Groups') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="chart-bar-square" :href="route('bulk-mailer.smtp-analytics.index')" :current="request()->routeIs('bulk-mailer.smtp-analytics.*')" wire:navigate>
                            {{ __('Sending Analytics') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="cog-6-tooth" :href="route('bulk-mailer.operations.index')" :current="request()->routeIs('bulk-mailer.operations.*')" wire:navigate>
                            {{ __('Queue & Delivery') }}
                        </flux:sidebar.item>
                    </div>
                </div>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>