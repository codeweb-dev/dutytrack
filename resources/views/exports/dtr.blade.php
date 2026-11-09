{{-- Recreates Daily_Time_Record_EARIST_A4_Portrait.docx with one month of the student's attendance. --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Daily Time Record — {{ $name }} — {{ $month }}</title>
    <style>
        @page { margin: 8mm 11.5mm 7.5mm; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 7pt; color: #000; }
        table { border-collapse: collapse; width: 100%; }
        .school { font-size: 8.5pt; font-weight: bold; line-height: 1.3; }
        .title { margin: 2mm 0 2.5mm; text-align: center; font-size: 13pt; font-weight: bold; }
        .info td { padding: 1mm 0; font-size: 8pt; }
        .fill { display: inline-block; min-width: 55mm; border-bottom: 0.75pt solid #000; font-weight: bold; }
        .dtr { margin-top: 2mm; }
        .dtr th, .dtr td { border: 0.75pt solid #000; text-align: center; height: 5.1mm; padding: 0 1mm; }
        .dtr th { font-weight: bold; height: 8mm; }
        .holiday { font-weight: bold; letter-spacing: 2pt; }
        .certify { margin: 4mm 0 0; font-size: 8pt; }
        .line { margin-top: 6mm; border-top: 0.75pt solid #000; }
        .signature { width: 60mm; margin: 7mm 0 0 auto; border-top: 0.75pt solid #000; text-align: center; padding-top: 1mm; font-size: 8pt; }
        .verified { margin-top: 4mm; text-align: center; font-weight: bold; font-size: 8pt; }
    </style>
</head>

<body>
    <table>
        <tr>
            <td style="width: 21mm;"><img src="{{ $logo }}" alt="" style="width: 19mm;"></td>
            <td class="school">
                Republic of the Philippines<br>
                EULOGIO “AMANG” RODRIGUEZ<br>
                INSTITUTE OF SCIENCE AND TECHNOLOGY<br>
                GENERAL MARIANO ALVAREZ, CAVITE
            </td>
        </tr>
    </table>

    <div class="title">DAILY TIME RECORD</div>

    <table class="info">
        <tr>
            <td>Name: <span class="fill">{{ $name }}</span></td>
            <td>Month: <span class="fill">{{ $month }}</span></td>
        </tr>
        <tr>
            <td>Office: <span class="fill">{{ $office }}</span></td>
            <td>Dept.: <span class="fill">{{ $dept }}</span></td>
        </tr>
        <tr>
            <td colspan="2">Official Time: <span class="fill">8:00 AM – 12:00 PM, 1:00 PM – 5:00 PM</span></td>
        </tr>
    </table>

    <table class="dtr">
        <tr>
            <th style="width: 8%;">Day</th>
            <th style="width: 15%;">Arrival</th>
            <th style="width: 15%;">Departure</th>
            <th style="width: 11%;">Tardiness<br>Hours</th>
            <th style="width: 11%;">Tardiness<br>Minutes</th>
            <th style="width: 11%;">Total<br>Hours</th>
            <th>Supervisor’s Signature</th>
        </tr>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['exists'] ? $row['day'] : '' }}</td>
                @if ($row['holiday'])
                    <td colspan="5" class="holiday">HOLIDAY</td>
                @else
                    <td>{{ $row['arrival'] }}</td>
                    <td>{{ $row['departure'] }}</td>
                    <td>{{ $row['late'] ? intdiv($row['late'], 60) : '' }}</td>
                    <td>{{ $row['late'] ? $row['late'] % 60 : '' }}</td>
                    <td>{{ $row['hours'] !== null ? rtrim(rtrim(number_format($row['hours'], 2), '0'), '.') : '' }}</td>
                @endif
                <td></td>
            </tr>
        @endforeach
        <tr>
            <td><strong>Total:</strong></td>
            <td></td>
            <td></td>
            <td>{{ $totalLate ? intdiv($totalLate, 60) : '' }}</td>
            <td>{{ $totalLate ? $totalLate % 60 : '' }}</td>
            <td>{{ $totalHours ? rtrim(rtrim(number_format($totalHours, 2), '0'), '.') : '' }}</td>
            <td></td>
        </tr>
    </table>

    <p class="certify">I CERTIFY on my honour that the above is a true and correct report of the hours work performed, records of which were made daily at the time of arrival and departure from the Office.</p>
    <div class="signature">Signature</div>
    <div class="line"></div>
    <div class="verified">Verified as to the prescribed office hours</div>
    <div class="signature">In-Charge</div>
</body>

</html>
