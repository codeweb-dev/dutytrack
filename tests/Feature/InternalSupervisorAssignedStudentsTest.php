<?php

use App\Enums\Role as AppRole;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\StudentTask;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureMentorAssignedStudentRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

function createMentorAssignedStudentUser(array $attributes = []): User
{
    ensureMentorAssignedStudentRolesExist();

    $mentor = User::factory()->create($attributes);
    $mentor->assignRole(AppRole::SUPERVISOR->value);

    return $mentor;
}

function createMentorAssignedStudentCoordinator(array $attributes = []): User
{
    ensureMentorAssignedStudentRolesExist();

    $coordinator = User::factory()->create($attributes);
    $coordinator->assignRole(AppRole::OJTCOORDINATOR->value);

    return $coordinator;
}

function createMentorAssignedStudent(User $coordinator, array $attributes = []): User
{
    ensureMentorAssignedStudentRolesExist();

    $sequence = fake()->unique()->numerify('####');

    $student = User::factory()->create(array_merge([
        'name' => "Mentor Assigned Student {$sequence}",
        'first_name' => "MentorAssigned{$sequence}",
        'last_name' => 'Student',
        'email' => "mentor-assigned-student-{$sequence}@example.com",
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

test('mentors can view only their assigned students', function () {
    $coordinator = createMentorAssignedStudentCoordinator();
    $mentorA = createMentorAssignedStudentUser(['name' => 'Mentor Assigned A']);
    $mentorB = createMentorAssignedStudentUser(['name' => 'Mentor Assigned B']);

    $ownedStudent = createMentorAssignedStudent($coordinator, ['name' => 'Owned Mentor Advisee']);
    $otherStudent = createMentorAssignedStudent($coordinator, ['name' => 'Other Mentor Advisee']);

    OjtAssignment::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentorA->id,
    ]);

    OjtAssignment::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentorB->id,
    ]);

    $this->actingAs($mentorA)
        ->get(route('supervisor.assigned-students'))
        ->assertOk()
        ->assertSee('Assigned Students')
        ->assertSee($ownedStudent->name)
        ->assertDontSee($otherStudent->name);
});

test('mentors can assign tasks to their assigned students', function () {
    $coordinator = createMentorAssignedStudentCoordinator();
    $mentor = createMentorAssignedStudentUser(['name' => 'Mentor Task Owner']);
    $studentA = createMentorAssignedStudent($coordinator, ['name' => 'Mentor Task Student A']);
    $studentB = createMentorAssignedStudent($coordinator, ['name' => 'Mentor Task Student B']);

    foreach ([$studentA, $studentB] as $student) {
        OjtAssignment::query()->create([
            'student_id' => $student->id,
            'ojt_type' => 'internal',
            'company_supervisor_id' => $mentor->id,
        ]);
    }

    $this->actingAs($mentor);

    $component = Livewire::test('pages::supervisor.tasks')
        ->set('student_ids', [$studentA->id, $studentB->id])
        ->set('task_title', 'Prepare weekly accomplishment report')
        ->set('task_description', 'Summarize completed OJT activities for this week.')
        ->set('priority', 'high')
        ->set('due_date', '2026-05-15')
        ->call('saveTask')
        ->assertHasNoErrors();

    $task = StudentTask::query()->where('student_id', $studentA->id)->first();

    expect($task)->not->toBeNull();
    expect($task->assigned_by)->toBe($mentor->id);
    expect($task->title)->toBe('Prepare weekly accomplishment report');
    expect($task->priority)->toBe('high');

    $otherTask = StudentTask::query()->where('student_id', $studentB->id)->where('title', 'Prepare weekly accomplishment report')->first();

    expect($otherTask)->not->toBeNull();
    expect($otherTask->task_group_id)->toBe($task->task_group_id);

    $pendingColumn = collect($component->instance()->displayColumns())->firstWhere('status', 'pending');

    expect($pendingColumn['tasks'])->toHaveCount(1);
    expect($pendingColumn['tasks']->first()['students'])->toHaveCount(2);
});

