<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body
    class="bg-gray-100 dark:bg-zinc-950 text-gray-900 dark:text-gray-100 overflow-x-hidden min-h-screen transition-colors duration-300">
    <x-landing.navigation />
    <x-landing.hero />
    <x-landing.dashboard-preview />
    <x-landing.benefits />
    <x-landing.footer />

    @fluxScripts
</body>

</html>
