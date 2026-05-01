<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
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

function validTimeInSelfie(): UploadedFile
{
    return UploadedFile::fake()->image('time-in-selfie.jpg');
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
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('selfie', validTimeInSelfie())
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->set('time_in_location_accuracy', 12)
        ->call('clockIn')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance)->not->toBeNull();
    expect($attendance->status)->toBe('incomplete');
    expect($attendance->time_in->format('H:i'))->toBe('07:00');
    expect($attendance->time_in_latitude)->toBe(14.599512);
    expect($attendance->time_in_longitude)->toBe(120.984222);
    expect($attendance->time_in_location_accuracy)->toBe(12);
    expect($attendance->time_in_selfie_path)->not->toBeNull();
    Storage::disk('public')->assertExists($attendance->time_in_selfie_path);

    Carbon::setTestNow(Carbon::parse('2026-04-30 17:00:00'));

    Livewire::test('pages::student.attendance')
        ->set('break_minutes', 60)
        ->call('clockOut')
        ->assertHasNoErrors();

    $attendance->refresh();

    expect($attendance->status)->toBe('present');
    expect($attendance->time_out->format('H:i'))->toBe('17:00');
    expect((float) $attendance->hours_rendered)->toBe(9.0);
});

test('early time in only counts from seven am', function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-30 06:30:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('selfie', validTimeInSelfie())
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('clockIn')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 17:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('clockOut')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->time_in->format('H:i'))->toBe('06:30');
    expect($attendance->status)->toBe('present');
    expect((float) $attendance->hours_rendered)->toBe(10.0);
});

test('students are marked late after seven am', function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:02:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('selfie', validTimeInSelfie())
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('clockIn')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->status)->toBe('late');
    expect($attendance->time_in->format('H:i'))->toBe('07:02');
});

test('overtime controls whether hours continue after five pm', function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('selfie', validTimeInSelfie())
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('clockIn')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 18:00:00'));

    Livewire::test('pages::student.attendance')
        ->set('overtime_enabled', true)
        ->call('clockOut')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->overtime_enabled)->toBeTrue();
    expect((float) $attendance->hours_rendered)->toBe(11.0);
});

test('hours stop at five pm when overtime is not enabled', function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:00:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('selfie', validTimeInSelfie())
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('clockIn')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 18:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('clockOut')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->overtime_enabled)->toBeFalse();
    expect((float) $attendance->hours_rendered)->toBe(10.0);
});
