<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="duty-auth min-h-screen antialiased">
    <div class="grid min-h-svh lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.85fr)]">
        <div class="duty-auth-story relative hidden min-h-svh flex-col p-10 lg:flex">
            <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#235444]">
                    <x-app-logo-icon class="h-7 fill-current text-white" />
                </span>
                <span>
                    {{ config('app.name', 'Laravel') }}
                </span>
            </a>
            <div class="relative z-20 mt-auto max-w-xl">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.18em] text-forest">One clear OJT journey</p>
                <h1 class="text-4xl font-semibold leading-tight xl:text-5xl">Placement, attendance, hours, and feedback in one place.</h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-zinc-600">DutyTrack keeps students, coordinators, professors, and company supervisors connected throughout practicum.</p>
            </div>
        </div>
        <div class="flex min-h-svh items-center px-6 py-10 sm:px-10 lg:px-16">
            <div class="duty-auth-panel mx-auto w-full max-w-md space-y-7 rounded-[24px] p-6 sm:p-9">
                <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden"
                    wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#235444]">
                        <x-app-logo-icon class="size-8 fill-current text-white" />
                    </span>

                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
