<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\User;
use Spatie\Permission\Models\Role as PermissionRole;

function createCertificateStudent(int $requiredHours, array $loggedHours): User
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }

    $student = User::factory()->student()->create(['name' => 'Juan Dela Cruz']);
    $student->studentProfile->update(['course' => 'BSIT', 'ojt_required_hours' => $requiredHours]);

    $assignment = OjtAssignment::create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => User::factory()->companySupervisor()->create()->id,
    ]);

    foreach ($loggedHours as $day => $hours) {
        AttendanceLog::create([
            'student_id' => $student->id,
            'ojt_assignment_id' => $assignment->id,
            'attendance_date' => now()->subDays(count($loggedHours) - $day)->toDateString(),
            'hours_rendered' => $hours,
            'status' => 'present',
        ]);
    }

    return $student;
}

test('certificate stays locked until the required hours are rendered', function () {
    $student = createCertificateStudent(16, [8]);

    $this->actingAs($student)->get(route('student.certificate'))
        ->assertOk()
        ->assertSee('Your certificate is locked')
        ->assertDontSee(route('student.certificate.download'));

    $this->get(route('student.certificate.view'))->assertNotFound();
    $this->get(route('student.certificate.download'))->assertNotFound();
});

test('students who completed their hours can view and download the certificate', function () {
    $student = createCertificateStudent(16, [8, 8.5]);

    $this->actingAs($student)->get(route('student.certificate'))
        ->assertOk()
        ->assertSee('Certificate of Completion')
        ->assertSee(route('student.certificate.view'))
        ->assertSee(route('student.certificate.download'));

    $this->get(route('student.certificate.view'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename="OJT-Certificate-juan-dela-cruz.pdf"');

    $this->get(route('student.certificate.download'))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename="OJT-Certificate-juan-dela-cruz.pdf"');
});

test('coordinators can view and download certificates of their own students only', function () {
    $student = createCertificateStudent(16, [8, 8]);
    $coordinator = User::factory()->coordinator()->create();
    $student->update(['coordinator_id' => $coordinator->id]);

    $this->actingAs($coordinator)->get(route('coordinator.certificates'))
        ->assertOk()
        ->assertSee($student->email)
        ->assertSee('Completed')
        ->assertSee(route('coordinator.certificates.view', $student));

    $this->get(route('coordinator.certificates.view', $student))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'inline; filename="OJT-Certificate-juan-dela-cruz.pdf"');
    $this->get(route('coordinator.certificates.download', $student))->assertOk();

    $this->actingAs(User::factory()->coordinator()->create())
        ->get(route('coordinator.certificates.view', $student))
        ->assertNotFound();
});

test('coordinators can get the certificate before the student completes the hours', function () {
    $student = createCertificateStudent(16, [8]);
    $coordinator = User::factory()->coordinator()->create();
    $student->update(['coordinator_id' => $coordinator->id]);

    $this->actingAs($coordinator)->get(route('coordinator.certificates'))
        ->assertOk()
        ->assertSee('In Progress')
        ->assertSee(route('coordinator.certificates.download', $student));

    $this->get(route('coordinator.certificates.view', $student))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $this->get(route('coordinator.certificates.download', $student))->assertOk();

    $this->actingAs($student)->get(route('student.certificate.download'))->assertNotFound();
});
