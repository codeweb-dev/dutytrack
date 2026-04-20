<?php

use App\Enums\Role as AppRole;
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
});
