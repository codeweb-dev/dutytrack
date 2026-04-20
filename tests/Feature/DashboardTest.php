<?php

use App\Enums\Role;
use App\Models\User;
use Spatie\Permission\Models\Role as PermissionRole;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users without a recognized role receive a forbidden response', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertForbidden();
});

test('admins can view the admin dashboard', function () {
    PermissionRole::findOrCreate(Role::ADMIN->value, 'web');

    $admin = User::factory()->create();
    $admin->assignRole(Role::ADMIN->value);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Weekly Activity')
        ->assertSee('Recent Assignments');
});

test('coordinators can view the coordinator dashboard', function () {
    PermissionRole::findOrCreate(Role::OJTCOORDINATOR->value, 'web');

    $coordinator = User::factory()->create();
    $coordinator->assignRole(Role::OJTCOORDINATOR->value);

    $this->actingAs($coordinator)
        ->get(route('coordinator.dashboard'))
        ->assertOk()
        ->assertSee('Weekly Attendance')
        ->assertSee('Recent Students');
});

test('professors can view the professor dashboard', function () {
    PermissionRole::findOrCreate(Role::PROFESSOR->value, 'web');

    $professor = User::factory()->create();
    $professor->assignRole(Role::PROFESSOR->value);

    $this->actingAs($professor)
        ->get(route('professor.dashboard'))
        ->assertOk()
        ->assertSee('Weekly Attendance')
        ->assertSee('Recent Advisees');
});

test('company supervisors can view the supervisor dashboard', function () {
    PermissionRole::findOrCreate(Role::COMPANYSUPERVISOR->value, 'web');

    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::COMPANYSUPERVISOR->value);

    $this->actingAs($supervisor)
        ->get(route('supervisor.dashboard'))
        ->assertOk()
        ->assertSee('Weekly Attendance')
        ->assertSee('Recent Trainees');
});

test('students can view the student dashboard', function () {
    PermissionRole::findOrCreate(Role::STUDENT->value, 'web');

    $student = User::factory()->create();
    $student->assignRole(Role::STUDENT->value);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('Weekly Logged Hours')
        ->assertSee('Recent Attendance');
});
