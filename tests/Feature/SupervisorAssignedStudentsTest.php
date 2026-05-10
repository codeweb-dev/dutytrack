<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\OjtAssignment;
use App\Models\StudentTask;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
    $supervisor->assignRole(AppRole::COMPANYSUPERVISOR->value);

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
        'year_level' => '4',
        'section' => 'A',
        'ojt_required_hours' => 600,
    ]);

    return $student;
}

test('company supervisors can view only their assigned students', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisorA = createSupervisorAssignedStudentUser(['name' => 'Company Supervisor Assigned A']);
    $supervisorB = createSupervisorAssignedStudentUser(['name' => 'Company Supervisor Assigned B']);

    $ownedStudent = createSupervisorAssignedStudent($coordinator, ['name' => 'Owned Supervisor Trainee']);
    $otherStudent = createSupervisorAssignedStudent($coordinator, ['name' => 'Other Supervisor Trainee']);

    OjtAssignment::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisorA->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    OjtAssignment::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisorB->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    $this->actingAs($supervisorA)
        ->get(route('supervisor.assigned-students'))
        ->assertOk()
        ->assertSee('Assigned Students')
        ->assertSee($ownedStudent->name)
        ->assertDontSee($otherStudent->name);
});

test('company supervisors can approve pending duty hours from duty hours page', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisorA = createSupervisorAssignedStudentUser(['name' => 'Company Supervisor Duty A']);
    $supervisorB = createSupervisorAssignedStudentUser(['name' => 'Company Supervisor Duty B']);

    $ownedStudent = createSupervisorAssignedStudent($coordinator, ['name' => 'Owned Supervisor Duty Student']);
    $otherStudent = createSupervisorAssignedStudent($coordinator, ['name' => 'Other Supervisor Duty Student']);

    $ownedAssignment = OjtAssignment::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisorA->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    $otherAssignment = OjtAssignment::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisorB->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    $attendance = AttendanceLog::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_assignment_id' => $ownedAssignment->id,
        'attendance_date' => '2026-04-30',
        'morning_time_in' => '2026-04-30 08:00:00',
        'morning_time_out' => '2026-04-30 12:00:00',
        'hours_rendered' => 4,
        'status' => 'present',
    ]);

    $ownedLog = DutyHourLog::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_assignment_id' => $ownedAssignment->id,
        'attendance_log_id' => $attendance->id,
        'log_date' => '2026-04-30',
        'hours_rendered' => 4,
        'status' => 'pending',
    ]);

    $otherLog = DutyHourLog::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_assignment_id' => $otherAssignment->id,
        'log_date' => '2026-04-30',
        'hours_rendered' => 3,
        'status' => 'pending',
    ]);

    $this->actingAs($supervisorA);

    $this->get(route('supervisor.duty-hours'))
        ->assertOk()
        ->assertSee('Duty Hours')
        ->assertSee('Owned Supervisor Duty Student')
        ->assertDontSee('Other Supervisor Duty Student');

    Livewire::test('pages::supervisor.duty-hours')
        ->call('approveDutyHour', $ownedLog->id)
        ->assertHasNoErrors();

    $ownedLog->refresh();

    expect($ownedLog->status)->toBe('approved');
    expect((float) $ownedLog->approved_hours)->toBe(4.0);
    expect($ownedLog->approved_by)->toBe($supervisorA->id);

    expect(fn () => Livewire::test('pages::supervisor.duty-hours')
        ->call('approveDutyHour', $otherLog->id))
        ->toThrow(ModelNotFoundException::class);
});

test('company supervisors can assign tasks to their assigned trainees', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisor = createSupervisorAssignedStudentUser(['name' => 'Supervisor Task Owner']);
    $studentA = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Task Student A']);
    $studentB = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Task Student B']);

    foreach ([$studentA, $studentB] as $student) {
        OjtAssignment::query()->create([
            'student_id' => $student->id,
            'ojt_type' => 'external',
            'company_supervisor_id' => $supervisor->id,
            'start_date' => '2026-04-20',
            'required_hours' => 600,
            'status' => 'active',
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

test('company supervisors cannot assign tasks to trainees not assigned to them', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisorA = createSupervisorAssignedStudentUser(['name' => 'Supervisor Task A']);
    $supervisorB = createSupervisorAssignedStudentUser(['name' => 'Supervisor Task B']);
    $student = createSupervisorAssignedStudent($coordinator, ['name' => 'Other Supervisor Task Student']);

    OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisorB->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
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

test('company supervisors can drag grouped task cards between columns', function () {
    $coordinator = createSupervisorAssignedStudentCoordinator();
    $supervisor = createSupervisorAssignedStudentUser(['name' => 'Supervisor Group Sort']);
    $studentA = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Group Sort A']);
    $studentB = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Group Sort B']);
    $studentC = createSupervisorAssignedStudent($coordinator, ['name' => 'Supervisor Group Sort C']);

    $assignmentA = OjtAssignment::query()->create([
        'student_id' => $studentA->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    $assignmentB = OjtAssignment::query()->create([
        'student_id' => $studentB->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    $assignmentC = OjtAssignment::query()->create([
        'student_id' => $studentC->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $supervisor->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
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
