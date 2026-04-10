<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />

                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    Dashboard
                </flux:sidebar.item>

                <flux:sidebar.group expandable icon="users" heading="Users Management">
                    <flux:sidebar.item href="{{ route('admin.students') }}" :current="request()->routeIs('admin.students')" wire:navigate>Students OJT Management</flux:sidebar.item>
                    <flux:sidebar.item href="{{ route('admin.supervisors') }}" :current="request()->routeIs('admin.supervisors')" wire:navigate>Supervisors Management</flux:sidebar.item>
                    <flux:sidebar.item href="{{ route('admin.partners') }}" :current="request()->routeIs('admin.partners')" wire:navigate>Professor & Coordinators</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable icon="computer-desktop" heading="Monitoring">
                    <flux:sidebar.item icon="computer-desktop" href="{{ route('admin.attendance') }}" :current="request()->routeIs('admin.attendance')" wire:navigate>
                        Attendance Monitoring
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="calendar-date-range" href="{{ route('admin.duty-hours') }}" :current="request()->routeIs('admin.duty-hours')" wire:navigate>
                        Duty Hours Tracking
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="chart-bar-square" href="{{ route('admin.productivity') }}" :current="request()->routeIs('admin.productivity')" wire:navigate>
                        Productivity Monitoring
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable icon="document-text" heading="Reports & Evaluation">
                    <flux:sidebar.item icon="document-arrow-up" href="{{ route('admin.reports') }}" :current="request()->routeIs('admin.reports')" wire:navigate>
                        Report Generation
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document-magnifying-glass" href="{{ route('admin.evaluation') }}" :current="request()->routeIs('admin.evaluation')" wire:navigate>
                        Evaluation Monitoring
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable icon="cog-6-tooth" heading="System Management">
                    <flux:sidebar.item icon="megaphone" href="{{ route('admin.announcements') }}" :current="request()->routeIs('admin.announcements')" wire:navigate>
                        System Announcements
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="clipboard-document-list" href="{{ route('admin.audit-logs') }}" :current="request()->routeIs('admin.audit-logs')" wire:navigate>
                        Audit Logs
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="cog" href="{{ route('admin.settings') }}" :current="request()->routeIs('admin.settings')" wire:navigate>
                        System Settings
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between mt-1">
                <flux:spacer class="hidden md:block" />

                <flux:sidebar.toggle class="md:hidden" icon="bars-2" inset="left" />

                <div class="flex items-center gap-3">
                    <flux:modal.trigger name="announcement">
                        <flux:button icon="megaphone" variant="subtle" />
                    </flux:modal.trigger>

                    <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />
                    
                    <flux:button icon="cog-6-tooth" variant="subtle" :href="route('profile.edit')" />

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
                    </flux:navbar>
                </div>
            </div>
        </flux:header>

        <flux:modal name="announcement" class="md:max-w-3xl">
            <livewire:announcement />
        </flux:modal>

        {{ $slot }}

        @fluxScripts

        @persist('toast')
            <flux:toast />
        @endpersist
    </body>
</html>
