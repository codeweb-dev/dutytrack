<?php

use App\Enums\Role as AppRole;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\StudentTask;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureSupervisorAssignedStudentRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

function createSupervisorAssignedStudentUser(array $attributes = []): User
{
    ensureSupervisorAssignedStudentRolesExist();

    $supervisor = User::factory()->create($attributes);
    $supervisor->assignRole(AppRole::SUPERVISOR->value);

    return $supervisor;
}

function createSupervisorAssignedStudentCoordinator(array $attributes = []): User
{
    ensureSupervisorAssignedStudentRolesExist();

    $coordinator = User::factory()->create($attributes);
    $coordinator->assignRole(AppRole::OJTCOORDINATOR->value);

    return $coordinator;
}

function createSupervisorAssignedStudent(User $coordinator, array $attributes = []): User
{
    ensureSupervisorAssignedStudentRolesExist();

    $sequence = fake()->unique()->numerify('####');

    $student = User::factory()->create(array_merge([
        'name' => "Supervisor Assigned Student {$sequence}",
        'first_name' => "SupervisorAssigned{$sequence}",
        'last_name' => 'Student',
        'email' => "supervisor-assigned-student-{$sequence}@example.com",
        'coordinator_id' => $coordinator->id,
    ], $attributes));

    $student->assignRole(AppRole::STUDENT->value);

    $student->studentProfile()->create([
        'student_id' => "SAS-{$sequence}",
        'course' => 'BSIT',
        'ojt_required_hours' => 600,
    ]);

    return $student;
}

test('supervisors can view only their assigned students', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisorA = createSupervisorAssignedStudentUser(['name' => 'Supervisor Assigned A']);
    $supervisorB = createSupervisorAssignedStudentUser(['name' => 'Supervisor Assigned B']);

    $ownedStudent = createSupervisorAssignedStudent($coordinator, ['name' => 'Owned Supervisor Trainee']);
    $otherStudent = createSupervisorAssignedStudent($coordinator, ['name' => 'Other Supervisor Trainee']);

    OjtAssignment::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisorA->id,
    ]);

    OjtAssignment::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisorB->id,
    ]);

    $this->actingAs($supervisorA)
        ->get(route('supervisor.assigned-students'))
        ->assertOk()
        ->assertSee('Assigned Students')
        ->assertSee($ownedStudent->name)
        ->assertDontSee($otherStudent->name);
});

test('supervisors can assign tasks to their assigned trainees', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisor = createSupervisorAssignedStudentUser(['name' => 'Supervisor Task Owner']);
    $studentA = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Task Student A']);
    $studentB = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Task Student B']);

    foreach ([$studentA, $studentB] as $student) {
        OjtAssignment::query()->create([
            'student_id' => $student->id,
            'ojt_type' => 'external',
            'company_supervisor_id' => $supervisor->id,
        ]);
    }

    $this->actingAs($supervisor);

    $component = Livewire::test('pages::supervisor.tasks')
        ->set('student_ids', [$studentA->id, $studentB->id])
        ->set('task_title', 'Prepare deployment checklist')
        ->set('task_description', 'List completed tasks and blockers before end of shift.')
        ->set('priority', 'high')
        ->set('due_date', '2026-05-15')
        ->call('saveTask')
        ->assertHasNoErrors();

    $task = StudentTask::query()->where('student_id', $studentA->id)->first();

    expect($task)->not->toBeNull();
    expect($task->assigned_by)->toBe($supervisor->id);
    expect($task->title)->toBe('Prepare deployment checklist');
    expect($task->priority)->toBe('high');

    $otherTask = StudentTask::query()->where('student_id', $studentB->id)->where('title', 'Prepare deployment checklist')->first();

    expect($otherTask)->not->toBeNull();
    expect($otherTask->task_group_id)->toBe($task->task_group_id);

    $pendingColumn = collect($component->instance()->displayColumns())->firstWhere('status', 'pending');

    expect($pendingColumn['tasks'])->toHaveCount(1);
    expect($pendingColumn['tasks']->first()['students'])->toHaveCount(2);

    $this->get(route('supervisor.tasks'))
        ->assertOk()
        ->assertSee('Tasks')
        ->assertSee('Prepare deployment checklist');
});

