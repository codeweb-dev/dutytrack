<?php

use App\Enums\Role as AppRole;
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
        'year_level' => '4',
        'section' => 'A',
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
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    OjtAssignment::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_type' => 'internal',
        'professor_id' => $professorB->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
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
            'start_date' => '2026-04-20',
            'required_hours' => 600,
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
        'start_date' => '2026-04-20',
        'required_hours' => 600,
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
        'start_date' => '2026-04-20',
        'required_hours' => 600,
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
