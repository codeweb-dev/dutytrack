<?php

use App\Enums\Role as AppRole;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\User;
use App\Support\PracticumEvaluationForm;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureStudentEvaluationRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

test('students can view and download their approved practicum evaluations', function () {
    ensureStudentEvaluationRolesExist();

    $coordinator = User::factory()->coordinator()->create(['name' => 'Student Evaluation Coordinator']);
    $student = User::factory()->student()->create([
        'name' => 'Evaluated Student',
        'coordinator_id' => $coordinator->id,
    ]);
    $mentor = User::factory()->companySupervisor()->create(['name' => 'Evaluation Mentor']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $evaluation = Evaluation::query()->create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'evaluator_id' => $mentor->id,
        'evaluator_type' => 'company_supervisor',
        'evaluation_date' => '2026-05-11',
        'inclusive_from' => '2026-05-01',
        'inclusive_to' => '2026-05-31',
        'cooperating_program' => 'Student Evaluation Coordinator',
        'ratings' => PracticumEvaluationForm::defaultRatings(),
        'rating' => 100,
        'remarks' => 'Excellent practicum performance.',
        'status' => 'reviewed',
    ]);

    $this->actingAs($student)
        ->get(route('student.evaluations'))
        ->assertOk()
        ->assertSee(route('student.evaluations.view', $evaluation), false)
        ->assertSee(route('student.evaluations.download', $evaluation), false)
        ->assertSee('Download Evaluation');

    $this->actingAs($student)
        ->get(route('student.evaluations.view', $evaluation))
        ->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8')
        ->assertSee('Excellent practicum performance.');

    $this->actingAs($student)
        ->get(route('student.evaluations.download', $evaluation))
        ->assertOk()
        ->assertHeader('content-type', 'application/msword; charset=UTF-8')
        ->assertHeader('content-disposition', 'attachment; filename="practicum-evaluation-'.$evaluation->id.'.doc"')
        ->assertSee('(Practicum-Student Evaluation Form)', false)
        ->assertSee('data:image/jpeg;base64', false)
        ->assertSee('EULOGIO &ldquo;AMANG&rdquo; RODRIGUEZ', false);
});

test('students cannot see an evaluation until the coordinator approves it', function () {
    ensureStudentEvaluationRolesExist();

    $student = User::factory()->student()->create();
    $mentor = User::factory()->companySupervisor()->create();

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $evaluation = Evaluation::query()->create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'evaluator_id' => $mentor->id,
        'evaluator_type' => 'company_supervisor',
        'evaluation_date' => '2026-05-11',
        'ratings' => PracticumEvaluationForm::defaultRatings(),
        'rating' => 84,
        'status' => 'submitted',
    ]);

    $this->actingAs($student)
        ->get(route('student.evaluations'))
        ->assertOk()
        ->assertSee('No evaluation yet')
        ->assertDontSee(route('student.evaluations.view', $evaluation), false);

    $this->actingAs($student)->get(route('student.evaluations.view', $evaluation))->assertForbidden();
    $this->actingAs($student)->get(route('student.evaluations.download', $evaluation))->assertForbidden();
});

test('students still see their evaluation after the coordinator reviews it', function () {
    ensureStudentEvaluationRolesExist();

    $student = User::factory()->student()->create();
    $mentor = User::factory()->companySupervisor()->create();

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $evaluation = Evaluation::query()->create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'evaluator_id' => $mentor->id,
        'evaluator_type' => 'company_supervisor',
        'evaluation_date' => '2026-05-11',
        'ratings' => PracticumEvaluationForm::defaultRatings(),
        'rating' => 84,
        'status' => 'reviewed',
    ]);

    $this->actingAs($student)
        ->get(route('student.evaluations'))
        ->assertOk()
        ->assertSee(route('student.evaluations.view', $evaluation), false)
        ->assertDontSee('No evaluation yet');
});

test('supervisors can view their evaluation in the browser and others cannot', function () {
    ensureStudentEvaluationRolesExist();

    $student = User::factory()->student()->create();
    $mentor = User::factory()->companySupervisor()->create();
    $otherMentor = User::factory()->companySupervisor()->create();

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $evaluation = Evaluation::query()->create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'evaluator_id' => $mentor->id,
        'evaluator_type' => 'company_supervisor',
        'evaluation_date' => '2026-05-11',
        'ratings' => PracticumEvaluationForm::defaultRatings(),
        'rating' => 80,
        'remarks' => 'Very dependable and eager to learn.',
        'status' => 'submitted',
    ]);

    $this->actingAs($mentor)
        ->get(route('supervisor.evaluations.view', $evaluation))
        ->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8')
        ->assertHeaderMissing('content-disposition')
        ->assertSee('Very dependable and eager to learn.');

    $this->actingAs($otherMentor)
        ->get(route('supervisor.evaluations.view', $evaluation))
        ->assertForbidden();
});
