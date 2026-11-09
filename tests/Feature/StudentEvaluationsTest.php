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

test('students can view and download their submitted practicum evaluations', function () {
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
        'status' => 'submitted',
    ]);

    $this->actingAs($student)
        ->get(route('student.evaluations'))
        ->assertOk()
        ->assertSee('My Evaluation Records')
        ->assertSee('Evaluation Mentor')
        ->assertSee('Student Evaluation Coordinator')
        ->assertSee('100.0')
        ->assertSee('Docs');

    $this->actingAs($student)
        ->get(route('student.evaluations.download', $evaluation))
        ->assertOk()
        ->assertHeader('content-type', 'application/msword; charset=UTF-8')
        ->assertHeader('content-disposition', 'attachment; filename="practicum-evaluation-'.$evaluation->id.'.doc"')
        ->assertSee('(Practicum-Student Evaluation Form)', false)
        ->assertSee('data:image/jpeg;base64', false)
        ->assertSee('EULOGIO &ldquo;AMANG&rdquo; RODRIGUEZ', false);
});
