@extends('exports.requirements.layout')

@section('body')
    <h1>PARENT’S CERTIFICATION AND WAIVER<br>FOR ON-THE-JOB TRAINING</h1>

    <p>To Whom It May Concern:</p>

    <p class="indent">This is to certify that I am allowing my son/daughter <span class="fill">{!! $v('name') !!}</span>, a
        <span class="fill">{!! $v('year_level', 10) !!}</span> student taking up <span class="fill">{!! $v('program') !!}</span>, to have
        this OJT/Practicum at <span class="fill">{!! $v('company_name') !!}</span>, one of the practicum sites approved and recognized by
        EARIST – Cavite Campus.</p>

    <p class="indent">It is understood that the student will abide by the rules and regulations set by the Practicum Coordinator of the
        course, who is tasked with close monitoring of the practicumer’s performance.</p>

    <p class="indent">While it has been assumed that previous practicumers assigned to the site have safely completed their assigned tasks,
        I fully agree to waive my right to hold EARIST – Cavite Campus and the practicum coordinator responsible for any untoward incident
        that may happen to my son/daughter in the course of fulfilling the required OJT/Practicum.</p>

    @foreach ([['name', 'Student Trainee', 'address', 'contact_number'], ['guardian_name', 'Parent/Guardian', 'guardian_address', 'guardian_contact_number']] as [$signer, $role, $address, $contact])
        <table class="sign">
            <tr>
                <td><span class="bold">{{ Str::upper($d[$signer] ?? '') }}</span><div class="line">Signature of the {{ $role }} over Printed Name</div></td>
                <td>{!! $v('date', 14) !!}<div class="line">Date Signed</div></td>
            </tr>
        </table>
        <p class="small" style="margin: 4pt 0 0 10pt;">Address: {!! $v($address) !!}<br>Contact no.: {!! $v($contact) !!}</p>
    @endforeach
@endsection
