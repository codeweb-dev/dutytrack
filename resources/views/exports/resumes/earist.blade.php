{{-- EARIST Classic: recreates the resume page of the EARIST OJT workbook. --}}
@extends('exports.resumes.layout')

@section('styles')
    @page { margin: 20mm 21mm 18mm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; line-height: 1.3; }
    /* Repeats on every page: the workbook's formal outer border. */
    .frame { position: fixed; top: -9mm; left: -9mm; right: -9mm; bottom: -9mm; border: 1.6pt solid #222; }
    .header td { vertical-align: top; }
    .name { margin: 13mm 0 0 4mm; font-size: 19pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; letter-spacing: 0.3pt; }
    .contact { margin: 7mm 0 0 5mm; font-size: 9.5pt; line-height: 1.55; }
    .contact b { font-weight: bold; }
    .header .photo { width: 2in; height: 2in; border: 1pt solid #333; text-align: center; vertical-align: middle; color: #777; font-size: 9pt; }
    /* Workbook divider: a heavy rule with short ticks down at both ends. */
    .rule { height: 3pt; border: 1.6pt solid #333; border-bottom: none; margin: 7pt 0 2pt; }
    h2 { margin-left: 4mm; font-size: 10.5pt; font-weight: bold; text-transform: uppercase; }
    .body { margin: 1pt 0 0 4mm; }
    .body ul { padding-left: 22pt; }
    .entry { margin-bottom: 3pt; }
    .muted { color: #222; }
    .sign { margin-top: 8pt; border-top: 1pt solid #333; padding-top: 7pt; }
    .certification { margin: 0 6mm; text-align: justify; line-height: 1.4; }
    .sign-box { margin-top: 3mm; margin-right: -4mm; }
    .sign-line { border-top-width: 1.6pt; }
@endsection

@section('content')
    <div class="frame"></div>

    <table class="header" width="100%">
        <tr>
            <td>
                <h1 class="name">{{ $name ?: 'Firstname Lastname' }}</h1>
                <div class="contact">
                    @if ($data['phone'])<div><b>Phone:</b> {{ $data['phone'] }}</div>@endif
                    @if ($data['email'])<div><b>Email:</b> {{ $data['email'] }}</div>@endif
                    @if ($data['address'])<div><b>Address:</b> {{ $data['address'] }}</div>@endif
                </div>
            </td>
            <td style="width: 2in; padding-left: 10pt;">
                @if ($photo)
                    <div style="width: 2in; height: 2in; border: 1pt solid #333; text-align: center;">
                        <img src="{{ $photo['src'] }}" alt="" style="{{ \App\Support\ResumeDocument::fit($photo, 144, 144) }}">
                    </div>
                @else
                    <table><tr><td class="photo">2x2</td></tr></table>
                @endif
            </td>
        </tr>
    </table>

    @include('exports.resumes.sections', [
        'sections' => ['objective', 'technical_skills', 'personal_skills', 'education', 'achievements', 'experience'],
        'inlinePeriod' => true,
    ])

    @include('exports.resumes.signature')
@endsection
