<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        ->assertSee($ownedStudent->display_name)
        ->assertDontSee($otherStudent->display_name);
});

test('coordinator summary cards filter students by status', function () {
    $coordinator = createCoordinator();
    $pending = createStudentForCoordinator($coordinator, ['name' => 'Pending Card Student', 'status' => 'pending']);
    $assigned = createStudentForCoordinator($coordinator, ['name' => 'Assigned Card Student']);
    $unassigned = createStudentForCoordinator($coordinator, ['name' => 'Unassigned Card Student']);

    $supervisor = User::factory()->create();
    $supervisor->assignRole(AppRole::SUPERVISOR->value);

    OjtAssignment::query()->create([
        'student_id' => $assigned->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $supervisor->id,
    ]);

    $this->actingAs($coordinator);

    $ids = fn ($component) => $component->instance()->students->pluck('id')->sort()->values()->all();

    $component = Livewire::test('pages::coordinator.student-management');

    expect($ids($component->set('status', 'assigned')))->toBe([$assigned->id])
        ->and($ids($component->set('status', 'pending')))->toBe([$pending->id])
        ->and($ids($component->set('status', 'active')))->toBe(collect([$assigned->id, $unassigned->id])->sort()->values()->all())
        ->and($ids($component->set('status', '')))->toHaveCount(3);
});

test('students submit a requirement with an attached file', function () {
    Storage::fake('local');

    $coordinator = createCoordinator();
    $student = createStudentForCoordinator($coordinator);

    StudentRequirement::ensureForStudent($student);

    $this->actingAs($student);

    Livewire::test('pages::student.dashboard')
        ->call('submitRequirement', 'parents_waiver')
        ->assertHasErrors('requirementFiles.parents_waiver')
        ->set('requirementFiles.parents_waiver', UploadedFile::fake()->create('waiver.pdf', 100, 'application/pdf'))
        ->call('submitRequirement', 'parents_waiver')
        ->assertHasNoErrors();

    $requirement = StudentRequirement::query()
        ->where('student_id', $student->id)
        ->where('key', 'parents_waiver')
        ->first();

    expect($requirement?->submitted_at)->not->toBeNull();
    expect($requirement?->is_completed)->toBeFalse();
    Storage::disk('local')->assertExists($requirement->file_path);
});

test('students can only view their own private requirement files', function () {
    Storage::fake('local');
    $coordinator = createCoordinator();
    $student = createStudentForCoordinator($coordinator);
    $otherStudent = createStudentForCoordinator($coordinator);
    $requirement = StudentRequirement::ensureForStudent($student)->firstWhere('key', 'parents_waiver');
    Storage::disk('local')->put('student-requirements/waiver.pdf', 'waiver');
    $requirement->update(['file_path' => 'student-requirements/waiver.pdf']);

    $this->actingAs($student)
        ->get(route('student.requirements.file', $requirement))
        ->assertOk();

    $this->actingAs($otherStudent)
        ->get(route('student.requirements.file', $requirement))
        ->assertNotFound();
});

test('coordinators approve requirements and approval cannot be undone', function () {
    Storage::fake('local');
    $coordinator = createCoordinator();
    $student = createStudentForCoordinator($coordinator);

    $requirement = StudentRequirement::ensureForStudent($student)
        ->firstWhere('key', 'parents_waiver');

    Storage::disk('local')->put('student-requirements/waiver.pdf', 'waiver');
    $requirement->update(['file_path' => 'student-requirements/waiver.pdf', 'submitted_at' => now()]);

    $this->actingAs($coordinator);

    Livewire::test('coordinator.student-management.requirements', ['student' => $student])
        ->call('approveRequirement', $requirement->id)
        ->call('approveRequirement', $requirement->id)
        ->assertHasNoErrors();

    $requirement->refresh();

    expect($requirement->is_completed)->toBeTrue();
    expect($requirement->completed_at)->not->toBeNull();
    expect($requirement->completed_by)->toBe($coordinator->id);
});

test('coordinators cannot approve requirements without an attached file', function () {
    $coordinator = createCoordinator();
    $student = createStudentForCoordinator($coordinator);

    $requirement = StudentRequirement::ensureForStudent($student)
        ->firstWhere('key', 'parents_waiver');

    $this->actingAs($coordinator);

    Livewire::test('coordinator.student-management.requirements', ['student' => $student])
        ->call('approveRequirement', $requirement->id)
        ->assertHasNoErrors();

    expect($requirement->refresh()->is_completed)->toBeFalse();
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
        ->assertSee($ownedStudent->display_name)
        ->assertDontSee($otherStudent->display_name);
});

