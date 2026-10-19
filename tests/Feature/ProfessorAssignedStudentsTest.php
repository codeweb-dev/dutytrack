<?php

use App\Enums\Role as AppRole;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\StudentTask;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureProfessorAssignedStudentRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

function createProfessorAssignedStudentUser(array $attributes = []): User
{
    ensureProfessorAssignedStudentRolesExist();

    $professor = User::factory()->create($attributes);
    $professor->assignRole(AppRole::PROFESSOR->value);

    return $professor;
}

function createProfessorAssignedStudentCoordinator(array $attributes = []): User
{
    ensureProfessorAssignedStudentRolesExist();

    $coordinator = User::factory()->create($attributes);
    $coordinator->assignRole(AppRole::OJTCOORDINATOR->value);

    return $coordinator;
}

function createProfessorAssignedStudent(User $coordinator, array $attributes = []): User
{
    ensureProfessorAssignedStudentRolesExist();

    $sequence = fake()->unique()->numerify('####');

    $student = User::factory()->create(array_merge([
        'name' => "Professor Assigned Student {$sequence}",
        'first_name' => "ProfessorAssigned{$sequence}",
        'last_name' => 'Student',
        'email' => "professor-assigned-student-{$sequence}@example.com",
        'coordinator_id' => $coordinator->id,
    ], $attributes));

    $student->assignRole(AppRole::STUDENT->value);

    $student->studentProfile()->create([
        'student_id' => "PAS-{$sequence}",
        'course' => 'BSIT',
        'ojt_required_hours' => 600,
    ]);

    return $student;
}

test('professors can view only their assigned students', function () {
    $coordinator = createProfessorAssignedStudentCoordinator();
    $professorA = createProfessorAssignedStudentUser(['name' => 'Professor Assigned A']);
    $professorB = createProfessorAssignedStudentUser(['name' => 'Professor Assigned B']);

    $ownedStudent = createProfessorAssignedStudent($coordinator, ['name' => 'Owned Professor Advisee']);
    $otherStudent = createProfessorAssignedStudent($coordinator, ['name' => 'Other Professor Advisee']);

    OjtAssignment::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_type' => 'internal',
        'professor_id' => $professorA->id,
        'status' => 'active',
    ]);

    OjtAssignment::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_type' => 'internal',
        'professor_id' => $professorB->id,
        'status' => 'active',
    ]);

    $this->actingAs($professorA)
        ->get(route('professor.assigned-students'))
        ->assertOk()
        ->assertSee('Assigned Students')
        ->assertSee($ownedStudent->name)
        ->assertDontSee($otherStudent->name);
});

test('professors can assign tasks to their assigned students', function () {
    $coordinator = createProfessorAssignedStudentCoordinator();
    $professor = createProfessorAssignedStudentUser(['name' => 'Professor Task Owner']);
    $studentA = createProfessorAssignedStudent($coordinator, ['name' => 'Professor Task Student A']);
    $studentB = createProfessorAssignedStudent($coordinator, ['name' => 'Professor Task Student B']);

    foreach ([$studentA, $studentB] as $student) {
        OjtAssignment::query()->create([
            'student_id' => $student->id,
            'ojt_type' => 'internal',
            'professor_id' => $professor->id,
            'status' => 'active',
        ]);
    }

    $this->actingAs($professor);

    $component = Livewire::test('pages::professor.tasks')
        ->set('student_ids', [$studentA->id, $studentB->id])
        ->set('task_title', 'Prepare weekly accomplishment report')
        ->set('task_description', 'Summarize completed OJT activities for this week.')
        ->set('priority', 'high')
        ->set('due_date', '2026-05-15')
        ->call('saveTask')
        ->assertHasNoErrors();

    $task = StudentTask::query()->where('student_id', $studentA->id)->first();

    expect($task)->not->toBeNull();
    expect($task->assigned_by)->toBe($professor->id);
    expect($task->title)->toBe('Prepare weekly accomplishment report');
    expect($task->priority)->toBe('high');

    $otherTask = StudentTask::query()->where('student_id', $studentB->id)->where('title', 'Prepare weekly accomplishment report')->first();

    expect($otherTask)->not->toBeNull();
    expect($otherTask->task_group_id)->toBe($task->task_group_id);

    $pendingColumn = collect($component->instance()->displayColumns())->firstWhere('status', 'pending');

    expect($pendingColumn['tasks'])->toHaveCount(1);
    expect($pendingColumn['tasks']->first()['students'])->toHaveCount(2);
});

