<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

function createCoordinator(array $attributes = []): User
{
    ensureRolesExist();

    $coordinator = User::factory()->create($attributes);
    $coordinator->assignRole(AppRole::OJTCOORDINATOR->value);

    return $coordinator;
}

function createStudentForCoordinator(User $coordinator, array $attributes = []): User
{
    ensureRolesExist();

    $sequence = fake()->unique()->numerify('####');

    $student = User::factory()->create(array_merge([
        'name' => "Student {$sequence}",
        'first_name' => "Student{$sequence}",
        'last_name' => 'Owner',
        'email' => "student{$sequence}@example.com",
        'coordinator_id' => $coordinator->id,
    ], $attributes));

    $student->assignRole(AppRole::STUDENT->value);

    $student->studentProfile()->create([
        'student_id' => "S-{$sequence}",
        'course' => 'BSIT',
        'year_level' => '4',
        'section' => 'A',
        'ojt_required_hours' => 600,
    ]);

    return $student;
}

test('coordinator student management only shows the logged in coordinators students', function () {
    $coordinatorA = createCoordinator(['name' => 'Coordinator Alpha', 'email' => 'alpha@example.com']);
    $coordinatorB = createCoordinator(['name' => 'Coordinator Beta', 'email' => 'beta@example.com']);

    $ownedStudent = createStudentForCoordinator($coordinatorA, [
        'name' => 'Owned Student Alpha',
        'first_name' => 'Owned',
        'last_name' => 'Alpha',
        'email' => 'owned-alpha@example.com',
    ]);

    $otherStudent = createStudentForCoordinator($coordinatorB, [
        'name' => 'Other Student Beta',
        'first_name' => 'Other',
        'last_name' => 'Beta',
        'email' => 'other-beta@example.com',
    ]);

    $this->actingAs($coordinatorA)
        ->get(route('coordinator.student-management'))
        ->assertOk()
        ->assertSee($ownedStudent->name)
        ->assertDontSee($otherStudent->name);
});

test('creating a student from the coordinator flow assigns ownership to the current coordinator', function () {
    $coordinator = createCoordinator([
        'name' => 'Coordinator Gamma',
        'email' => 'gamma@example.com',
    ]);

    $this->actingAs($coordinator);

    Livewire::test('coordinator.student-management.create')
        ->set('form.student_id', '2026-0001')
        ->set('form.first_name', 'Jamie')
        ->set('form.middle_name', 'Lopez')
        ->set('form.last_name', 'Student')
        ->set('form.email', 'jamie.student@example.com')
        ->set('form.contact_number', '9123456789')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->set('form.course', 'BSIT')
        ->set('form.year_level', '4')
        ->set('form.section', 'A')
        ->set('form.ojt_required_hours', 600)
        ->set('form.requirementChecks', ['orientation'])
        ->call('save')
        ->assertHasNoErrors();

    $student = User::query()
        ->with('studentProfile')
        ->where('email', 'jamie.student@example.com')
        ->first();

    expect($student)->not->toBeNull();
    expect($student->coordinator_id)->toBe($coordinator->id);
    expect($student->hasRole(AppRole::STUDENT->value))->toBeTrue();
    expect($student->studentProfile?->student_id)->toBe('2026-0001');
    expect($student->studentRequirements()->count())->toBe(count(StudentRequirement::definitions()));
    expect($student->studentRequirements()->where('key', 'orientation')->first()?->is_completed)->toBeTrue();
    expect($student->studentRequirements()->where('key', 'parents_waiver')->first()?->is_completed)->toBeFalse();
});

test('students can mark a missing physical requirement as submitted', function () {
    $coordinator = createCoordinator();
    $student = createStudentForCoordinator($coordinator);

    StudentRequirement::ensureForStudent($student);

    $this->actingAs($student);

    Livewire::test('pages::student.dashboard')
        ->call('submitRequirement', 'parents_waiver')
        ->assertHasNoErrors();

    $requirement = StudentRequirement::query()
        ->where('student_id', $student->id)
        ->where('key', 'parents_waiver')
        ->first();

    expect($requirement?->submitted_at)->not->toBeNull();
    expect($requirement?->is_completed)->toBeFalse();
});

test('coordinators can check off submitted student requirements', function () {
    $coordinator = createCoordinator();
    $student = createStudentForCoordinator($coordinator);

    $requirement = StudentRequirement::ensureForStudent($student)
        ->firstWhere('key', 'parents_waiver');

    $this->actingAs($coordinator);

    Livewire::test('coordinator.student-management.requirements', ['student' => $student])
        ->call('toggleRequirement', $requirement->id)
        ->assertHasNoErrors();

    $requirement->refresh();

    expect($requirement->is_completed)->toBeTrue();
    expect($requirement->completed_at)->not->toBeNull();
    expect($requirement->completed_by)->toBe($coordinator->id);
});

test('coordinators can monitor requirements for their own students', function () {
    $coordinatorA = createCoordinator(['name' => 'Coordinator Requirements A']);
    $coordinatorB = createCoordinator(['name' => 'Coordinator Requirements B']);

    $ownedStudent = createStudentForCoordinator($coordinatorA, ['name' => 'Requirement Owned Student']);
    $otherStudent = createStudentForCoordinator($coordinatorB, ['name' => 'Requirement Other Student']);

    StudentRequirement::ensureForStudent($ownedStudent);
    StudentRequirement::ensureForStudent($otherStudent);

    $this->actingAs($coordinatorA)
        ->get(route('coordinator.requirements'))
        ->assertOk()
        ->assertSee('Requirement Monitoring')
        ->assertSee($ownedStudent->name)
        ->assertDontSee($otherStudent->name);
});

test('coordinators can monitor attendance for their own students', function () {
    $coordinatorA = createCoordinator(['name' => 'Coordinator Attendance A']);
    $coordinatorB = createCoordinator(['name' => 'Coordinator Attendance B']);

    $ownedStudent = createStudentForCoordinator($coordinatorA, ['name' => 'Attendance Owned Student']);
    $otherStudent = createStudentForCoordinator($coordinatorB, ['name' => 'Attendance Other Student']);

    $professor = User::factory()->create([
        'name' => 'Attendance Professor',
        'email' => 'attendance-professor@example.com',
    ]);
    $professor->assignRole(AppRole::PROFESSOR->value);

    $ownedAssignment = OjtAssignment::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    $otherAssignment = OjtAssignment::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);

    AttendanceLog::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_assignment_id' => $ownedAssignment->id,
        'attendance_date' => '2026-05-01',
        'time_in' => '2026-05-01 07:00:00',
        'time_out' => '2026-05-01 17:00:00',
        'hours_rendered' => 10,
        'status' => 'present',
    ]);

    AttendanceLog::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_assignment_id' => $otherAssignment->id,
        'attendance_date' => '2026-05-01',
        'time_in' => '2026-05-01 07:15:00',
        'time_out' => '2026-05-01 17:00:00',
        'hours_rendered' => 9.75,
        'status' => 'late',
    ]);

    $this->actingAs($coordinatorA)
        ->get(route('coordinator.attendance'))
        ->assertOk()
        ->assertSee('Attendance Monitoring')
        ->assertSee($ownedStudent->name)
        ->assertDontSee($otherStudent->name);
});