test('coordinators can monitor attendance for their own students', function () {
    $coordinatorA = createCoordinator(['name' => 'Coordinator Attendance A']);
    $coordinatorB = createCoordinator(['name' => 'Coordinator Attendance B']);

    $ownedStudent = createStudentForCoordinator($coordinatorA, ['name' => 'Attendance Owned Student']);
    $otherStudent = createStudentForCoordinator($coordinatorB, ['name' => 'Attendance Other Student']);

    $mentor = User::factory()->create([
        'name' => 'Attendance Mentor',
        'email' => 'attendance-mentor@example.com',
    ]);
    $mentor->assignRole(AppRole::SUPERVISOR->value);

    $ownedAssignment = OjtAssignment::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    $otherAssignment = OjtAssignment::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
    ]);

    AttendanceLog::query()->create([
        'student_id' => $ownedStudent->id,
        'ojt_assignment_id' => $ownedAssignment->id,
        'attendance_date' => '2026-05-01',
        'morning_time_in' => '2026-05-01 07:00:00',
        'morning_time_out' => '2026-05-01 12:00:00',
        'afternoon_time_in' => '2026-05-01 13:00:00',
        'afternoon_time_out' => '2026-05-01 17:00:00',
        'hours_rendered' => 10,
        'status' => 'present',
    ]);

    AttendanceLog::query()->create([
        'student_id' => $otherStudent->id,
        'ojt_assignment_id' => $otherAssignment->id,
        'attendance_date' => '2026-05-01',
        'morning_time_in' => '2026-05-01 07:15:00',
        'morning_time_out' => '2026-05-01 12:00:00',
        'afternoon_time_in' => '2026-05-01 13:00:00',
        'afternoon_time_out' => '2026-05-01 17:00:00',
        'hours_rendered' => 9.75,
        'status' => 'late',
    ]);

    $this->actingAs($coordinatorA)
        ->get(route('coordinator.attendance'))
        ->assertOk()
        ->assertSee('Attendance Monitoring')
        ->assertSee($ownedStudent->display_name)
        ->assertDontSee($otherStudent->display_name);
});

test('editing a student requires a profile photo when none exists', function () {
    Storage::fake('public');

    $coordinator = createCoordinator();
    $student = createStudentForCoordinator($coordinator, ['contact_number' => '9123456789', 'profile_photo_path' => null]);
    $student->studentProfile->update([
        'birthdate' => '2004-01-01',
        'address' => 'Street, City',
        'guardian_name' => 'Maria Dela Cruz',
        'guardian_address' => 'Street, City',
        'guardian_contact_number' => '9123456789',
    ]);

    $this->actingAs($coordinator);

    Livewire::test('coordinator.student-management.edit', ['student' => $student])
        ->call('save')
        ->assertHasErrors(['form.photo' => 'required'])
        ->set('form.photo', UploadedFile::fake()->image('me.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    expect($student->fresh()->profile_photo_path)->not->toBeNull();
});

test('coordinators can set a student to inactive so they cannot log in until activated again', function () {
    $coordinator = createCoordinator();
    $student = createStudentForCoordinator($coordinator, ['status' => 'active']);
    $student->studentProfile->update(['birthdate' => '2004-01-01']);
    $login = fn () => $this->post(route('login.store'), [
        'role' => AppRole::STUDENT->value,
        'email' => $student->studentProfile->student_id,
        'birthdate' => '2004-01-01',
        'password' => 'password',
    ]);

    $this->actingAs($coordinator);
    $component = Livewire::test('pages::coordinator.student-management')->call('setInactive', $student->id);

    expect($student->fresh()->status)->toBe('inactive')
        ->and($component->set('status', 'inactive')->instance()->students->pluck('id')->all())->toBe([$student->id])
        ->and($component->set('status', 'active')->instance()->students)->toBeEmpty();

    auth()->logout();
    $login()->assertSessionHasErrors(['account' => 'Your account has been set to inactive by your OJT coordinator.']);
    $this->assertGuest();

    $this->actingAs($coordinator);
    Livewire::test('pages::coordinator.student-management')->call('activate', $student->id);
    auth()->logout();

    $login()->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($student);
});
