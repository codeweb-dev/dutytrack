{{-- Corporate: dark header band, strong headings with a side bar, structured dividers. --}}
@extends('exports.resumes.layout')

@section('styles')
    @page { margin: 16mm 18mm 16mm; }
    body { font-size: 9.6pt; color: #1e293b; }
    .band { margin: -16mm -18mm 0; padding: 11mm 18mm 9mm; background: #1e293b; color: #fff; }
    .band td { vertical-align: middle; }
    .name { font-size: 22pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1pt; }
    .tagline { margin-top: 2pt; color: #cbd5e1; font-size: 9.5pt; }
    .contact { margin-top: 7pt; color: #e2e8f0; font-size: 8.8pt; line-height: 1.5; }
    .photo { border: 2pt solid #fff; }
    .rule { display: none; }
    .section { margin-top: 12pt; }
    h2 { font-size: 10pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6pt; color: #1e293b; border-left: 3pt solid #1e293b; padding: 1pt 0 1pt 6pt; margin-bottom: 5pt; background: #f1f5f9; }
    .body { padding: 0 2pt 6pt 9pt; border-bottom: 0.5pt solid #e2e8f0; }
    .entry-title { color: #0f172a; }
    .muted { color: #475569; }
    .sign { margin-top: 16pt; }
    .sign-box { margin-top: 2mm; }
@endsection

@section('content')
    <div class="band">
        <table width="100%">
            <tr>
                <td>
                    <div class="name">{{ $name ?: 'Your Name' }}</div>
                    @if ($education && $education[0]['heading'])
                        <div class="tagline">{{ $education[0]['heading'] }}</div>
                    @endif
                    <div class="contact">
                        {{ implode('   |   ', array_filter([$data['phone'], $data['email']])) }}
                        @if ($data['address'])<br>{{ $data['address'] }}@endif
                    </div>
                </td>
                @if ($photo)
                    <td style="width: 1.3in; text-align: right;">
                        <img class="photo" src="{{ $photo['src'] }}" alt="" style="{{ \App\Support\ResumeDocument::fit($photo, 92, 92) }}">
                    </td>
                @endif
            </tr>
        </table>
    </div>

    @include('exports.resumes.sections', [
        'sections' => ['objective', 'skills', 'education', 'achievements', 'experience'],
    ])

    @include('exports.resumes.signature')
@endsection
