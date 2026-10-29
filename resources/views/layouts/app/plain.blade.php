<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="duty-app min-h-screen bg-white dark:bg-zinc-800">
    <flux:header class="duty-topbar block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between mt-1">
            <flux:spacer />

            <flux:sidebar.toggle class="md:hidden" icon="bars-2" inset="left" />

            <div class="flex items-center gap-3">
                <flux:button icon="megaphone" variant="subtle" :href="route('announcements')" wire:navigate />

                <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle"
                    aria-label="Toggle dark mode" />

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
        <flux:toast />
    @endpersist
</body>

</html>
