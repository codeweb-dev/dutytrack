<?php

use App\Enums\Role as AppRole;
use App\Models\OjtAssignment;
use App\Models\StudentTask;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

beforeEach(function () {
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
});

function createTaskFor(User $student, User $supervisor, OjtAssignment $assignment, string $status = 'pending'): StudentTask
{
    return StudentTask::create([
        'student_id' => $student->id,
        'assigned_by' => $supervisor->id,
        'ojt_assignment_id' => $assignment->id,
        'title' => 'Weekly report',
        'priority' => 'normal',
        'status' => $status,
        'sort_order' => 0,
    ]);
}

test('tasks follow the trainee to their new supervisor', function () {
    $student = User::factory()->student()->create();
    $oldSupervisor = User::factory()->companySupervisor()->create();
    $newSupervisor = User::factory()->companySupervisor()->create();
    $assignment = OjtAssignment::create(['student_id' => $student->id, 'ojt_type' => 'external', 'company_supervisor_id' => $oldSupervisor->id]);
    $task = createTaskFor($student, $oldSupervisor, $assignment);

    $assignment->update(['company_supervisor_id' => $newSupervisor->id]);

    expect(Livewire::actingAs($newSupervisor)->test('pages::supervisor.tasks')->instance()->tasks->pluck('id')->all())->toBe([$task->id]);
    expect(Livewire::actingAs($oldSupervisor)->test('pages::supervisor.tasks')->instance()->tasks)->toBeEmpty();

    Livewire::actingAs($student)->test('pages::student.tasks')->call('sortTask', (string) $task->id, 0, 'in_review');

    expect($newSupervisor->notifications()->count())->toBe(1);
    expect($oldSupervisor->notifications()->count())->toBe(0);
});

test('students cannot approve or un-approve their own tasks', function () {
    $student = User::factory()->student()->create();
    $supervisor = User::factory()->companySupervisor()->create();
    $assignment = OjtAssignment::create(['student_id' => $student->id, 'ojt_type' => 'external', 'company_supervisor_id' => $supervisor->id]);
    $pending = createTaskFor($student, $supervisor, $assignment);
    $done = createTaskFor($student, $supervisor, $assignment, 'done');

    Livewire::actingAs($student)->test('pages::student.tasks')->call('sortTask', (string) $pending->id, 0, 'done')->assertDispatched('toast-show');
    Livewire::actingAs($student)->test('pages::student.tasks')->call('sortTask', (string) $pending->id, 0, 'revision')->assertDispatched('toast-show');
    Livewire::actingAs($student)->test('pages::student.tasks')->call('sortTask', (string) $done->id, 0, 'in_progress')->assertDispatched('toast-show');

    expect($pending->fresh()->status)->toBe('pending');
    expect($done->fresh()->status)->toBe('done');
});

test('a new assignment takes over unfinished tasks from an archived one', function () {
    $coordinator = User::factory()->create();
    $coordinator->assignRole(AppRole::OJTCOORDINATOR->value);
    $student = User::factory()->student()->create(['coordinator_id' => $coordinator->id]);
    $oldSupervisor = User::factory()->companySupervisor()->create();
    $newSupervisor = User::factory()->companySupervisor()->create();
    $old = OjtAssignment::create(['student_id' => $student->id, 'ojt_type' => 'external', 'company_supervisor_id' => $oldSupervisor->id]);
    $open = createTaskFor($student, $oldSupervisor, $old);
    $done = createTaskFor($student, $oldSupervisor, $old, 'done');
    $old->delete();

    Livewire::actingAs($coordinator)->test('coordinator.ojt-assignments.create')
        ->set('form.student_id', $student->id)
        ->set('form.ojt_type', 'external')
        ->set('form.company_supervisor_id', $newSupervisor->id)
        ->set('form.geofence_latitude', 14.599512)
        ->set('form.geofence_longitude', 120.984222)
        ->set('form.geofence_radius', 100)
        ->call('save')
        ->assertHasNoErrors();

    $new = OjtAssignment::query()->where('student_id', $student->id)->sole();

    expect($open->fresh()->ojt_assignment_id)->toBe($new->id);
    expect($done->fresh()->ojt_assignment_id)->toBe($old->id);
});
