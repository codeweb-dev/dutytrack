{{-- Modern Professional: accent header band, skills side by side, right-aligned dates. --}}
@extends('exports.resumes.layout')

@section('styles')
    @page { margin: 16mm 17mm 16mm; }
    body { font-size: 9.6pt; color: #1f2933; }
    .band { margin: -16mm -17mm 0; padding: 12mm 17mm 9mm; background: #f0f7f6; border-bottom: 2.5pt solid #0f766e; }
    .band td { vertical-align: middle; }
    .name { font-size: 23pt; font-weight: bold; color: #0b3b37; letter-spacing: 0.2pt; }
    .contact { margin-top: 5pt; color: #3e4c59; font-size: 9pt; line-height: 1.5; }
    .contact span { color: #0f766e; font-weight: bold; }
    .photo { border: 2pt solid #fff; }
    .rule { display: none; }
    .section { margin-top: 11pt; }
    h2 { font-size: 9pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1.2pt; color: #0f766e; padding-bottom: 2.5pt; border-bottom: 0.75pt solid #99c7c1; margin-bottom: 4pt; }
    .entry-title { color: #0b3b37; }
    .muted { color: #52606d; }
    li { margin-bottom: 2pt; }
    .sign { margin-top: 16pt; }
    .certification { color: #3e4c59; font-style: italic; }
    .sign-box { margin-top: 2mm; }
    .sign-line { border-top-color: #0b3b37; color: #0b3b37; }
@endsection

@section('content')
    <div class="band">
        <table width="100%">
            <tr>
                <td>
                    <div class="name">{{ $name ?: 'Your Name' }}</div>
                    <div class="contact">
                        @if ($data['phone'])<div><span>Phone</span>&nbsp; {{ $data['phone'] }}</div>@endif
                        @if ($data['email'])<div><span>Email</span>&nbsp; {{ $data['email'] }}</div>@endif
                        @if ($data['address'])<div><span>Address</span>&nbsp; {{ $data['address'] }}</div>@endif
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
