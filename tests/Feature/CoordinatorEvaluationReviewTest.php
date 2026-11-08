<?php

use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\User;
use App\Support\PracticumEvaluationForm;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

function createEvaluationReviewFixture(User $coordinator): Evaluation
{
    $student = User::factory()->student()->create(['coordinator_id' => $coordinator->id]);
    $supervisor = User::factory()->companySupervisor()->create();
    $assignment = OjtAssignment::create([
        'student_id' => $student->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
        'status' => 'active',
    ]);

    return Evaluation::create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'evaluator_id' => $supervisor->id,
        'evaluator_type' => 'company_supervisor',
        'evaluation_date' => today(),
        'inclusive_from' => today()->subMonth(),
        'inclusive_to' => today(),
        'rating' => 80,
        'ratings' => PracticumEvaluationForm::defaultRatings(4),
        'status' => 'submitted',
    ]);
}

test('coordinator can review a supervisor evaluation for their student', function () {
    $this->seed(Database\Seeders\RoleSeeder::class);
    $coordinator = User::factory()->coordinator()->create();
    $evaluation = createEvaluationReviewFixture($coordinator);

    Livewire::actingAs($coordinator)
        ->test('pages::coordinator.evaluations')
        ->assertSee($evaluation->student->display_name)
        ->assertSee($evaluation->evaluator->display_name)
        ->call('markReviewed', $evaluation->id)
        ->assertHasNoErrors();

    expect($evaluation->fresh()->status)->toBe('reviewed');
    expect(AuditLog::query()->where('target_id', $evaluation->id)->where('action', 'reviewed')->exists())->toBeTrue();
});

test('coordinator cannot review another coordinator’s student evaluation', function () {
    $this->seed(Database\Seeders\RoleSeeder::class);
    $owner = User::factory()->coordinator()->create();
    $otherCoordinator = User::factory()->coordinator()->create();
    $evaluation = createEvaluationReviewFixture($owner);

    Livewire::actingAs($otherCoordinator)
        ->test('pages::coordinator.evaluations')
        ->assertDontSee($evaluation->student->display_name);

    expect(fn () => Livewire::actingAs($otherCoordinator)
        ->test('pages::coordinator.evaluations')
        ->call('markReviewed', $evaluation->id))
        ->toThrow(ModelNotFoundException::class);

    expect($evaluation->fresh()->status)->toBe('submitted');
});
