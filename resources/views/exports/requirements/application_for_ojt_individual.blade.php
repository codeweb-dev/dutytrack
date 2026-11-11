@extends('exports.requirements.layout')

@section('body')
    <p class="right">{!! $v('date') !!}</p>

    <p style="margin-top: 10pt;">
        <span class="bold">{{ Str::upper($d['supervisor_name'] ?? '') }}</span><br>
        {!! $v('supervisor_position') !!}<br>
        {!! $v('company_name') !!}<br>
        {!! $v('company_address') !!}
    </p>

    <p style="margin-top: 10pt;">Sir/Madam:</p>

    <p class="indent">May I respectfully apply to your good office for On-the-Job Training in line with my course,
        <span class="fill">{!! $v('program') !!}</span>. The said OJT will be a <span class="fill">{!! $v('hours', 6) !!}</span>-hour exposure
        in the <span class="fill">{!! $v('setting') !!}</span> setting.</p>

    <p class="indent">The opportunity to undergo training in your company would greatly help me in my future endeavor as a
        <span class="fill">{!! $v('practitioner') !!}</span> Practitioner.</p>

    <p class="indent">Hoping this request merits your favorable action.</p>

    <table class="sign">
        <tr>
            <td></td>
            <td style="text-align: left; padding-left: 20pt;">Respectfully yours,<br><br>
                <span class="bold">{{ Str::upper($d['name'] ?? '') }}</span></td>
        </tr>
    </table>
@endsection
