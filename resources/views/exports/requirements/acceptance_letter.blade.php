@extends('exports.requirements.layout')

@section('body')
    <p>{!! $v('date') !!}</p>

    <p style="margin-top: 8pt;">
        <span class="bold">{{ Str::upper($d['coordinator_name'] ?? '') ?: str_repeat('_', 28) }}</span><br>
        Internship/OJT Coordinator<br>EARIST – Cavite Campus<br>GMA, Cavite
    </p>

    <p style="margin-top: 8pt;">Sir/Madam:</p>

    <p class="indent">In reference to your letter dated <span class="fill">{!! $v('letter_date', 16) !!}</span> regarding the On-the-Job Training
        of the student whose name appears below, may I inform you that your invitation for our company to serve as a cooperating agency
        for the student’s <span class="fill">{!! $v('hours', 6) !!}</span> hours On-the-Job Training / Internship is hereby
        <span class="bold">ACCEPTED</span>.</p>

    <p>Stated below are the details of the training.</p>

    <table class="rows">
        @foreach (['name' => 'Name of Trainee', 'program' => 'Course', 'company_name' => 'Cooperating Company', 'company_address' => 'Company’s Address',
            'company_head' => 'Head of the Company', 'department' => 'Department Assigned', 'supervisor_name' => 'Immediate Supervisor',
            'job_description' => 'Trainee’s Job Description', 'office_telephone' => 'Office Telephone', 'training_schedule' => 'Training Schedule',
            'incentives' => 'Incentives (if any)'] as $name => $label)
            <tr><td class="label">{{ $label }}</td><td class="colon">:</td><td class="fill">{!! nl2br($v($name)) !!}</td></tr>
        @endforeach
    </table>

    <p style="margin-top: 8pt;" class="indent">Thank you very much for inviting our office in your Cooperative Education Training Program.</p>

    <table class="sign">
        <tr>
            <td></td>
            <td style="text-align: left; padding-left: 20pt;">Very truly yours,<br><br>
                <span class="bold">{{ Str::upper($d['supervisor_name'] ?? '') ?: str_repeat('_', 28) }}</span><br>Immediate Supervisor</td>
        </tr>
    </table>
@endsection
