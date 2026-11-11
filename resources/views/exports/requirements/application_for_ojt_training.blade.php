@extends('exports.requirements.layout')

@php($box = fn (bool $checked) => $checked ? '[X]' : '[&nbsp;&nbsp;]')

@section('body')
    <table>
        <tr>
            <td><h1 style="text-align: left; margin: 0 0 8pt;">APPLICATION FOR ON-THE-JOB TRAINING</h1>
                <div class="small">Control No.: ______________</div>
                <div class="small">Student No.: <span class="fill">{!! $v('student_number') !!}</span></div>
                <div class="small">Date of Application: <span class="fill">{!! $v('date') !!}</span></div>
            </td>
            <td style="width: 32mm;">
                <div class="center small" style="border: 0.75pt solid #111; height: 28mm; padding-top: 11mm;">Passport Size</div>
            </td>
        </tr>
    </table>

    <table class="rows compact" style="margin-top: 6pt; font-size: 9.5pt;">
        <tr><td class="label">Student Name</td><td class="colon">:</td>
            <td><span class="fill">{!! $v('surname', 12) !!}</span>, <span class="fill">{!! $v('first_name', 12) !!}</span> <span class="fill">{!! $v('middle_name', 8) !!}</span>
                <div class="small">(Surname, First name, Middle name)</div></td></tr>
        <tr><td class="label">Gender</td><td class="colon">:</td>
            <td>{!! $box(($d['gender'] ?? '') === 'Female') !!} Female &nbsp; {!! $box(($d['gender'] ?? '') === 'Male') !!} Male
                &nbsp;&nbsp;&nbsp; Birthdate: <span class="fill">{!! $v('birthdate', 12) !!}</span></td></tr>
        <tr><td class="label">Course</td><td class="colon">:</td>
            <td><span class="fill">{!! $v('program') !!}</span>@if (filled($d['major'] ?? null)) major in <span class="fill">{{ $d['major'] }}</span>@endif</td></tr>
        <tr><td class="label">Semester / School Year</td><td class="colon">:</td>
            <td>@foreach (['First', 'Second', 'Summer'] as $semester){!! $box(($d['semester'] ?? '') === $semester) !!} {{ $semester }} &nbsp; @endforeach
                &nbsp; S.Y. <span class="fill">{!! $v('school_year', 10) !!}</span></td></tr>
        <tr><td class="label">Contact No. of Trainee</td><td class="colon">:</td><td class="fill">{!! $v('contact_number') !!}</td></tr>
        <tr><td class="label">Email Address</td><td class="colon">:</td><td class="fill">{!! $v('email') !!}</td></tr>
        <tr><td class="label">Address</td><td class="colon">:</td><td class="fill">{!! $v('address') !!}</td></tr>
        <tr><td class="label">Company / Office / Industry</td><td class="colon">:</td><td class="fill">{!! $v('company_name') !!}</td></tr>
        <tr><td class="label">Company Address</td><td class="colon">:</td><td class="fill">{!! $v('company_address') !!}</td></tr>
        <tr><td class="label">Contact No. / Email</td><td class="colon">:</td><td><span class="fill">{!! $v('company_contact', 14) !!}</span> / <span class="fill">{!! $v('company_email', 18) !!}</span></td></tr>
        <tr><td class="label">Contact Person / Position</td><td class="colon">:</td><td><span class="fill">{!! $v('supervisor_name', 18) !!}</span> / <span class="fill">{!! $v('supervisor_position', 14) !!}</span></td></tr>
        <tr><td class="label">Number of Hours Required</td><td class="colon">:</td><td class="fill">{!! $v('hours', 8) !!}</td></tr>
        <tr><td class="label">Start / End of OJT</td><td class="colon">:</td><td><span class="fill">{!! $v('start_date', 14) !!}</span> to <span class="fill">{!! $v('end_date', 14) !!}</span></td></tr>
    </table>

    <p style="margin: 6pt 0 3pt;" class="bold">Documents submitted for evaluation and reference:</p>
    <table class="small">
        @foreach (array_chunk(\App\Support\RequirementForm::DOCUMENTS, 2) as $pair)
            <tr>@foreach ($pair as $document)<td style="width: 50%; padding: 0.5pt 0;">{!! $box(in_array($document, $d['documents'] ?? [], true)) !!} {{ $document }}</td>@endforeach</tr>
        @endforeach
    </table>

    <table class="sign tight" style="font-size: 9.5pt;">
        <tr><td></td><td><span class="bold">{{ Str::upper($d['first_name'] ?? '') }} {{ Str::upper($d['surname'] ?? '') }}</span><div class="line">Signature of Student Trainee</div></td></tr>
    </table>

    <p style="margin: 8pt 0 0; font-size: 9.5pt;"><span class="bold">ACTION TAKEN</span> &nbsp; [&nbsp;&nbsp;] Approved &nbsp; [&nbsp;&nbsp;] Disapproved
        &nbsp;&nbsp; REMARKS: {{ str_repeat('_', 30) }}</p>
    <table class="sign tight" style="font-size: 9.5pt;">
        <tr><td><div class="line">Recommending approval</div></td><td><div class="line">Approved by: OJT Coordinator</div></td></tr>
    </table>
@endsection
