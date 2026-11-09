{{--
    Shared resume section markup. Each template picks the sections it shows, in
    order, and styles them with CSS. Empty sections are left out entirely.
    $inlinePeriod: true prints "Title (period)", false floats the period right.
--}}
@php($inlinePeriod ??= false)

@foreach ($sections as $section)
    @switch($section)
        @case('objective')
            @if (filled($data['objective']))
                <div class="section">
                    <div class="rule"></div>
                    <h2>Career Objective</h2>
                    <div class="body">
                        <p>{!! nl2br(e(trim($data['objective']))) !!}</p>
                    </div>
                </div>
            @endif
        @break

        @case('technical_skills')
        @case('personal_skills')
            @php($items = $section === 'technical_skills' ? $technicalSkills : $personalSkills)
            @if ($items)
                <div class="section">
                    <div class="rule"></div>
                    <h2>{{ $section === 'technical_skills' ? 'Technical Skills' : 'Personal Skills' }}</h2>
                    <div class="body">
                        <ul>
                            @foreach ($items as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @break

        @case('skills')
            {{-- Technical and personal skills side by side. dompdf cannot split a table
                 row across pages (it drops the overflow), so long lists are stacked instead. --}}
            @if (! $technicalSkills || ! $personalSkills || max(count($technicalSkills), count($personalSkills)) > 20)
                @include('exports.resumes.sections', ['sections' => ['technical_skills', 'personal_skills']])
            @elseif ($technicalSkills || $personalSkills)
                <table class="skills" width="100%">
                    <tr>
                        <td>@include('exports.resumes.sections', ['sections' => ['technical_skills']])</td>
                        <td>@include('exports.resumes.sections', ['sections' => ['personal_skills']])</td>
                    </tr>
                </table>
            @endif
        @break

        @case('education')
            @if ($education)
                <div class="section">
                    <div class="rule"></div>
                    <h2>Education</h2>
                    <div class="body">
                        @foreach ($education as $item)
                            <div class="entry">
                                <div class="entry-title">
                                    @if (! $inlinePeriod && $item['period'])
                                        <span class="when">{{ $item['period'] }}</span>
                                    @endif
                                    {{ $item['heading'] }}
                                    @if ($inlinePeriod && $item['period'])
                                        ({{ $item['period'] }})
                                    @endif
                                </div>
                                @if ($item['school'])
                                    <div>{{ $item['school'] }}</div>
                                @endif
                                @if ($item['address'])
                                    <div class="muted">{{ $item['address'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @break

        @case('achievements')
            @if ($achievements)
                <div class="section">
                    <div class="rule"></div>
                    <h2>Achievements / Responsibilities / Organizations</h2>
                    <div class="body">
                        <ul>
                            @foreach ($achievements as $item)
                                <li class="entry">
                                    {{ implode(', ', array_filter([$item['organization'], $item['title']])) }}
                                    @if ($item['period'])
                                        ({{ $item['period'] }})
                                    @endif
                                    @if ($item['description'])
                                        <div class="description muted">{!! nl2br(e($item['description'])) !!}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @break

        @case('experience')
            @if ($experience)
                <div class="section">
                    <div class="rule"></div>
                    <h2>Working Experience</h2>
                    <div class="body">
                        @foreach ($experience as $item)
                            <div class="entry">
                                <div class="entry-title">
                                    @if (! $inlinePeriod && $item['period'])
                                        <span class="when">{{ $item['period'] }}</span>
                                    @endif
                                    {{ implode(' - ', array_filter([$item['title'], $item['type']])) }}
                                </div>
                                @if ($item['company'])
                                    <div>{{ $item['company'] }}</div>
                                @endif
                                @if ($inlinePeriod && $item['period'])
                                    <div class="muted">{{ $item['period'] }}</div>
                                @endif
                                @if ($item['description'])
                                    <div class="description"><em>{!! nl2br(e($item['description'])) !!}</em></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @break
    @endswitch
@endforeach
