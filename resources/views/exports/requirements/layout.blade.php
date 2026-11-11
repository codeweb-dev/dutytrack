{{-- Shared page for the generated requirement documents (see App\Support\RequirementForm). --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @include('exports.requirements.styles')
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
