<?php

use App\Enums\Role as AppRole;
use App\Models\OjtAssignment;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureAssignmentRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

function createCoordinatorForAssignments(array $attributes = []): User
{
    ensureAssignmentRolesExist();

    $coordinator = User::factory()->create($attributes);
    $coordinator->assignRole(AppRole::OJTCOORDINATOR->value);

    return $coordinator;
}

function createManagedStudent(User $coordinator, array $attributes = []): User
{
    ensureAssignmentRolesExist();

    $sequence = fake()->unique()->numerify('####');

    $student = User::factory()->create(array_merge([
        'name' => "Managed Student {$sequence}",
        'first_name' => "Managed{$sequence}",
        'last_name' => 'Student',
        'email' => "managed-student-{$sequence}@example.com",
        'coordinator_id' => $coordinator->id,
    ], $attributes));

    $student->assignRole(AppRole::STUDENT->value);

    $student->studentProfile()->create([
        'student_id' => "SID-{$sequence}",
        'course' => 'BSIT',
        'ojt_required_hours' => 600,
    ]);

    return $student;
}

function createCompanySupervisor(array $attributes = []): User
{
    ensureAssignmentRolesExist();

    $sequence = fake()->unique()->numerify('####');

    $supervisor = User::factory()->create(array_merge([
        'name' => "Supervisor {$sequence}",
        'first_name' => "Supervisor{$sequence}",
        'last_name' => 'Lead',
        'email' => "supervisor-{$sequence}@example.com",
    ], $attributes));

    $supervisor->assignRole(AppRole::SUPERVISOR->value);
    $supervisor->supervisorProfile()->create([
        'supervisor_type' => 'internal',
        'company_name' => 'Evergreen College',
    ]);

    return $supervisor;
}

test('coordinator can create an internal assignment for their own student', function () {
    $coordinator = createCoordinatorForAssignments();
    $student = createManagedStudent($coordinator);
    $supervisor = createCompanySupervisor();

    $this->actingAs($coordinator);

    Livewire::test('coordinator.ojt-assignments.create')
        ->set('form.student_id', $student->id)
        ->set('form.ojt_type', 'internal')
        ->set('form.company_supervisor_id', $supervisor->id)
        ->set('form.geofence_latitude', 14.599512)
        ->set('form.geofence_longitude', 120.984222)
        ->set('form.geofence_radius', 100)
        ->call('save')
        ->assertHasNoErrors();

    $assignment = OjtAssignment::query()->where('student_id', $student->id)->first();

    expect($assignment)->not->toBeNull();
    expect($assignment->ojt_type)->toBe('internal');
    expect($assignment->company_supervisor_id)->toBe($supervisor->id);
});

test('coordinator can create an external assignment for their own student', function () {
    $coordinator = createCoordinatorForAssignments();
    $student = createManagedStudent($coordinator);
    $supervisor = createCompanySupervisor();
    $supervisor->supervisorProfile()->update(['supervisor_type' => 'external']);

    $this->actingAs($coordinator);

    Livewire::test('coordinator.ojt-assignments.create')
        ->set('form.student_id', $student->id)
        ->set('form.ojt_type', 'external')
        ->set('form.company_supervisor_id', $supervisor->id)
        ->set('form.geofence_latitude', 14.599512)
        ->set('form.geofence_longitude', 120.984222)
        ->set('form.geofence_radius', 100)
        ->call('save')
        ->assertHasNoErrors();

    $assignment = OjtAssignment::query()->where('student_id', $student->id)->first();

    expect($assignment)->not->toBeNull();
    expect($assignment->ojt_type)->toBe('external');
    expect($assignment->company_supervisor_id)->toBe($supervisor->id);
});

test('coordinator can switch from internal to external before saving an assignment', function () {
    $coordinator = createCoordinatorForAssignments();
    $student = createManagedStudent($coordinator);
    $supervisor = createCompanySupervisor();
    $supervisor->supervisorProfile()->update(['supervisor_type' => 'external']);

    $this->actingAs($coordinator);

    Livewire::test('coordinator.ojt-assignments.create')
        ->set('form.student_id', $student->id)
        ->set('form.ojt_type', 'internal')
        ->set('form.company_supervisor_id', $supervisor->id)
        ->set('form.ojt_type', 'external')
        ->set('form.company_supervisor_id', $supervisor->id)
        ->set('form.geofence_latitude', 14.599512)
        ->set('form.geofence_longitude', 120.984222)
        ->set('form.geofence_radius', 100)
        ->call('save')
        ->assertHasNoErrors();

    $assignment = OjtAssignment::query()->where('student_id', $student->id)->first();

    expect($assignment)->not->toBeNull();
    expect($assignment->ojt_type)->toBe('external');
    expect($assignment->company_supervisor_id)->toBe($supervisor->id);
});

test('coordinator cannot assign another coordinators student', function () {
    $coordinator = createCoordinatorForAssignments();
    $otherCoordinator = createCoordinatorForAssignments(['email' => 'other-coordinator@example.com']);
    $otherStudent = createManagedStudent($otherCoordinator);
    $supervisor = createCompanySupervisor();

    $this->actingAs($coordinator);

    Livewire::test('coordinator.ojt-assignments.create')
        ->set('form.student_id', $otherStudent->id)
        ->set('form.ojt_type', 'internal')
        ->set('form.company_supervisor_id', $supervisor->id)
        ->set('form.geofence_latitude', 14.599512)
        ->set('form.geofence_longitude', 120.984222)
        ->set('form.geofence_radius', 100)
        ->call('save')
        ->assertForbidden();

    expect(OjtAssignment::query()->where('student_id', $otherStudent->id)->exists())->toBeFalse();
});

test('coordinator cannot assign a student account as supervisor', function () {
    $coordinator = createCoordinatorForAssignments();
    $student = createManagedStudent($coordinator);
    $this->actingAs($coordinator);

    Livewire::test('coordinator.ojt-assignments.create')
        ->set('form.student_id', $student->id)
        ->set('form.company_supervisor_id', $student->id)
        ->call('save')
        ->assertForbidden();

    expect(OjtAssignment::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('coordinator must pin an attendance location for an assignment', function () {
    $coordinator = createCoordinatorForAssignments();
    $student = createManagedStudent($coordinator);
    $supervisor = createCompanySupervisor();

    $this->actingAs($coordinator);

    Livewire::test('coordinator.ojt-assignments.create')
        ->set('form.student_id', $student->id)
        ->set('form.ojt_type', 'internal')
        ->set('form.company_supervisor_id', $supervisor->id)
        ->call('save')
        ->assertHasErrors(['form.geofence_latitude', 'form.geofence_longitude', 'form.geofence_radius']);

    expect(OjtAssignment::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('coordinator can view archived assignments on the archive page', function () {
    $coordinator = createCoordinatorForAssignments();
    $student = createManagedStudent($coordinator);
    $supervisor = createCompanySupervisor();

    OjtAssignment::create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $supervisor->id,
        'geofence_latitude' => 14.599512,
        'geofence_longitude' => 120.984222,
        'geofence_radius' => 100,
    ])->delete();

    $this->actingAs($coordinator)
        ->get(route('coordinator.archive'))
        ->assertOk();

    Livewire::test('pages::coordinator.archive')
        ->set('type', 'assignments')
        ->assertSee($student->display_name);
});
