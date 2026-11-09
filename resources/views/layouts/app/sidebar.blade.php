<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="duty-app min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible
        class="duty-sidebar border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />

            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        @php
            $user = auth()->user();
        @endphp

        @if ($user->hasRole('ojt_coordinator'))
            <livewire:coordinator.sidebar />
        @elseif($user->hasRole('supervisor'))
            <livewire:supervisor.sidebar />
        @elseif($user->hasRole('student'))
            <livewire:student.sidebar />
        @endif

        <flux:spacer />

        @if ($user->hasRole('student') && ! request()->routeIs('student.pomodoro'))
            <div x-data x-cloak x-show="$store.pomodoro.isActive"
                class="rounded-xl border border-zinc-200 bg-white p-3 in-data-flux-sidebar-collapsed-desktop:hidden dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('student.pomodoro') }}" wire:navigate class="min-w-0">
                        <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="size-2 rounded-full" x-bind:class="$store.pomodoro.activePreset.dot"></span>
                            <span x-text="$store.pomodoro.activeLabel + ($store.pomodoro.isRunning ? '' : ' · Paused')"></span>
                        </div>
                        <div class="mt-1 text-2xl font-semibold tracking-tight tabular-nums text-zinc-950 dark:text-white"
                            x-text="$store.pomodoro.formattedTime"></div>
                    </a>

                    <flux:button x-on:click="$store.pomodoro.toggleTimer()" variant="primary" size="sm" square
                        x-bind:aria-label="$store.pomodoro.isRunning ? 'Pause timer' : 'Resume timer'">
                        <flux:icon name="play" x-show="!$store.pomodoro.isRunning" class="size-4" />
                        <flux:icon name="pause" x-show="$store.pomodoro.isRunning" class="size-4" />
                    </flux:button>
                </div>

                <div class="mt-3 h-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                    <div class="h-full rounded-full transition-all duration-500" x-bind:class="$store.pomodoro.activePreset.dot"
                        x-bind:style="`width: ${$store.pomodoro.progress * 100}%`"></div>
                </div>
            </div>
        @endif

        <flux:sidebar.nav>
            <flux:sidebar.item icon="information-circle" x-data x-on:click="$dispatch('onboarding-replay')">
                {{ __('Help') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->display_name" />
    </flux:sidebar>

    <flux:header
        class="duty-topbar block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between gap-4">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" aria-label="Open navigation"
                data-tour="mobile-navigation-toggle" />

            <div class="ml-auto flex items-center gap-3">
                <flux:button icon="megaphone" variant="subtle" :href="route('announcements')" aria-label="Announcements"
                    wire:navigate data-tour="topbar-announcements" />

                <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle"
                    aria-label="Toggle dark mode" />

                <flux:button icon="cog-6-tooth" variant="subtle" :href="route('profile.edit')" aria-label="Settings"
                    wire:navigate data-tour="account-settings" />

                <flux:navbar>
                    <flux:spacer />

                    <flux:dropdown position="top" align="start">
                        <flux:profile
                            :avatar="auth()->user()->profile_photo_path ? auth()->user()->profilePhotoUrl() : null"
                            :initials="!auth()->user()->profile_photo_path ? auth()->user()->initials() : null"
                            data-tour="profile-menu" />

                        <flux:menu>
                            <flux:menu.radio.group>
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                        <flux:avatar :name="auth()->user()->display_name"
                                            :src="auth()->user()->profile_photo_path ? auth()->user()->profilePhotoUrl() : null"
                                            :initials="auth()->user()->initials()" />

                                        <div class="grid flex-1 text-start text-sm leading-tight">
                                            <flux:heading class="truncate">{{ auth()->user()->display_name }}
                                            </flux:heading>
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
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                                    class="w-full cursor-pointer" data-test="logout-button">
                                    {{ __('Log out') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </flux:navbar>
            </div>
        </div>
    </flux:header>
    {{ $slot }}

    <livewire:onboarding-tour />

    @fluxScripts

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
</body>

</html>
