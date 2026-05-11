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
    $professor = User::factory()->professor()->create(['name' => 'Evaluation Professor']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'status' => 'active',
    ]);

    $evaluation = Evaluation::query()->create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'evaluator_id' => $professor->id,
        'evaluator_type' => 'professor',
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
        ->assertSee('Evaluation Professor')
        ->assertSee('Student Evaluation Coordinator')
        ->assertSee('100.0');

    $this->actingAs($student)
        ->get(route('student.evaluations.download', $evaluation))
        ->assertOk()
        ->assertHeader('content-disposition');
});
