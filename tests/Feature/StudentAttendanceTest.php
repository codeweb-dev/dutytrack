<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
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
        ->assertSee('Morning Time In')
        ->assertSee('Morning Out')
        ->assertSee('Afternoon In')
        ->assertSee('Afternoon Out')
        ->assertSee('View Map')
        ->assertSee('Time-in Location Map')
        ->assertSee('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', false)
        ->assertSee('wire:ignore', false)
        ->assertSee('data-attendance-location-picker-map', false)
        ->assertSee('https://tile.openstreetmap.org/{z}/{x}/{y}.png', false)
        ->assertSee('Accept the location permission request', false)
        ->assertSee('map.flyTo(point, 17', false)
        ->assertSee('capturedTimeInFullscreenMap', false)
        ->assertSee('modal-show', false)
        ->assertSee('attendance-location-found', false);
});

test('students can record morning and afternoon attendance', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->set('time_in_location_accuracy', 12)
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance)->not->toBeNull();
    expect($attendance->status)->toBe('incomplete');
    expect($attendance->morning_time_in->format('H:i'))->toBe('07:00');
    expect($attendance->time_in_latitude)->toBe(14.599512);
    expect($attendance->time_in_longitude)->toBe(120.984222);
    expect($attendance->time_in_location_accuracy)->toBe(12);

    Carbon::setTestNow(Carbon::parse('2026-04-30 12:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 13:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 17:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    $attendance->refresh();

    expect($attendance->status)->toBe('present');
    expect($attendance->morning_time_out->format('H:i'))->toBe('12:00');
    expect($attendance->afternoon_time_in->format('H:i'))->toBe('13:00');
    expect($attendance->afternoon_time_out->format('H:i'))->toBe('17:00');
    expect((float) $attendance->hours_rendered)->toBe(9.0);

    $dutyHourLog = DutyHourLog::query()->where('attendance_log_id', $attendance->id)->first();

    expect($dutyHourLog)->not->toBeNull();
    expect($dutyHourLog->status)->toBe('pending');
    expect((float) $dutyHourLog->hours_rendered)->toBe(9.0);
    expect((float) $dutyHourLog->approved_hours)->toBe(0.0);
});

test('students can bypass attendance time for testing', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 09:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->call('bypassAttendance', 60)
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();
    $dutyHourLog = DutyHourLog::query()->where('attendance_log_id', $attendance->id)->first();

    expect($attendance)->not->toBeNull();
    expect($attendance->morning_time_in->format('H:i'))->toBe('08:00');
    expect($attendance->morning_time_out->format('H:i'))->toBe('09:00');
    expect($attendance->afternoon_time_in->format('H:i'))->toBe('09:00');
    expect($attendance->afternoon_time_out->format('H:i'))->toBe('09:00');
    expect((float) $attendance->hours_rendered)->toBe(1.0);
    expect($dutyHourLog)->not->toBeNull();
    expect($dutyHourLog->status)->toBe('pending');
    expect((float) $dutyHourLog->hours_rendered)->toBe(1.0);
});

test('students can view their own duty hours page', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 09:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->call('bypassAttendance', 60)
        ->assertHasNoErrors();

    $this->get(route('student.duty-hours'))
        ->assertOk()
        ->assertSee('My Duty Hours')
        ->assertSee('1 hr')
        ->assertSee('Pending');
});

test('student attendance renders saved location with laravel maps leaflet', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 08:00:00'));

    $student = createStudentWithAssignment();
    $assignment = OjtAssignment::query()->where('student_id', $student->id)->firstOrFail();

    $attendance = AttendanceLog::create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'attendance_date' => '2026-04-30',
        'morning_time_in' => Carbon::parse('2026-04-30 07:00:00'),
        'time_in_latitude' => 14.599512,
        'time_in_longitude' => 120.984222,
        'time_in_location_accuracy' => 12,
        'hours_rendered' => 0,
        'status' => 'incomplete',
    ]);

    $this->actingAs($student)
        ->get(route('student.attendance'))
        ->assertOk()
        ->assertSee('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', false)
        ->assertSee("L.map('todayAttendanceMap{$attendance->id}')", false)
        ->assertSee('14.599512, 120.984222', false);
});

test('early time in only counts from seven am', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 06:30:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 12:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 13:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 17:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->morning_time_in->format('H:i'))->toBe('06:30');
    expect($attendance->status)->toBe('present');
    expect((float) $attendance->hours_rendered)->toBe(9.0);
});

test('students are marked late after seven am', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:02:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->status)->toBe('late');
    expect($attendance->morning_time_in->format('H:i'))->toBe('07:02');
});

test('overtime controls whether hours continue after five pm', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 12:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 13:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 18:00:00'));

    Livewire::test('pages::student.attendance')
        ->set('overtime_enabled', true)
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->overtime_enabled)->toBeTrue();
    expect((float) $attendance->hours_rendered)->toBe(10.0);
});

test('hours stop at five pm when overtime is not enabled', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 12:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 13:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 18:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->overtime_enabled)->toBeFalse();
    expect((float) $attendance->hours_rendered)->toBe(9.0);
});
