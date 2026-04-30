<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureStudentAttendanceRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

function createStudentWithAssignment(): User
{
    ensureStudentAttendanceRolesExist();

    $student = User::factory()->student()->create();
    $professor = User::factory()->professor()->create();

    OjtAssignment::create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'professor_id' => $professor->id,
        'start_date' => '2026-04-20',
        'required_hours' => 486,
        'status' => 'active',
    ]);

    return $student;
}

test('students can view their attendance page', function () {
    $student = createStudentWithAssignment();

    $this->actingAs($student)
        ->get(route('student.attendance'))
        ->assertOk()
        ->assertSee('My Attendance')
        ->assertSee('Clock In');
});

test('students can clock in and clock out for attendance', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 08:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->call('clockIn')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance)->not->toBeNull();
    expect($attendance->status)->toBe('incomplete');
    expect($attendance->time_in->format('H:i'))->toBe('08:00');

    Carbon::setTestNow(Carbon::parse('2026-04-30 17:00:00'));

    Livewire::test('pages::student.attendance')
        ->set('break_minutes', 60)
        ->call('clockOut')
        ->assertHasNoErrors();

    $attendance->refresh();

    expect($attendance->status)->toBe('present');
    expect($attendance->time_out->format('H:i'))->toBe('17:00');
    expect((float) $attendance->hours_rendered)->toBe(8.0);
});
