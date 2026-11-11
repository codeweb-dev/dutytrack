@extends('exports.requirements.layout')

@section('body')
    <p>{!! $v('date') !!}</p>

    <p style="margin-top: 10pt;">
        <span class="bold">{{ Str::upper($d['supervisor_name'] ?? '') }}</span><br>
        {!! $v('supervisor_position') !!}<br>
        {!! $v('company_name') !!}<br>
        {!! $v('company_address') !!}
    </p>

    <p style="margin-top: 10pt;">Dear Sir/Madam:</p>

    <p class="indent">This letter will serve as a recommendation for <span class="fill">{!! $v('name') !!}</span>, who is currently enrolled
        in the Practicum/Internship course as one of the requirements of the <span class="fill">{!! $v('program') !!}</span> program of our
        institution. The student is required to complete <span class="fill">{!! $v('hours', 6) !!}</span> hours of on-the-job training related
        to the course.</p>

    <p class="indent"><span class="fill">{!! $v('name') !!}</span> exhibits persistence and dedication in whatever tasks are assigned, and
        I believe the student is worthy to be given a chance to work as a trainee at your esteemed company. As a student, they are
        observed to have good moral character.</p>

    <p class="indent">Kindly afford the student the opportunity to undergo training in your company, which would greatly help in their
        future endeavor as a <span class="fill">{!! $v('practitioner') !!}</span> Practitioner.</p>

    <p class="indent">Thank you very much.</p>

    <table class="sign">
        <tr>
            <td></td>
            <td style="text-align: left; padding-left: 20pt;">Respectfully yours,<br><br>
                <span class="bold">{{ Str::upper($d['coordinator_name'] ?? '') ?: str_repeat('_', 28) }}</span><br>Practicum/Internship Coordinator</td>
        </tr>
    </table>
@endsection
