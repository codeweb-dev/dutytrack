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

test('only the four supported roles are seeded and professor routes are removed', function () {
    $this->seed(RoleSeeder::class);

    expect(PermissionRole::query()->pluck('name')->sort()->values()->all())
        ->toBe(['admin', 'ojt_coordinator', 'student', 'supervisor']);

    $admin = User::factory()->create();
    $admin->assignRole(Role::ADMIN->value);
    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()->assertSee('Supervisors')->assertDontSee('Professors')->assertDontSee('Company Supervisors');
    $this->get('/admin/professors')->assertNotFound();
    $this->get('/professor/dashboard')->assertNotFound();
});

test('legacy staff migrate without losing accounts assignments profiles or messages', function () {
    $migration = require database_path('migrations/2026_10_19_000001_consolidate_supervisor_roles.php');
    $migration->down();

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
