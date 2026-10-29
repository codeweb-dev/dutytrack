<?php

use App\Enums\Role;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\OjtAssignment;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

test('legacy staff migrate without losing accounts assignments profiles or messages', function () {
    $migration = require database_path('migrations/2026_10_19_000001_consolidate_supervisor_roles.php');
    $migration->down();
    Schema::table('professor_profiles', fn ($table) => $table->string('employee_id')->nullable()->unique());

    PermissionRole::findOrCreate('professor', 'web');
    PermissionRole::findOrCreate('company_supervisor', 'web');
    $professor = User::factory()->create();
    $professor->assignRole('professor');
    $supervisor = User::factory()->create();
    $supervisor->assignRole('company_supervisor');
    $student = User::factory()->create();
    DB::table('professor_profiles')->insert([
        'user_id' => $professor->id, 'employee_id' => 'EMP-123',
        'department' => 'Computing', 'position' => 'Professor',
    ]);
    $assignment = OjtAssignment::query()->create([
        'student_id' => $student->id, 'ojt_type' => 'internal', 'status' => 'active',
    ]);
    DB::table('ojt_assignments')->where('id', $assignment->id)->update(['professor_id' => $professor->id]);
    $conversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id, 'student_id' => $student->id,
        'participant_id' => $professor->id, 'participant_role' => 'professor',
    ]);
    $message = ChatMessage::query()->create([
        'chat_conversation_id' => $conversation->id, 'sender_id' => $professor->id, 'body' => 'Keep this message',
    ]);
    $existingConversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id, 'student_id' => $student->id,
        'participant_id' => $professor->id, 'participant_role' => 'supervisor',
    ]);
    $evaluationId = DB::table('evaluations')->insertGetId([
        'student_id' => $student->id, 'ojt_assignment_id' => $assignment->id,
        'evaluator_id' => $professor->id, 'evaluator_type' => 'professor',
    ]);

    $migration->up();

    expect($professor->fresh()->hasRole('supervisor'))->toBeTrue()
        ->and($supervisor->fresh()->hasRole('supervisor'))->toBeTrue()
        ->and(PermissionRole::query()->whereIn('name', ['professor', 'company_supervisor'])->exists())->toBeFalse()
        ->and($assignment->fresh()->company_supervisor_id)->toBe($professor->id)
        ->and($message->fresh()->chat_conversation_id)->toBe($existingConversation->id)
        ->and($professor->fresh()->supervisorProfile)->not->toBeNull()
        ->and(Schema::hasTable('coordinator_profiles'))->toBeTrue();
    $this->assertDatabaseHas('coordinator_profiles', ['user_id' => $professor->id, 'employee_id' => 'EMP-123']);
    $this->assertDatabaseHas('evaluations', ['id' => $evaluationId, 'evaluator_type' => 'company_supervisor']);
});

test('coordinators can create supervisors owned by their account', function () {
    $this->seed(RoleSeeder::class);

    $coordinator = User::factory()->create();
    $coordinator->assignRole(Role::OJTCOORDINATOR->value);

    \Livewire\Livewire::actingAs($coordinator)
        ->test('coordinator.supervisors.create')
        ->set('form.supervisor_id', 'SUP-9001')
        ->set('form.first_name', 'Maria')
        ->set('form.last_name', 'Santos')
        ->set('form.email', 'maria.santos@example.com')
        ->set('form.password', 'Santos')
        ->set('form.password_confirmation', 'Santos')
        ->set('form.company_name', 'Acme Corp')
        ->call('save')
        ->assertHasNoErrors();

    $supervisor = User::query()->where('email', 'maria.santos@example.com')->first();

    expect($supervisor)->not->toBeNull()
        ->and($supervisor->hasRole(Role::SUPERVISOR->value))->toBeTrue()
        ->and((int) $supervisor->coordinator_id)->toBe($coordinator->id);
});
