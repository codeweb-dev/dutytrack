@extends('exports.requirements.layout')

@section('body')
    <h1>OJT RULES AND REGULATIONS</h1>

    <ol style="margin: 0; padding-left: 16pt; font-size: 9.5pt;">
        <li>The students shall secure the OJT application form from their respective OJT Program Coordinators.</li>
        <li>The accomplished OJT application form shall be forwarded to their respective OJT Program Coordinators for evaluation, signature and approval, with the following documents attached:
            <ul style="padding-left: 14pt;">
                <li>Original Comprehensive Resume</li><li>Photocopy of Certificate of Registration</li><li>Photocopy of Evaluation of Grades</li>
                <li>Photocopy of Official Receipt (Student Payee)</li><li>Photocopy of PSA Birth Certificate</li><li>Parent’s Waiver</li><li>Memorandum of Agreement</li>
            </ul>
        </li>
        <li>No student will be allowed to undergo practicum without approval from the Dean.</li>
        <li>OJT rendered without the consent of the institution shall be considered NULL and VOID.</li>
        <li>In case the student fails to complete the required number of hours as prescribed in their course, the student will have to continue the OJT on the succeeding semester and will not be cleared unless proof of completion of OJT is presented.</li>
        <li>The student shall submit the following documents one (1) week after the end of each month for monitoring purposes:
            <ul style="padding-left: 14pt;">
                <li>Photocopy of time card or time sheet signed or duly initialed by the trainee’s immediate superior.</li>
                <li>In the absence of a time card due to the computerization of time in/out, a certification from the company.</li>
                <li>Monthly or daily report</li><li>Narrative report</li>
            </ul>
        </li>
        <li>Prospective companies for OJT chosen by the student must secure formal communication with the Institute before accepting the trainee.</li>
        <li>The OJT Program Coordinator has the prerogative to endorse the student to either in-campus or off-campus training based on the trainee’s capabilities.</li>
        <li>In case the trainee prefers to have the training inside the campus, the trainee shall submit a request letter subject to the approval of the Dean.</li>
        <li>On-the-Job Training abroad is not allowed unless the company or agency furnishes the school with the necessary documents, e.g. Memorandum of Agreement (MOA) and Contract of Apprenticeship.</li>
    </ol>

    <p style="margin-top: 14pt;">I, <span class="fill">{!! $v('name') !!}</span>, a <span class="fill">{!! $v('program') !!}</span> student,
        have read and fully understand the above OJT rules and regulations.</p>

    <p class="bold center" style="margin-top: 8pt;">CONFORMED</p>

    <table class="sign">
        <tr><td><span class="bold">{{ Str::upper($d['name'] ?? '') }}</span><div class="line">Signature of Student-Trainee over Printed Name</div></td>
            <td>{!! $v('date', 14) !!}<div class="line">Date Signed</div></td></tr>
        <tr><td><span class="bold">{{ Str::upper($d['guardian_name'] ?? '') }}</span><div class="line">Signature of Parent/Guardian over Printed Name</div></td>
            <td>{!! $v('date', 14) !!}<div class="line">Date Signed</div></td></tr>
    </table>

    <table class="rows" style="margin-top: 12pt;">
        <tr><td class="label">Address</td><td class="colon">:</td><td class="fill">{!! $v('guardian_address') !!}</td></tr>
        <tr><td class="label">Contact No.</td><td class="colon">:</td><td class="fill">{!! $v('guardian_contact_number') !!}</td></tr>
        <tr><td class="label">Email Address</td><td class="colon">:</td><td class="fill">{!! $v('guardian_email') !!}</td></tr>
    </table>
@endsection
