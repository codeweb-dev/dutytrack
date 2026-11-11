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

test('coordinators can view the coordinator dashboard', function () {
    PermissionRole::findOrCreate(Role::OJTCOORDINATOR->value, 'web');
    PermissionRole::findOrCreate(Role::STUDENT->value, 'web');

    $coordinator = User::factory()->create();
    $coordinator->assignRole(Role::OJTCOORDINATOR->value);

    $this->actingAs($coordinator)
        ->get(route('coordinator.dashboard'))
        ->assertOk()
        ->assertSee('Weekly Attendance')
        ->assertSee('Recent Students');
});

test('supervisors can view the supervisor dashboard', function () {
    PermissionRole::findOrCreate(Role::SUPERVISOR->value, 'web');

    $supervisor = User::factory()->create();
    $supervisor->assignRole(Role::SUPERVISOR->value);

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
        ->assertSee('OJT Progress')
        ->assertDontSee('Recent Attendance')
        ->assertDontSee('Weekly Logged Hours')
        ->assertDontSee('Approved Hours');
});

test('duty hour storage and routes have been removed', function () {
    expect(\Illuminate\Support\Facades\Schema::hasTable('duty_hour_logs'))->toBeFalse();

    foreach (['coordinator', 'supervisor', 'student'] as $role) {
        expect(\Illuminate\Support\Facades\Route::has($role.'.duty-hours'))->toBeFalse();
        $this->get('/'.$role.'/duty-hours')->assertNotFound();
    }
});

test('students can view the requirements page', function () {
    PermissionRole::findOrCreate(Role::STUDENT->value, 'web');

    $student = User::factory()->create();
    $student->assignRole(Role::STUDENT->value);

    $this->actingAs($student)
        ->get(route('student.requirements'))
        ->assertOk()
        ->assertSee('OJT Student Requirements');
});
