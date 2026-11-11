{{-- Shared page for the generated requirement documents (see App\Support\RequirementForm). --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 11mm 18mm; }
        body { font-family: 'DejaVu Serif', serif; font-size: 10pt; color: #111; line-height: 1.4; }
        p { margin: 0 0 9pt; text-align: justify; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; padding: 0; }
        .header { margin-bottom: 10pt; border-bottom: 1.5pt solid #111; padding-bottom: 6pt; }
        .header td { vertical-align: middle; text-align: center; }
        .small { font-size: 9pt; }
        .bold { font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        h1 { margin: 4pt 0 14pt; text-align: center; font-size: 12.5pt; letter-spacing: 0.4pt; }
        .fill { font-weight: bold; text-decoration: underline; }
        .rows td { padding: 2pt 0; }
        .rows.compact td { padding: 1pt 0; }
        .rows td.label { width: 36%; }
        .rows td.colon { width: 4%; }
        .sign td { width: 50%; padding-top: 24pt; text-align: center; vertical-align: bottom; }
        .sign.tight td { padding-top: 14pt; }
        .line { margin: 0 10pt; border-top: 0.75pt solid #111; padding-top: 2pt; font-size: 9pt; }
        .indent { text-indent: 28pt; }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td style="width: 20mm;"><img src="{{ $logo }}" alt="EARIST" style="width: 16mm; height: 15.6mm;"></td>
            <td>
                <div class="small">Republic of the Philippines</div>
                <div class="bold">EULOGIO “AMANG” RODRIGUEZ</div>
                <div class="bold">INSTITUTE OF SCIENCE AND TECHNOLOGY</div>
                <div class="bold" style="letter-spacing: 3pt;">CAVITE CAMPUS</div>
                <div class="small">General Mariano Alvarez, Cavite</div>
            </td>
            <td style="width: 20mm;"></td>
        </tr>
    </table>

    @yield('body')
</body>

</html>