test('mentors cannot assign tasks to students not assigned to them', function () {
    $coordinator = createMentorAssignedStudentCoordinator();
    $mentorA = createMentorAssignedStudentUser(['name' => 'Mentor Task A']);
    $mentorB = createMentorAssignedStudentUser(['name' => 'Mentor Task B']);
    $student = createMentorAssignedStudent($coordinator, ['name' => 'Other Mentor Task Student']);

    OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentorB->id,
    ]);

    $this->actingAs($mentorA);

    Livewire::test('pages::supervisor.tasks')
        ->set('student_ids', [$student->id])
        ->set('task_title', 'Should not be assigned')
        ->set('priority', 'normal')
        ->call('saveTask')
        ->assertForbidden();

    expect(StudentTask::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('students can see tasks assigned by their mentor', function () {
    $coordinator = createMentorAssignedStudentCoordinator();
    $mentor = createMentorAssignedStudentUser(['name' => 'Mentor Visible Task']);
    $student = createMentorAssignedStudent($coordinator, ['name' => 'Student Visible Task']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    StudentTask::query()->create([
        'student_id' => $student->id,
        'assigned_by' => $mentor->id,
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
        ->assertSee($mentor->name);
});

test('students can drag tasks between kanban columns', function () {
    $coordinator = createMentorAssignedStudentCoordinator();
    $mentor = createMentorAssignedStudentUser(['name' => 'Mentor Sort Task']);
    $student = createMentorAssignedStudent($coordinator, ['name' => 'Student Sort Task']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $pendingTask = StudentTask::query()->create([
        'student_id' => $student->id,
        'assigned_by' => $mentor->id,
        'ojt_assignment_id' => $assignment->id,
        'title' => 'Pending task',
        'priority' => 'normal',
        'status' => 'pending',
        'sort_order' => 0,
    ]);

    $doneTask = StudentTask::query()->create([
        'student_id' => $student->id,
        'assigned_by' => $mentor->id,
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

test('mentors can drag grouped task cards between columns', function () {
    $coordinator = createMentorAssignedStudentCoordinator();
    $mentor = createMentorAssignedStudentUser(['name' => 'Mentor Group Sort']);
    $studentA = createMentorAssignedStudent($coordinator, ['name' => 'Group Sort Student A']);
    $studentB = createMentorAssignedStudent($coordinator, ['name' => 'Group Sort Student B']);
    $studentC = createMentorAssignedStudent($coordinator, ['name' => 'Group Sort Student C']);

    $assignmentA = OjtAssignment::query()->create([
        'student_id' => $studentA->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $assignmentB = OjtAssignment::query()->create([
        'student_id' => $studentB->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $assignmentC = OjtAssignment::query()->create([
        'student_id' => $studentC->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $groupId = (string) fake()->uuid();

    foreach ([[$studentA, $assignmentA], [$studentB, $assignmentB]] as [$student, $assignment]) {
        StudentTask::query()->create([
            'student_id' => $student->id,
            'assigned_by' => $mentor->id,
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
        'assigned_by' => $mentor->id,
        'ojt_assignment_id' => $assignmentC->id,
        'title' => 'Existing review task',
        'priority' => 'normal',
        'status' => 'in_review',
        'sort_order' => 0,
    ]);

    $this->actingAs($mentor);

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

test('mentors can submit practicum evaluations for assigned students', function () {
    $coordinator = createMentorAssignedStudentCoordinator(['name' => 'Mentor Evaluation Coordinator']);
    $mentor = createMentorAssignedStudentUser(['name' => 'Mentor Evaluator']);
    $student = createMentorAssignedStudent($coordinator, ['name' => 'Mentor Evaluation Student']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $this->actingAs($mentor);

    Livewire::test('pages::supervisor.evaluations')
        ->call('openEvaluationModal', $assignment->id)
        ->set('inclusive_from', '2026-05-01')
        ->set('inclusive_to', '2026-05-31')
        ->set('evaluation_remarks', 'Consistently dependable.')
        ->call('saveEvaluation')
        ->assertHasNoErrors();

    $evaluation = Evaluation::query()->first();

    expect($evaluation)->not->toBeNull();
    expect($evaluation->student_id)->toBe($student->id);
    expect($evaluation->evaluator_id)->toBe($mentor->id);
    expect($evaluation->evaluator_type)->toBe('company_supervisor');
    expect($evaluation->cooperating_program)->toBe('Mentor Evaluation Coordinator');
    expect((float) $evaluation->rating)->toBe(100.0);
    expect($evaluation->status)->toBe('submitted');
});

test('mentors can submit multiple practicum evaluations for one assigned student', function () {
    $coordinator = createMentorAssignedStudentCoordinator(['name' => 'Mentor Multiple Evaluation Coordinator']);
    $mentor = createMentorAssignedStudentUser(['name' => 'Mentor Multiple Evaluator']);
    $student = createMentorAssignedStudent($coordinator, ['name' => 'Mentor Multiple Evaluation Student']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $this->actingAs($mentor);

    Livewire::test('pages::supervisor.evaluations')
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
        ->where('evaluator_id', $mentor->id)
        ->orderBy('inclusive_from')
        ->get();

    expect($evaluations)->toHaveCount(2);
    expect($evaluations->pluck('remarks')->all())->toBe([
        'First evaluation period.',
        'Second evaluation period.',
    ]);
});

test('mentors can request a revision and students can redo the task', function () {
    $coordinator = createMentorAssignedStudentCoordinator();
    $mentor = createMentorAssignedStudentUser(['name' => 'Mentor Revision']);
    $student = createMentorAssignedStudent($coordinator, ['name' => 'Student Revision']);

    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $task = StudentTask::query()->create([
        'student_id' => $student->id,
        'assigned_by' => $mentor->id,
        'ojt_assignment_id' => $assignment->id,
        'title' => 'Revisable task',
        'priority' => 'normal',
        'status' => 'in_review',
    ]);

    $this->actingAs($mentor);

    Livewire::test('pages::supervisor.tasks')
        ->call('openRevision', 'task-'.$task->id)
        ->call('requestRevision')
        ->assertHasErrors('revision_note')
        ->set('revision_note', 'Add the missing screenshots.')
        ->call('requestRevision')
        ->assertHasNoErrors();

    expect($task->refresh()->status)->toBe('revision');
    expect($task->revision_note)->toBe('Add the missing screenshots.');

    $this->actingAs($student)
        ->get(route('student.tasks'))
        ->assertSee('Add the missing screenshots.');

    Livewire::test('pages::student.tasks')
        ->call('sortTask', (string) $task->id, 0, 'in_review')
        ->assertHasNoErrors();

    expect($task->refresh()->status)->toBe('in_review');

    Livewire::test('pages::student.tasks')
        ->call('sortTask', (string) $task->id, 0, 'revision')
        ->assertForbidden();
});

test('mentors can edit a grouped task for every trainee on it', function () {
    $coordinator = createMentorAssignedStudentCoordinator();
    $mentor = createMentorAssignedStudentUser(['name' => 'Mentor Edit']);
    $outsider = createMentorAssignedStudentUser(['name' => 'Mentor Outsider']);
    $students = collect([
        createMentorAssignedStudent($coordinator, ['name' => 'Student Edit One']),
        createMentorAssignedStudent($coordinator, ['name' => 'Student Edit Two']),
    ]);

    $tasks = $students->map(function ($student) use ($mentor) {
        $assignment = OjtAssignment::query()->create([
            'student_id' => $student->id,
            'ojt_type' => 'internal',
            'company_supervisor_id' => $mentor->id,
        ]);

        return StudentTask::query()->create([
            'student_id' => $student->id,
            'assigned_by' => $mentor->id,
            'ojt_assignment_id' => $assignment->id,
            'task_group_id' => 'edit-group',
            'title' => 'Old title',
            'priority' => 'normal',
            'status' => 'in_progress',
        ]);
    });

    $this->actingAs($mentor);

    Livewire::test('pages::supervisor.tasks')
        ->call('editTask', 'edit-group')
        ->assertSet('task_title', 'Old title')
        ->set('task_title', 'New title')
        ->set('priority', 'high')
        ->set('due_date', '2026-12-01')
        ->call('saveTask')
        ->assertHasNoErrors()
        ->assertSet('editing_task_key', null);

    $tasks->each(function (StudentTask $task) {
        $task->refresh();

        expect($task->title)->toBe('New title')
            ->and($task->priority)->toBe('high')
            ->and($task->due_date->toDateString())->toBe('2026-12-01')
            ->and($task->status)->toBe('in_progress');
    });

    expect(StudentTask::query()->count())->toBe(2);

    $this->actingAs($outsider);

    Livewire::test('pages::supervisor.tasks')
        ->call('editTask', 'edit-group')
        ->assertNotFound();
});