test('supervisors cannot assign tasks to trainees not assigned to them', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisorA = createSupervisorAssignedStudentUser(['name' => 'Supervisor Task A']);
    $supervisorB = createSupervisorAssignedStudentUser(['name' => 'Supervisor Task B']);
    $student = createSupervisorAssignedStudent($coordinator, ['name' => 'Other Supervisor Task Student']);

    OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisorB->id,
    ]);

    $this->actingAs($supervisorA);

    Livewire::test('pages::supervisor.tasks')
        ->set('student_ids', [$student->id])
        ->set('task_title', 'Should not be assigned')
        ->set('priority', 'normal')
        ->call('saveTask')
        ->assertForbidden();

    expect(StudentTask::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('supervisors can drag grouped task cards between columns', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisor = createSupervisorAssignedStudentUser(['name' => 'Supervisor Group Sort']);
    $studentA = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Group Sort A']);
    $studentB = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Group Sort B']);
    $studentC = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Group Sort C']);

    $assignmentA = OjtAssignment::query()->create([
        'student_id' => $studentA->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
    ]);

    $assignmentB = OjtAssignment::query()->create([
        'student_id' => $studentB->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
    ]);

    $assignmentC = OjtAssignment::query()->create([
        'student_id' => $studentC->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
    ]);

    $groupId = (string) fake()->uuid();

    foreach ([[$studentA, $assignmentA], [$studentB, $assignmentB]] as [$student, $assignment]) {
        StudentTask::query()->create([
            'student_id' => $student->id,
            'assigned_by' => $supervisor->id,
            'ojt_assignment_id' => $assignment->id,
            'task_group_id' => $groupId,
            'title' => 'Grouped supervisor task',
            'priority' => 'normal',
            'status' => 'pending',
            'sort_order' => 0,
        ]);
    }

    $existingReviewTask = StudentTask::query()->create([
        'student_id' => $studentC->id,
        'assigned_by' => $supervisor->id,
        'ojt_assignment_id' => $assignmentC->id,
        'title' => 'Existing supervisor review task',
        'priority' => 'normal',
        'status' => 'in_review',
        'sort_order' => 0,
    ]);

    $this->actingAs($supervisor);

    Livewire::test('pages::supervisor.tasks')
        ->call('sortTaskCard', $groupId, 0, 'in_review')
        ->assertHasNoErrors();

    $groupedTasks = StudentTask::query()
        ->where('task_group_id', $groupId)
        ->orderBy('student_id')
        ->get();

    expect($groupedTasks)->toHaveCount(2);
    expect($groupedTasks->pluck('status')->unique()->values()->all())->toBe(['in_review']);
    expect($groupedTasks->pluck('sort_order')->unique()->values()->all())->toBe([0]);
    expect($existingReviewTask->refresh()->sort_order)->toBe(1);
});

test('supervisors can submit practicum evaluations for assigned trainees', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator(['name' => 'Supervisor Evaluation Coordinator']);
    $supervisor = createSupervisorAssignedStudentUser(['name' => 'Supervisor Evaluator']);
    $student = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Evaluation Student']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
    ]);

    $this->actingAs($supervisor);

    Livewire::test('pages::supervisor.evaluations')
        ->call('openEvaluationModal', $assignment->id)
        ->set('inclusive_from', '2026-05-01')
        ->set('inclusive_to', '2026-05-31')
        ->set('evaluation_remarks', 'Shows strong work habits.')
        ->call('saveEvaluation')
        ->assertHasNoErrors();

    $evaluation = Evaluation::query()->first();

    expect($evaluation)->not->toBeNull();
    expect($evaluation->student_id)->toBe($student->id);
    expect($evaluation->evaluator_id)->toBe($supervisor->id);
    expect($evaluation->evaluator_type)->toBe('company_supervisor');
    expect($evaluation->cooperating_program)->toBe('Supervisor Evaluation Coordinator');
    expect((float) $evaluation->rating)->toBe(100.0);
    expect($evaluation->status)->toBe('submitted');
});

test('supervisors can submit multiple practicum evaluations for one assigned trainee', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator(['name' => 'Supervisor Multiple Evaluation Coordinator']);
    $supervisor = createSupervisorAssignedStudentUser(['name' => 'Supervisor Multiple Evaluator']);
    $student = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Multiple Evaluation Student']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
    ]);

    $this->actingAs($supervisor);

    Livewire::test('pages::supervisor.evaluations')
        ->call('openEvaluationModal', $assignment->id)
        ->set('inclusive_from', '2026-05-01')
        ->set('inclusive_to', '2026-05-15')
        ->set('evaluation_remarks', 'First supervisor evaluation period.')
        ->call('saveEvaluation')
        ->call('openEvaluationModal', $assignment->id)
        ->set('inclusive_from', '2026-05-16')
        ->set('inclusive_to', '2026-05-31')
        ->set('evaluation_remarks', 'Second supervisor evaluation period.')
        ->call('saveEvaluation')
        ->assertHasNoErrors();

    $evaluations = Evaluation::query()
        ->where('student_id', $student->id)
        ->where('ojt_assignment_id', $assignment->id)
        ->where('evaluator_id', $supervisor->id)
        ->orderBy('inclusive_from')
        ->get();

    expect($evaluations)->toHaveCount(2);
    expect($evaluations->pluck('remarks')->all())->toBe([
        'First supervisor evaluation period.',
        'Second supervisor evaluation period.',
    ]);
});

test('supervisors can sort assigned students by student name', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisor = createSupervisorAssignedStudentUser();
    $zed = createSupervisorAssignedStudent($coordinator, ['name' => 'Zed Sorted Trainee']);
    $abe = createSupervisorAssignedStudent($coordinator, ['name' => 'Abe Sorted Trainee']);

    foreach ([$abe, $zed] as $student) {
        OjtAssignment::query()->create([
            'student_id' => $student->id,
            'ojt_type' => 'external',
            'company_supervisor_id' => $supervisor->id,
        ]);
    }

    $this->actingAs($supervisor);

    Livewire::test('pages::supervisor.assigned-students')
        ->set('sortBy', 'student')
        ->assertSeeInOrder([$abe->name, $zed->name])
        ->set('sortDirection', 'desc')
        ->assertSeeInOrder([$zed->name, $abe->name])
        ->call('clearFilters')
        ->assertSet('sortBy', '')
        ->assertSet('sortDirection', 'asc');
});