test('professors cannot assign tasks to students not assigned to them', function () {
    $coordinator = createProfessorAssignedStudentCoordinator();
    $professorA = createProfessorAssignedStudentUser(['name' => 'Professor Task A']);
    $professorB = createProfessorAssignedStudentUser(['name' => 'Professor Task B']);
    $student = createProfessorAssignedStudent($coordinator, ['name' => 'Other Professor Task Student']);

    OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'professor_id' => $professorB->id,
        'status' => 'active',
    ]);

    $this->actingAs($professorA);

    Livewire::test('pages::professor.tasks')
        ->set('student_ids', [$student->id])
        ->set('task_title', 'Should not be assigned')
        ->set('priority', 'normal')
        ->call('saveTask')
        ->assertForbidden();

    expect(StudentTask::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('students can see tasks assigned by their professor', function () {
    $coordinator = createProfessorAssignedStudentCoordinator();
    $professor = createProfessorAssignedStudentUser(['name' => 'Professor Visible Task']);
    $student = createProfessorAssignedStudent($coordinator, ['name' => 'Student Visible Task']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'status' => 'active',
    ]);

    StudentTask::query()->create([
        'student_id' => $student->id,
        'assigned_by' => $professor->id,
        'ojt_assignment_id' => $assignment->id,
        'title' => 'Submit daily task evidence',
        'description' => 'Upload screenshots and short notes.',
        'priority' => 'normal',
        'status' => 'pending',
    ]);

    $this->actingAs($student)
        ->get(route('student.tasks'))
        ->assertOk()
        ->assertSee('Tasks')
        ->assertSee('Submit daily task evidence')
        ->assertSee($professor->name);
});

test('students can drag tasks between kanban columns', function () {
    $coordinator = createProfessorAssignedStudentCoordinator();
    $professor = createProfessorAssignedStudentUser(['name' => 'Professor Sort Task']);
    $student = createProfessorAssignedStudent($coordinator, ['name' => 'Student Sort Task']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'status' => 'active',
    ]);

    $pendingTask = StudentTask::query()->create([
        'student_id' => $student->id,
        'assigned_by' => $professor->id,
        'ojt_assignment_id' => $assignment->id,
        'title' => 'Pending task',
        'priority' => 'normal',
        'status' => 'pending',
        'sort_order' => 0,
    ]);

    $doneTask = StudentTask::query()->create([
        'student_id' => $student->id,
        'assigned_by' => $professor->id,
        'ojt_assignment_id' => $assignment->id,
        'title' => 'Done task',
        'priority' => 'normal',
        'status' => 'done',
        'sort_order' => 0,
    ]);

    $this->actingAs($student);

    Livewire::test('pages::student.tasks')
        ->call('sortTask', (string) $pendingTask->id, 0, 'done')
        ->assertHasNoErrors();

    expect($pendingTask->refresh()->status)->toBe('done');
    expect($pendingTask->sort_order)->toBe(0);
    expect($pendingTask->completed_at)->not->toBeNull();
    expect($doneTask->refresh()->sort_order)->toBe(1);
});

