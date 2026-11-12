<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\ChatConversation;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

beforeEach(function () {
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }

    $this->coordinator = User::factory()->coordinator()->create();
    $this->supervisor = User::factory()->companySupervisor()->create(['coordinator_id' => $this->coordinator->id]);
    $this->student = User::factory()->student()->create(['coordinator_id' => $this->coordinator->id]);
    $this->assignment = OjtAssignment::create([
        'student_id' => $this->student->id,
        'ojt_type' => 'external',
        'company_supervisor_id' => $this->supervisor->id,
    ]);
});

test('a supervisor with trainees cannot be archived or set inactive', function () {
    Livewire::actingAs($this->coordinator)->test('coordinator.supervisors.delete', ['supervisor' => $this->supervisor])->call('delete');
    Livewire::actingAs($this->coordinator)->test('pages::coordinator.supervisor-management')->call('setInactive', $this->supervisor->id);

    $supervisor = User::withTrashed()->find($this->supervisor->id);
    expect($supervisor->trashed())->toBeFalse();
    expect($supervisor->status)->toBe('Active');
});

test('archiving a student archives their assignment', function () {
    Livewire::actingAs($this->coordinator)->test('coordinator.student-management.delete', ['student' => $this->student])->call('delete');

    expect(OjtAssignment::find($this->assignment->id))->toBeNull();
    expect(OjtAssignment::withTrashed()->find($this->assignment->id)->trashed())->toBeTrue();
});

test('a supervisor with trainees cannot switch type', function () {
    $this->supervisor->update(['first_name' => 'Ana', 'last_name' => 'Reyes', 'profile_photo_path' => 'supervisors/photos/existing.jpg']);

    Livewire::actingAs($this->coordinator)->test('coordinator.supervisors.edit', ['supervisor' => $this->supervisor])
        ->set('form.supervisor_type', 'internal')
        ->call('save')
        ->assertHasErrors(['form.supervisor_type']);

    expect($this->supervisor->supervisorProfile->fresh()->supervisor_type)->toBe('external');
});

test('an assignment cannot be restored while its supervisor is archived', function () {
    $this->assignment->delete();
    $this->supervisor->delete();

    Livewire::actingAs($this->coordinator)->test('pages::coordinator.archive')->call('restoreAssignment', $this->assignment->id);

    expect(OjtAssignment::withTrashed()->find($this->assignment->id)->trashed())->toBeTrue();
});

test('coordinators only see their own archive and audit logs', function () {
    $otherCoordinator = User::factory()->coordinator()->create();
    $otherCoordinator->delete();
    $mine = AuditLog::create(['actor_id' => $this->coordinator->id, 'module' => 'Students', 'action' => 'updated']);
    $theirs = AuditLog::create(['actor_id' => $otherCoordinator->id, 'module' => 'Students', 'action' => 'updated']);

    $archive = Livewire::actingAs($this->coordinator)->test('pages::coordinator.archive')->instance();
    expect($archive->archivedUsers->pluck('id'))->not->toContain($otherCoordinator->id);

    $logs = Livewire::actingAs($this->coordinator)->test('pages::coordinator.audit-logs')->instance()->auditLogs->pluck('id');
    expect($logs)->toContain($mine->id)->not->toContain($theirs->id);
});

test('students can report on afternoon-only days', function () {
    $log = AttendanceLog::create([
        'student_id' => $this->student->id,
        'ojt_assignment_id' => $this->assignment->id,
        'attendance_date' => now()->toDateString(),
        'afternoon_time_in' => now()->setTime(13, 0),
        'afternoon_time_out' => now()->setTime(17, 0),
        'hours_rendered' => 4,
        'status' => 'incomplete',
    ]);

    $component = Livewire::actingAs($this->student)->test('pages::student.reports-submissions');

    expect($component->instance()->completedAttendances->pluck('id'))->toContain($log->id);
});

test('the old supervisor loses the chat after a reassignment', function () {
    $conversation = ChatConversation::create([
        'ojt_assignment_id' => $this->assignment->id,
        'student_id' => $this->student->id,
        'participant_id' => $this->supervisor->id,
        'participant_role' => 'supervisor',
    ]);

    $this->assignment->update(['company_supervisor_id' => User::factory()->companySupervisor()->create()->id]);

    Livewire::actingAs($this->supervisor)->test('pages::messages')->call('selectConversation', $conversation->id)->assertForbidden();
});

test('changing your password signs out your other sessions', function () {
    DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $this->student->id, 'payload' => '', 'last_activity' => time()]);

    Livewire::actingAs($this->student)->test('pages::settings.password')
        ->set('current_password', 'password')
        ->set('password', 'NEWPASS')
        ->set('password_confirmation', 'NEWPASS')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(DB::table('sessions')->where('id', 'other-device')->exists())->toBeFalse();
});
