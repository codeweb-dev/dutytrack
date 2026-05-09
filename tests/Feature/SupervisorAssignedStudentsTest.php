<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\OjtAssignment;
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
        'time_in' => '2026-04-30 08:00:00',
        'time_out' => '2026-04-30 12:00:00',
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