test('professors can drag grouped task cards between columns', function () {
    $coordinator = createProfessorAssignedStudentCoordinator();
    $professor = createProfessorAssignedStudentUser(['name' => 'Professor Group Sort']);
    $studentA = createProfessorAssignedStudent($coordinator, ['name' => 'Group Sort Student A']);
    $studentB = createProfessorAssignedStudent($coordinator, ['name' => 'Group Sort Student B']);
    $studentC = createProfessorAssignedStudent($coordinator, ['name' => 'Group Sort Student C']);

    $assignmentA = OjtAssignment::query()->create([
        'student_id' => $studentA->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'status' => 'active',
    ]);

    $assignmentB = OjtAssignment::query()->create([
        'student_id' => $studentB->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'status' => 'active',
    ]);

    $assignmentC = OjtAssignment::query()->create([
        'student_id' => $studentC->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'status' => 'active',
    ]);

    $groupId = (string) fake()->uuid();

    foreach ([[$studentA, $assignmentA], [$studentB, $assignmentB]] as [$student, $assignment]) {
        StudentTask::query()->create([
            'student_id' => $student->id,
            'assigned_by' => $professor->id,
            'ojt_assignment_id' => $assignment->id,
            'task_group_id' => $groupId,
            'title' => 'Grouped sortable task',
            'priority' => 'normal',
            'status' => 'pending',
            'sort_order' => 0,
        ]);
    }

    $existingReviewTask = StudentTask::query()->create([
        'student_id' => $studentC->id,
        'assigned_by' => $professor->id,
        'ojt_assignment_id' => $assignmentC->id,
        'title' => 'Existing review task',
        'priority' => 'normal',
        'status' => 'in_review',
        'sort_order' => 0,
    ]);

    $this->actingAs($professor);

    Livewire::test('pages::professor.tasks')
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

test('professors can submit practicum evaluations for assigned students', function () {
    $coordinator = createProfessorAssignedStudentCoordinator(['name' => 'Professor Evaluation Coordinator']);
    $professor = createProfessorAssignedStudentUser(['name' => 'Professor Evaluator']);
    $student = createProfessorAssignedStudent($coordinator, ['name' => 'Professor Evaluation Student']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'status' => 'active',
    ]);

    $this->actingAs($professor);

    Livewire::test('pages::professor.evaluations')
        ->call('openEvaluationModal', $assignment->id)
        ->set('inclusive_from', '2026-05-01')
        ->set('inclusive_to', '2026-05-31')
        ->set('evaluation_remarks', 'Consistently dependable.')
        ->call('saveEvaluation')
        ->assertHasNoErrors();

    $evaluation = Evaluation::query()->first();

    expect($evaluation)->not->toBeNull();
    expect($evaluation->student_id)->toBe($student->id);
    expect($evaluation->evaluator_id)->toBe($professor->id);
    expect($evaluation->evaluator_type)->toBe('professor');
    expect($evaluation->cooperating_program)->toBe('Professor Evaluation Coordinator');
    expect((float) $evaluation->rating)->toBe(100.0);
    expect($evaluation->status)->toBe('submitted');
});

test('professors can submit multiple practicum evaluations for one assigned student', function () {
    $coordinator = createProfessorAssignedStudentCoordinator(['name' => 'Professor Multiple Evaluation Coordinator']);
    $professor = createProfessorAssignedStudentUser(['name' => 'Professor Multiple Evaluator']);
    $student = createProfessorAssignedStudent($coordinator, ['name' => 'Professor Multiple Evaluation Student']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'status' => 'active',
    ]);

    $this->actingAs($professor);

    Livewire::test('pages::professor.evaluations')
        ->call('openEvaluationModal', $assignment->id)
        ->set('inclusive_from', '2026-05-01')
        ->set('inclusive_to', '2026-05-15')
        ->set('evaluation_remarks', 'First evaluation period.')
        ->call('saveEvaluation')
        ->call('openEvaluationModal', $assignment->id)
        ->set('inclusive_from', '2026-05-16')
        ->set('inclusive_to', '2026-05-31')
        ->set('evaluation_remarks', 'Second evaluation period.')
        ->call('saveEvaluation')
        ->assertHasNoErrors();

    $evaluations = Evaluation::query()
        ->where('student_id', $student->id)
        ->where('ojt_assignment_id', $assignment->id)
        ->where('evaluator_id', $professor->id)
        ->orderBy('inclusive_from')
        ->get();

    expect($evaluations)->toHaveCount(2);
    expect($evaluations->pluck('remarks')->all())->toBe([
        'First evaluation period.',
        'Second evaluation period.',
    ]);
});
