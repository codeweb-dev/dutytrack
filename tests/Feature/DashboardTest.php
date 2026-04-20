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
