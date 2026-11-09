<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $name ?: 'Resume' }}</title>
    <style>
        body { margin: 0; font-family: Helvetica, Arial, sans-serif; font-size: 10pt; line-height: 1.35; color: #111; }
        p { margin: 0; }
        h1, h2 { margin: 0; }
        ul { margin: 0; padding: 0 0 0 15pt; }
        li { margin: 0 0 1.5pt; }
        table { border-collapse: collapse; }
        td { padding: 0; vertical-align: top; }
        .rule, h2 { page-break-after: avoid; }
        .entry { page-break-inside: avoid; margin-bottom: 5pt; }
        .entry:last-child { margin-bottom: 0; }
        .entry-title { font-weight: bold; }
        .when { float: right; font-weight: normal; color: #555; padding-left: 8pt; }
        .muted { color: #555; }
        .description { margin-top: 1.5pt; }
        .skills td { width: 50%; padding-right: 12pt; }
        .sign { page-break-inside: avoid; }
        .sign-box { width: 200pt; margin-left: auto; text-align: center; }
        .sign-img { height: 44pt; vertical-align: bottom; text-align: center; }
        .sign-line { border-top: 1pt solid #111; padding-top: 3pt; font-weight: bold; text-transform: uppercase; font-size: 9.5pt; }
        @yield('styles')
    </style>
</head>

<body>
    @yield('content')
</body>

</html>
