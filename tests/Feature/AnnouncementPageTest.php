<?php

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\User;
use Spatie\Permission\Models\Role as PermissionRole;

test('guests are redirected away from the announcements page', function () {
    $this->get(route('announcements'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view the announcements page', function () {
    PermissionRole::findOrCreate(Role::STUDENT->value, 'web');

    $student = User::factory()->create();
    $student->assignRole(Role::STUDENT->value);

    Announcement::create([
        'title' => 'Server Maintenance Window',
        'message' => 'The platform will undergo scheduled maintenance this Friday evening.',
        'type' => 'maintenance',
    ]);

    $this->actingAs($student)
        ->get(route('announcements'))
        ->assertOk()
        ->assertSee("What's new around here?", false)
        ->assertSee('Server Maintenance Window');
});
