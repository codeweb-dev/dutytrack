<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-id" content="{{ auth()->id() }}">

<title>
    {{ filled($title ?? null) ? $title . ' | ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

{{-- <link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png"> --}}

<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo.png') }}">
<link rel="shortcut icon" href="{{ asset('logo.png') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<link rel="manifest" href="{{ route('pwa.manifest') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ url('pwa/apple-touch-icon.png') }}">
<meta name="theme-color" content="#ef4444">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="DutyTrack">
<meta name="dutytrack-worker" content="{{ route('pwa.worker') }}">
