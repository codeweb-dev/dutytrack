{{-- Recreates Narrative-Report.doc from the student's draft (see App\Support\NarrativeReport). --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Narrative Report — {{ $d['name'] }}</title>
    @include('exports.requirements.styles')
    <style>
        @page { margin: 16mm 20mm 20mm; }
        body { font-size: 10.5pt; line-height: 1.55; }
        .page { page-break-before: always; }
        h2 { margin: 0 0 14pt; text-align: center; font-size: 12.5pt; letter-spacing: 0.4pt; }
        h3 { margin: 16pt 0 8pt; font-size: 11pt; }
        .cover { padding-top: 22mm; text-align: center; line-height: 1.7; }
        .cover .title { font-size: 13pt; font-weight: bold; }
        .cover .gap { height: 16mm; }
        .toc td { padding: 2.5pt 0; vertical-align: bottom; }
        .toc .group { padding-top: 10pt; font-weight: bold; }
        .toc .item { padding-left: 16pt; }
        .toc .num { width: 12mm; text-align: right; }
        .letterhead { margin-bottom: 18pt; border: 0.75pt dashed #888; padding: 8pt; text-align: center; color: #666; font-size: 9pt; }
        .figure { text-align: center; }
        .figure img { max-width: 100%; max-height: 215mm; }
        .photos td { width: 50%; padding: 0 4pt 10pt; text-align: center; vertical-align: top; }
        .photos img { width: 76mm; }
        .caption { font-size: 8.5pt; color: #444; }
        .dtr { font-size: 8pt; line-height: 1.15; }
        .dtr th, .dtr td { border: 0.75pt solid #111; padding: 1.5pt 4pt; text-align: center; }
        .appendix-letter { font-size: 10pt; line-height: 1.4; }
    </style>
</head>

@php
    $company = Str::upper($d['company_name'] ?: str_repeat('_', 20));
    $sections = collect(\App\Support\NarrativeReport::SECTIONS)->filter(fn ($section, $key) => filled($d[$key]));
    $companySections = $sections->only(['vision_mission', 'corporate_profile', 'history', 'products_services']);
    $proper = $sections->except(['acknowledgment', ...$companySections->keys()]);

    $contents = [
        'PRELIMINARIES' => ['toc-approval' => 'Approval Sheet', 'toc-acknowledgment' => 'Acknowledgment', 'toc-certification' => 'Certification of On-the-Job Training'],
        'NARRATIVE REPORT PROPER' => collect()
            ->merge($proper->only(['introduction', 'course_syllabus'])->mapWithKeys(fn ($s, $k) => ["toc-$k" => $s['label']]))
            ->merge($companySections->mapWithKeys(fn ($s, $k) => ["toc-$k" => 'The Company: '.$s['label']]))
            ->merge($images['org_chart'] ? ['toc-org_chart' => 'The Company: Organizational Chart'] : [])
            ->merge($images['sketch'] ? ['toc-sketch' => 'The Company: Sketch'] : [])
            ->merge($proper->except(['introduction', 'course_syllabus'])->mapWithKeys(fn ($s, $k) => ["toc-$k" => $s['label']]))
            ->merge($photos ? ['toc-photographs' => 'Photographs'] : [])
            ->all(),
        'APPENDICES' => collect($letters)->keys()->mapWithKeys(fn ($label, $i) => ["toc-letter-$i" => $label])
            ->merge($dtr ? ['toc-dtr' => 'Daily Time Record'] : [])
            ->all(),
    ];
@endphp

<body>
    {{-- Title page --}}
    <div class="cover">
        <img src="{{ $logo }}" alt="EARIST" style="width: 22mm;">
        <div class="gap"></div>
        <div class="title">A NARRATIVE REPORT OF ON-THE-JOB TRAINING EXPERIENCES<br>IN {{ $company }},<br>{{ Str::upper($d['company_location']) }}</div>
        <div class="gap"></div>
        <div>Submitted to</div>
        <div class="bold">{{ Str::upper($d['coordinator_name']) ?: str_repeat('_', 28) }}</div>
        <div>Practicum/Internship Coordinator</div>
        <div>Eulogio “Amang” Rodriguez Institute of Science and Technology</div>
        <div>Cavite Campus</div>
        <div class="gap"></div>
        <div>In Partial Fulfillment<br>of the Requirements in {{ $d['course_code'] ?: str_repeat('_', 10) }}<br>(Practicum / Internship)</div>
        <div class="gap"></div>
        <div>Submitted by</div>
        <div class="bold">{{ Str::upper($d['name']) }}</div>
        <div>{{ $d['program'] }}</div>
        <div>{{ $d['semester'] }}</div>
        <div>School Year {{ $d['school_year'] }}</div>
    </div>

    {{-- Approval sheet --}}
    <div class="page" id="toc-approval">
        <h2>APPROVAL SHEET</h2>
        <p class="indent">This report entitled <span class="bold">“A NARRATIVE REPORT OF ON-THE-JOB TRAINING EXPERIENCES IN {{ $company }}”</span>, which is submitted by:</p>
        <p class="center bold" style="margin: 14pt 0;">{{ Str::upper($d['name']) }}</p>
        <p>in partial fulfillment of the requirements of the course {{ $d['course_code'] ?: str_repeat('_', 10) }} (Practicum / Internship {{ $d['hours'] ?: '___' }} hours OJT), is hereby approved.</p>
        <table class="sign">
            <tr>
                <td></td>
                <td><span class="bold">{{ Str::upper($d['coordinator_name']) }}</span><div class="line">Practicum/Internship Coordinator</div></td>
            </tr>
        </table>
        <p style="margin-top: 24pt;">Rating: ______________<br>Date: ______________</p>
    </div>

    {{-- Acknowledgment --}}
    <div class="page" id="toc-acknowledgment">
        <h2>ACKNOWLEDGMENT</h2>
        {!! $text($d['acknowledgment']) !!}
        <p class="right bold" style="margin-top: 18pt;">{{ Str::upper($d['name']) }}</p>
    </div>

    {{-- Certification, printed on the company's letterhead --}}
    <div class="page" id="toc-certification">
        <div class="letterhead">(OJT Company Letterhead)</div>
        <h2>CERTIFICATION OF ON-THE-JOB TRAINING</h2>
        <p class="indent">This is to certify that all the activities and experiences contained in this narrative report were undertaken by
            <span class="bold">{{ Str::upper($d['name']) }}</span>, a {{ $d['program'] }} intern of the EARIST – Cavite Campus, GMA, Cavite,
            during the {{ Str::lower($d['semester']) }} of School Year {{ $d['school_year'] }}, under my guidance and supervision.</p>
        <table class="sign">
            <tr>
                <td><span class="bold">{{ Str::upper($d['supervisor_name']) }}</span><div class="line">{{ $d['supervisor_position'] ?: 'Immediate Supervisor' }}</div></td>
                <td><span class="bold">{{ Str::upper($d['manager_name']) }}</span><div class="line">{{ $d['manager_position'] ?: 'Manager' }}</div></td>
            </tr>
        </table>
    </div>

    {{-- Table of contents --}}
    <div class="page">
        <h2>TABLE OF CONTENTS</h2>
        <table class="toc">
            <tr><td></td><td class="num bold">Page</td></tr>
            @foreach ($contents as $group => $items)
                @continue(! $items)
                <tr><td class="group" colspan="2">{{ $group }}</td></tr>
                @foreach ($items as $id => $label)
                    <tr><td class="item">{{ $label }}</td><td class="num">{{ $pages[$id] ?? '' }}</td></tr>
                @endforeach
            @endforeach
        </table>
    </div>

    {{-- Report proper --}}
    @foreach ($proper->only(['introduction', 'course_syllabus']) as $key => $section)
        <div class="page" id="toc-{{ $key }}">
            <h2>{{ Str::upper($section['label']) }}</h2>
            {!! $text($d[$key]) !!}
        </div>
    @endforeach

    @if ($companySections->isNotEmpty() || $images['org_chart'] || $images['sketch'])
        <div class="page">
            <h2>THE COMPANY</h2>
            @foreach ($companySections as $key => $section)
                <h3 id="toc-{{ $key }}">{{ $section['label'] }}</h3>
                {!! $text($d[$key]) !!}
            @endforeach
        </div>

        @foreach (['org_chart' => 'ORGANIZATIONAL CHART', 'sketch' => 'SKETCH'] as $type => $heading)
            @if ($images[$type])
                <div class="page figure" id="toc-{{ $type }}">
                    <h2>{{ $heading }}</h2>
                    <img src="{{ $images[$type] }}" alt="{{ $heading }}">
                </div>
            @endif
        @endforeach
    @endif

    @foreach ($proper->except(['introduction', 'course_syllabus']) as $key => $section)
        <div class="page" id="toc-{{ $key }}">
            <h2>{{ Str::upper($section['label']) }}</h2>
            {!! $text($d[$key]) !!}
        </div>
    @endforeach

    @if ($photos)
        <div class="page" id="toc-photographs">
            <h2>PHOTOGRAPHS</h2>
            <table class="photos">
                @foreach (array_chunk($photos, 2) as $pair)
                    <tr>
                        @foreach ($pair as $photo)
                            <td><img src="{{ $photo['src'] }}" alt=""><div class="caption">{{ $photo['caption'] }}</div></td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Appendices --}}
    @foreach ($letters as $label => $html)
        <div class="page appendix-letter" id="toc-letter-{{ $loop->index }}">{!! $html !!}</div>
    @endforeach

    @foreach ($dtr as $month)
        <div class="page" @if ($loop->first) id="toc-dtr" @endif>
            <h2>DAILY TIME RECORD</h2>
            <table class="rows" style="margin-bottom: 8pt;">
                <tr><td>Name: <span class="bold">{{ $month['name'] }}</span></td><td>Month: <span class="bold">{{ $month['month'] }}</span></td></tr>
                <tr><td>Office: <span class="bold">{{ $month['office'] }}</span></td><td>Total hours: <span class="bold">{{ \App\Support\HourFormatter::format($month['totalHours']) }}</span></td></tr>
            </table>
            <table class="dtr">
                <tr><th style="width: 12%;">Day</th><th>Arrival</th><th>Departure</th><th style="width: 18%;">Hours</th></tr>
                @foreach ($month['rows'] as $row)
                    @continue(! $row['exists'])
                    <tr>
                        <td>{{ $row['day'] }}</td>
                        @if ($row['holiday'])
                            <td colspan="3" class="bold">HOLIDAY</td>
                        @else
                            <td>{{ $row['arrival'] }}</td>
                            <td>{{ $row['departure'] }}</td>
                            <td>{{ $row['hours'] !== null ? rtrim(rtrim(number_format($row['hours'], 2), '0'), '.') : '' }}</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
</body>

</html>
