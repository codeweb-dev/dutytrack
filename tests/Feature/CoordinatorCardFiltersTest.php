<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

beforeEach(function () {
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }

    $this->coordinator = User::factory()->coordinator()->create();
    $this->done = User::factory()->student()->create(['coordinator_id' => $this->coordinator->id, 'email' => 'done@example.com']);
    $this->ongoing = User::factory()->student()->create(['coordinator_id' => $this->coordinator->id, 'email' => 'ongoing@example.com']);
    $this->actingAs($this->coordinator);
});

test('certificate cards filter completed and in-progress students', function () {
    $this->done->studentProfile->update(['ojt_required_hours' => 8]);
    $this->ongoing->studentProfile->update(['ojt_required_hours' => 16]);

    foreach ([$this->done, $this->ongoing] as $student) {
        $assignment = OjtAssignment::create(['student_id' => $student->id, 'ojt_type' => 'internal', 'company_supervisor_id' => User::factory()->companySupervisor()->create()->id]);
        AttendanceLog::create(['student_id' => $student->id, 'ojt_assignment_id' => $assignment->id, 'attendance_date' => now()->toDateString(), 'hours_rendered' => 8, 'status' => 'present']);
    }

    Livewire::test('pages::coordinator.certificates')
        ->assertSet('summary', ['total' => 2, 'completed' => 1, 'in_progress' => 1])
        ->set('status', 'completed')->assertSee('done@example.com')->assertDontSee('ongoing@example.com')
        ->set('status', 'in_progress')->assertSee('ongoing@example.com')->assertDontSee('done@example.com');
});

test('requirement cards filter complete, pending and for-checking students', function () {
    StudentRequirement::ensureForStudent($this->done);
    StudentRequirement::ensureForStudent($this->ongoing);
    $this->done->studentRequirements()->update(['is_completed' => true]);
    $this->ongoing->studentRequirements()->first()->update(['submitted_at' => now()]);

    Livewire::test('pages::coordinator.requirements')
        ->set('status', 'complete')->assertSee('done@example.com')->assertDontSee('ongoing@example.com')
        ->set('status', 'pending')->assertSee('ongoing@example.com')->assertDontSee('done@example.com')
        ->set('status', 'for_checking')->assertSee('ongoing@example.com')->assertDontSee('done@example.com');
});

test('every redesigned page renders for each card filter', function (string $page, string $property, array $values) {
    $component = Livewire::test("pages::coordinator.{$page}")->assertOk();

    foreach ($values as $value) {
        $component->set($property, $value)->assertOk();
    }
})->with([
    ['attendance', 'status', ['present', 'late', 'absent', 'incomplete', '']],
    ['evaluations', 'status', ['pending', 'submitted', 'reviewed', '']],
    ['announcements', 'type', ['info', 'warning', 'urgent', 'maintenance', '']],
    ['audit-logs', 'period', ['today', 'week', 'month', '']],
    ['archive', 'type', ['assignments', 'announcements', 'logs', 'users']],
]);

test('student requirement cards filter the checklist', function () {
    $requirements = StudentRequirement::ensureForStudent($this->done);
    $requirements[0]->update(['is_completed' => true]);
    $requirements[1]->update(['submitted_at' => now()]);
    $this->actingAs($this->done);

    $keys = fn ($component) => collect($component->instance()->checklistSteps)->flatMap(fn ($step) => $step['items']->pluck('key'))->all();

    $component = Livewire::test('pages::student.requirements');
    expect($keys($component->set('status', 'approved')))->toBe([$requirements[0]->key])
        ->and($keys($component->set('status', 'for_checking')))->toBe([$requirements[1]->key])
        ->and($keys($component->set('status', 'missing')))->toHaveCount($requirements->count() - 2)
        ->and($keys($component->set('status', '')))->toHaveCount($requirements->count());
});

test('attendance day details search students and sort by last name', function () {
    $this->done->update(['first_name' => 'Zed', 'middle_name' => null, 'last_name' => 'Abad', 'name' => 'Zed Abad']);
    $this->ongoing->update(['first_name' => 'Amy', 'middle_name' => null, 'last_name' => 'Zamora', 'name' => 'Amy Zamora']);

    foreach ([$this->ongoing, $this->done] as $student) {
        $assignment = OjtAssignment::create(['student_id' => $student->id, 'ojt_type' => 'internal', 'company_supervisor_id' => User::factory()->companySupervisor()->create()->id]);
        AttendanceLog::create(['student_id' => $student->id, 'ojt_assignment_id' => $assignment->id, 'attendance_date' => '2026-11-05', 'hours_rendered' => 8, 'status' => 'present']);
    }

    $component = Livewire::test('pages::coordinator.attendance')->call('showAttendanceDay', '2026-11-05');

    expect($component->instance()->selectedAttendance->pluck('student_id')->all())->toBe([$this->done->id, $this->ongoing->id]);

    $component->set('search', 'zamo');

    expect($component->instance()->selectedAttendance->pluck('student_id')->all())->toBe([$this->ongoing->id]);
});
