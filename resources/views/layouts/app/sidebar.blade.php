<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="duty-app min-h-screen">
    <flux:sidebar sticky collapsible class="duty-sidebar">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />

            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        @php
            $user = auth()->user();
        @endphp

        @if ($user->hasRole('admin'))
            <livewire:admin.sidebar />
        @elseif($user->hasRole('ojt_coordinator'))
            <livewire:coordinator.sidebar />
        @elseif($user->hasRole('professor'))
            <livewire:professor.sidebar />
        @elseif($user->hasRole('company_supervisor'))
            <livewire:supervisor.sidebar />
        @elseif($user->hasRole('student'))
            <livewire:student.sidebar />
        @endif

        <flux:spacer />

        @if ($user->hasRole('student'))
            <div class="in-data-flux-sidebar-collapsed-desktop:hidden">
                <flux:callout icon="fire" color="amber">
                    <flux:callout.heading>Your pet is waiting 🐶</flux:callout.heading>

                    <flux:callout.text>
                        Build your streak by completing daily OJT activities.
                        The longer your streak, the happier and stronger your pet becomes!

                        Don’t miss a day — keep your pet growing.

                        <flux:callout.link :href="route('student.pet-streak')">Begin your streak</flux:callout.link>
                    </flux:callout.text>
                </flux:callout>
            </div>
        @endif

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <flux:header class="duty-topbar block!">
        <div class="flex items-center justify-between gap-4">

            <div class="flex min-w-0 items-center gap-3">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" aria-label="Open navigation" />
                <div class="min-w-0">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-500">DutyTrack workspace</div>
                    <div class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ ucwords(str_replace(['.', '-'], [' / ', ' '], request()->route()?->getName() ?? 'Dashboard')) }}</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <flux:button icon="megaphone" variant="subtle" :href="route('announcements')" aria-label="Announcements" wire:navigate />

                <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle light and dark appearance" />

                <flux:button icon="cog-6-tooth" variant="subtle" :href="route('profile.edit')" aria-label="Settings" wire:navigate />

                <flux:separator vertical class="my-5" />

                <flux:navbar>
                    <flux:spacer />

                    <flux:dropdown position="top" align="start">
                        <flux:profile
                            :avatar="auth()->user()->profile_photo_path ? auth()->user()->profilePhotoUrl() : null"
                            :initials="!auth()->user()->profile_photo_path ? auth()->user()->initials() : null" />

                        <flux:menu>
                            <flux:menu.radio.group>
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                        <flux:avatar :name="auth()->user()->name"
                                            :initials="auth()->user()->initials()" />

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

    @fluxScripts

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
</body>

</html>
