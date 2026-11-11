@extends('exports.requirements.layout')

@section('body')
    <h1>STUDENT HEALTH RECORD</h1>

    <table class="rows">
        <tr><td class="label">Name of Student</td><td class="colon">:</td><td class="fill">{!! $v('name') !!}</td></tr>
        <tr><td class="label">Student Number</td><td class="colon">:</td><td class="fill">{!! $v('student_number') !!}</td></tr>
        <tr><td class="label">Course / Major</td><td class="colon">:</td><td class="fill">{!! $v('program') !!}</td></tr>
    </table>

    <p style="margin-top: 18pt;">Is the above student in good health? &nbsp; [&nbsp;&nbsp;] Yes &nbsp;&nbsp; [&nbsp;&nbsp;] No</p>
    <p>List physical defects or limitations, which might affect the student’s job performance in the training.</p>
    <p>{{ str_repeat('_', 78) }}<br>{{ str_repeat('_', 78) }}<br>{{ str_repeat('_', 78) }}</p>
    <p>Other Remarks:</p>
    <p>{{ str_repeat('_', 78) }}<br>{{ str_repeat('_', 78) }}</p>

    <table class="sign">
        <tr>
            <td></td>
            <td><div class="line">Medical Officer</div><div class="small" style="margin-top: 10pt;">Date: ____________________</div></td>
        </tr>
    </table>

    <p style="margin-top: 30pt;">Please return this form to:<br>
        <span class="bold">{!! $v('coordinator_name') !!}</span><br>Practicum/Internship Coordinator</p>
@endsection
