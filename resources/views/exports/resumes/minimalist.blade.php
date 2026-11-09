{{-- Minimalist: monochrome, centered header, generous whitespace, single ATS-friendly column. --}}
@extends('exports.resumes.layout')

@section('styles')
    @page { margin: 18mm 24mm 18mm; }
    body { font-size: 9.8pt; line-height: 1.4; color: #222; }
    .header { text-align: center; }
    .name { font-family: Times, 'Times New Roman', serif; font-size: 24pt; font-weight: normal; text-transform: uppercase; letter-spacing: 3pt; }
    .contact { margin-top: 6pt; color: #555; font-size: 9pt; letter-spacing: 0.3pt; }
    .photo { margin-bottom: 8pt; }
    .rule { display: none; }
    .section { margin-top: 12pt; }
    h2 { font-size: 8.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2pt; color: #444; border-bottom: 0.5pt solid #aaa; padding-bottom: 3pt; margin-bottom: 6pt; }
    .entry { margin-bottom: 7pt; }
    .muted, .when { color: #666; }
    .sign { margin-top: 16pt; }
    .certification { color: #444; }
    .sign-box { margin-top: 2mm; }
    .sign-line { border-top-width: 0.6pt; font-weight: normal; letter-spacing: 1pt; }
@endsection

@section('content')
    <div class="header">
        @if ($photo)
            <img class="photo" src="{{ $photo['src'] }}" alt="" style="{{ \App\Support\ResumeDocument::fit($photo, 64, 64) }}">
        @endif
        <h1 class="name">{{ $name ?: 'Your Name' }}</h1>
        <div class="contact">
            {{ implode('  ·  ', array_filter([$data['phone'], $data['email'], $data['address']])) }}
        </div>
    </div>

    @include('exports.resumes.sections', [
        'sections' => ['objective', 'technical_skills', 'personal_skills', 'education', 'achievements', 'experience'],
    ])

    @include('exports.resumes.signature')
@endsection
