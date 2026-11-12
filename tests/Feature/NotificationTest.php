<?php

use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\StudentTask;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Support\PracticumEvaluationForm;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('the header bell shows unread notifications and marks them read', function () {
    $user = User::factory()->create();
    $user->notify(new SystemNotification('New task assigned', 'Write the weekly report', route('announcements')));

    $component = Livewire::actingAs($user)->test('notifications')
        ->assertSee('New task assigned')
        ->assertSee('Write the weekly report');

    expect($user->unreadNotifications()->count())->toBe(1);

    $component->call('open', $user->notifications()->first()->id)
        ->assertRedirect(route('announcements'));

    expect($user->unreadNotifications()->count())->toBe(0);
});

test('the bell shows only the 5 latest while the notifications page shows all', function () {
    $user = User::factory()->create();
    foreach (range(1, 6) as $i) {
        $user->notify(new SystemNotification("Notice {$i}"));
    }

    expect(Livewire::actingAs($user)->test('notifications')->instance()->items)->toHaveCount(5);

    $this->actingAs($user)->get(route('notifications'))
        ->assertOk()
        ->assertSee(array_map(fn ($i) => "Notice {$i}", range(1, 6)));
});

test('approving an evaluation notifies that student and the supervisor', function () {
    $this->seed(Database\Seeders\RoleSeeder::class);
    $coordinator = User::factory()->coordinator()->create();
    $student = User::factory()->student()->create(['coordinator_id' => $coordinator->id]);
    $supervisor = User::factory()->companySupervisor()->create();
    $assignment = OjtAssignment::create(['student_id' => $student->id, 'ojt_type' => 'external', 'company_supervisor_id' => $supervisor->id]);
    $evaluation = Evaluation::create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'evaluator_id' => $supervisor->id,
        'evaluator_type' => 'company_supervisor',
        'evaluation_date' => today(),
        'inclusive_from' => today()->subMonth(),
        'inclusive_to' => today(),
        'rating' => 80,
        'ratings' => PracticumEvaluationForm::defaultRatings(4),
        'status' => 'submitted',
    ]);

    Livewire::actingAs($coordinator)->test('pages::coordinator.evaluations')->call('markReviewed', $evaluation->id);

    expect($student->notifications()->first()->data['title'])->toBe('Evaluation released')
        ->and($supervisor->notifications()->first()->data['title'])->toBe('Evaluation approved')
        ->and($coordinator->notifications()->count())->toBe(0);
});

test('moving a task notifies the other side', function () {
    $this->seed(Database\Seeders\RoleSeeder::class);
    $student = User::factory()->student()->create();
    $supervisor = User::factory()->companySupervisor()->create();
    $assignment = OjtAssignment::create(['student_id' => $student->id, 'ojt_type' => 'external', 'company_supervisor_id' => $supervisor->id]);
    $task = StudentTask::create([
        'student_id' => $student->id,
        'assigned_by' => $supervisor->id,
        'ojt_assignment_id' => $assignment->id,
        'task_group_id' => 'group-1',
        'title' => 'Weekly report',
        'priority' => 'normal',
        'status' => 'pending',
        'sort_order' => 0,
    ]);

    Livewire::actingAs($student)->test('pages::student.tasks')->call('sortTask', (string) $task->id, 0, 'in_progress');
    expect($supervisor->notifications()->first()->data['title'])->toBe('Task moved to In Progress');

    // Reordering inside the same column is not a move.
    Livewire::actingAs($student)->test('pages::student.tasks')->call('sortTask', (string) $task->id, 0, 'in_progress');
    expect($supervisor->notifications()->count())->toBe(1);

    Livewire::actingAs($supervisor)->test('pages::supervisor.tasks')->call('sortTaskCard', 'group-1', 0, 'done');
    expect($student->notifications()->first()->data['title'])->toBe('Task approved');
});

test('account approval and requirement review notify the student and coordinator', function () {
    $this->seed(Database\Seeders\RoleSeeder::class);
    Storage::fake('local');
    $coordinator = User::factory()->coordinator()->create();
    $student = User::factory()->student()->create(['coordinator_id' => $coordinator->id, 'status' => 'pending']);

    Livewire::actingAs($coordinator)->test('pages::coordinator.student-management')->call('approve', $student->id);
    expect($student->notifications()->pluck('data')->pluck('title'))->toContain('Account approved');

    $requirement = StudentRequirement::ensureForStudent($student)->first();
    Storage::disk('local')->put('student-requirements/file.pdf', 'pdf');
    $requirement->submitFile('student-requirements/file.pdf');
    expect($coordinator->notifications()->pluck('data')->pluck('title'))->toContain('Requirement submitted');

    Livewire::actingAs($coordinator)
        ->test('coordinator.student-management.requirements', ['student' => $student])
        ->call('approveRequirement', $requirement->id);
    expect($student->notifications()->pluck('data')->pluck('title'))->toContain('Requirement approved');
});

test('a new assignment, a new task and a revision each notify the people involved', function () {
    $this->seed(Database\Seeders\RoleSeeder::class);
    $coordinator = User::factory()->coordinator()->create();
    $student = User::factory()->student()->create(['coordinator_id' => $coordinator->id]);
    $supervisor = User::factory()->companySupervisor()->create(['coordinator_id' => $coordinator->id]);
    $titles = fn (User $user) => $user->notifications()->pluck('data')->pluck('title');

    Livewire::actingAs($coordinator)->test('coordinator.ojt-assignments.create')
        ->set('form.student_id', $student->id)
        ->set('form.ojt_type', 'external')
        ->set('form.company_supervisor_id', $supervisor->id)
        ->set('form.geofence_latitude', 14.599512)
        ->set('form.geofence_longitude', 120.984222)
        ->set('form.geofence_radius', 100)
        ->call('save')
        ->assertHasNoErrors();

    expect($titles($student))->toContain('OJT assignment created')
        ->and($titles($supervisor))->toContain('New trainee assigned to you');

    Livewire::actingAs($supervisor)->test('pages::supervisor.tasks')
        ->set('student_ids', [$student->id])
        ->set('task_title', 'Prepare checklist')
        ->call('saveTask')
        ->assertHasNoErrors();

    expect($titles($student))->toContain('New task assigned');

    $task = StudentTask::query()->where('student_id', $student->id)->firstOrFail();

    Livewire::actingAs($supervisor)->test('pages::supervisor.tasks')
        ->set('revision_task_key', $task->task_group_id)
        ->set('revision_note', 'Please add the blockers.')
        ->call('requestRevision')
        ->assertHasNoErrors();

    expect($titles($student))->toContain('Revision requested')
        ->and($titles($student))->not->toContain('Task moved to Revision');
});
