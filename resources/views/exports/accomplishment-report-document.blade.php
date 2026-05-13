@php
    $chunks = $logs->chunk(5)->values();
    $photoChunks = $logs->chunk(4)->values();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accomplishment Report</title>
    <style>
        @page WordSection1 {
            size: 8.5in 11in;
            margin: 0.45in 0.5in;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            margin: 0;
        }

        .WordSection1 {
            page: WordSection1;
            padding: 10pt;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .report-table th,
        .report-table td,
        .photo-table th,
        .photo-table td {
            border: 1px solid #000;
            padding: 4pt 6pt;
            vertical-align: top;
        }

        .week-row th {
            background: #8b8b8b;
            color: #111;
            font-size: 16pt;
            text-align: left;
            padding: 2pt 22pt;
        }

        .header th {
            background: #333;
            color: #fff;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
        }

        .subheader th {
            background: #555;
            color: #fff;
            text-align: center;
            font-size: 12pt;
        }

        .date-cell {
            width: 18%;
            text-align: center;
        }

        .time-cell {
            width: 10%;
            text-align: center;
        }

        .accomplishment-cell {
            width: 28%;
        }

        .hours-cell {
            width: 14%;
            text-align: center;
        }

        .entry-row td {
            height: 54pt;
        }

        .total-label {
            background: #8b8b8b;
            font-size: 18pt;
            font-weight: bold;
        }

        .total-hours {
            background: #222;
            color: #fff;
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
        }

        .superior {
            margin-top: 22pt;
            font-size: 14pt;
            font-weight: bold;
        }

        .page-break {
            page-break-before: always;
        }

        .photo-table th {
            background: #333;
            color: #fff;
            text-align: center;
            font-size: 14pt;
        }

        .photo-date {
            width: 18%;
            text-align: center;
            font-weight: bold;
        }

        .photo-cell {
            width: 82%;
            text-align: center;
        }

        .photo-row td {
            height: 80pt;
        }

        .photo {
            max-width: 130px;
            max-height: 90px;
            margin: 2pt 4pt;
        }
    </style>
</head>
<body>
    <div class="WordSection1">
        @forelse ($chunks as $index => $chunk)
            @if ($index > 0)
                <div class="page-break"></div>
            @endif

            <table class="report-table">
                <tr class="week-row">
                    <th colspan="7">WEEK {{ $index + 1 }}</th>
                </tr>
                <tr class="header">
                    <th rowspan="2" class="date-cell">DATE</th>
                    <th colspan="2">MORNING</th>
                    <th colspan="2">AFTERNOON</th>
                    <th rowspan="2" class="accomplishment-cell">ACCOMPLISHMENT</th>
                    <th rowspan="2" class="hours-cell">TOTAL<br>HOURS</th>
                </tr>
                <tr class="subheader">
                    <th class="time-cell">IN</th>
                    <th class="time-cell">OUT</th>
                    <th class="time-cell">IN</th>
                    <th class="time-cell">OUT</th>
                </tr>

                @foreach ($chunk as $log)
                    <tr class="entry-row">
                        <td class="date-cell">{{ $log->attendance_date?->format('m/d/Y') }}</td>
                        <td class="time-cell">{{ $log->morning_time_in?->format('h:i A') }}</td>
                        <td class="time-cell">{{ $log->morning_time_out?->format('h:i A') }}</td>
                        <td class="time-cell">{{ $log->afternoon_time_in?->format('h:i A') }}</td>
                        <td class="time-cell">{{ $log->afternoon_time_out?->format('h:i A') }}</td>
                        <td class="accomplishment-cell">{{ $log->submission?->accomplishment }}</td>
                        <td class="hours-cell">{{ number_format((float) $log->hours_rendered, 2) }}</td>
                    </tr>
                @endforeach

                @for ($i = $chunk->count(); $i < 5; $i++)
                    <tr class="entry-row">
                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    </tr>
                @endfor

                <tr>
                    <td colspan="5" class="total-label">TOTAL HOURS:</td>
                    <td colspan="2" class="total-hours">{{ number_format($index === $chunks->count() - 1 ? $totalHours : (float) $chunk->sum('hours_rendered'), 2) }} HOURS</td>
                </tr>
            </table>

            <p class="superior">Superior: {{ $supervisorName }}</p>
        @empty
            <p>No completed attendance records found for the selected date range.</p>
        @endforelse

        @foreach ($photoChunks as $index => $chunk)
            <div class="page-break"></div>
            <table class="photo-table">
                <tr class="week-row">
                    <th colspan="2">WEEK {{ $index + 1 }}</th>
                </tr>
                <tr>
                    <th class="photo-date">DATE</th>
                    <th class="photo-cell">Photos</th>
                </tr>

                @foreach ($chunk as $log)
                    <tr class="photo-row">
                        <td class="photo-date">{{ $log->attendance_date?->format('m/d/Y') }}</td>
                        <td class="photo-cell">
                            @foreach (($photoData[$log->id] ?? []) as $photo)
                                <img class="photo" src="{{ $photo }}" alt="Accomplishment photo">
                            @endforeach
                        </td>
                    </tr>
                @endforeach

                @for ($i = $chunk->count(); $i < 4; $i++)
                    <tr class="photo-row">
                        <td></td><td></td>
                    </tr>
                @endfor
            </table>
        @endforeach
    </div>
</body>
</html>
