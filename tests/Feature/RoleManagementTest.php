<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role as PermissionRole;

test('only the three supported roles are seeded and admin and professor routes are removed', function () {
    $this->seed(RoleSeeder::class);

    expect(PermissionRole::query()->pluck('name')->sort()->values()->all())
        ->toBe(['ojt_coordinator', 'student', 'supervisor']);

    $coordinator = User::factory()->create();
    $coordinator->assignRole(Role::OJTCOORDINATOR->value);
    $this->actingAs($coordinator);
    $this->get('/admin/dashboard')->assertNotFound();
    $this->get('/admin/professors')->assertNotFound();
    $this->get('/professor/dashboard')->assertNotFound();
});

test('coordinators manage announcements and audit logs; other roles cannot', function () {
    $this->seed(RoleSeeder::class);

    $coordinator = User::factory()->create();
    $coordinator->assignRole(Role::OJTCOORDINATOR->value);

    $this->actingAs($coordinator)->get(route('coordinator.announcements'))->assertOk()->assertSee('System Announcements');
    $this->actingAs($coordinator)->get(route('coordinator.audit-logs'))->assertOk()->assertSee('Audit Logs');
    $this->actingAs($coordinator)->get(route('announcements'))->assertOk()->assertSee('Manage Announcements');

    foreach ([Role::SUPERVISOR, Role::STUDENT] as $role) {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        $this->actingAs($user)->get(route('coordinator.announcements'))->assertForbidden();
        $this->actingAs($user)->get(route('coordinator.audit-logs'))->assertForbidden();
        $this->actingAs($user)->get(route('announcements'))->assertOk()->assertDontSee('Manage Announcements');
    }
});

test('coordinators can create supervisors owned by their account', function () {
    $this->seed(RoleSeeder::class);

    $coordinator = User::factory()->create();
    $coordinator->assignRole(Role::OJTCOORDINATOR->value);

    \Livewire\Livewire::actingAs($coordinator)
        ->test('coordinator.supervisors.create')
        ->set('form.first_name', 'Maria')
        ->set('form.last_name', 'Santos')
        ->set('form.email', 'maria.santos@example.com')
        ->set('form.password', 'Santos-2026!')
        ->set('form.password_confirmation', 'Santos-2026!')
        ->set('form.company_name', 'Acme Corp')
        ->set('form.photo', \Illuminate\Http\UploadedFile::fake()->image('maria.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $supervisor = User::query()->where('email', 'maria.santos@example.com')->first();

    expect($supervisor)->not->toBeNull()
        ->and($supervisor->hasRole(Role::SUPERVISOR->value))->toBeTrue()
        ->and((int) $supervisor->coordinator_id)->toBe($coordinator->id);
});

test('supervisor photo is required on create', function () {
    $this->seed(RoleSeeder::class);

    $coordinator = User::factory()->create();
    $coordinator->assignRole(Role::OJTCOORDINATOR->value);

    \Livewire\Livewire::actingAs($coordinator)
        ->test('coordinator.supervisors.create')
        ->set('form.first_name', 'Maria')
        ->set('form.last_name', 'Santos')
        ->set('form.email', 'maria.santos@example.com')
        ->set('form.password', 'Santos-2026!')
        ->set('form.password_confirmation', 'Santos-2026!')
        ->set('form.company_name', 'Acme Corp')
        ->call('save')
        ->assertHasErrors(['form.photo' => 'required']);
});

test('supervisor accounts cannot get a short or last-name password', function () {
    $this->seed(RoleSeeder::class);

    $coordinator = User::factory()->create();
    $coordinator->assignRole(Role::OJTCOORDINATOR->value);

    \Livewire\Livewire::actingAs($coordinator)
        ->test('coordinator.supervisors.create')
        ->set('form.last_name', 'Santos')
        ->assertSet('form.password', '')
        ->set('form.first_name', 'Maria')
        ->set('form.email', 'maria.santos@example.com')
        ->set('form.password', 'Sa')
        ->set('form.password_confirmation', 'Sa')
        ->set('form.company_name', 'Acme Corp')
        ->set('form.photo', \Illuminate\Http\UploadedFile::fake()->image('maria.jpg'))
        ->call('save')
        ->assertHasErrors(['form.password']);

    expect(User::query()->where('email', 'maria.santos@example.com')->exists())->toBeFalse();
});
