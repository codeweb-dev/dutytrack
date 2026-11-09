<?php

use App\Enums\Role as AppRole;
use App\Models\AttendanceLog;
use App\Models\AttendanceSubmission;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
    $mentor = User::factory()->companySupervisor()->create();

    OjtAssignment::create([
        'student_id' => $student->id,
        'ojt_type' => 'internal',
        'company_supervisor_id' => $mentor->id,
        'status' => 'active',
    ]);

    return $student;
}

beforeEach(function () {
    Http::fake(['date.nager.at/*' => Http::response([])]);
});

test('students see public holidays and cannot time in on a holiday', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 08:00:00'));
    Cache::forget('public-holidays-ph-v5-2026');
    Http::fake(['date.nager.at/*' => Http::response([
        ['date' => '2026-04-30', 'localName' => 'Test Holiday', 'name' => 'Test Holiday'],
    ])]);

    $student = createStudentWithAssignment();
    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->assertSee('Test Holiday')
        ->call('recordNextPunch', 'morning_time_in')
        ->assertHasNoErrors();

    expect(AttendanceLog::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('students can view their attendance page', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 08:00:00'));

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
        ->call('recordNextPunch', 'morning_time_in')
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
        ->call('recordNextPunch', 'morning_time_out')
        ->assertHasNoErrors();

    expect((float) $attendance->fresh()->hours_rendered)->toBe(4.0);

    Carbon::setTestNow(Carbon::parse('2026-04-30 13:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'afternoon_time_in')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 17:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'afternoon_time_out')
        ->assertHasNoErrors();

    $attendance->refresh();

    expect($attendance->status)->toBe('present');
    expect($attendance->morning_time_out->format('H:i'))->toBe('12:00');
    expect($attendance->afternoon_time_in->format('H:i'))->toBe('13:00');
    expect($attendance->afternoon_time_out->format('H:i'))->toBe('17:00');
    expect((float) $attendance->hours_rendered)->toBe(8.0);

});

test('students who miss morning in can start an afternoon-only attendance', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 12:59:00'));

    $student = createStudentWithAssignment();
    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->assertSee('Afternoon In starts at 1:00 PM');

    Carbon::setTestNow(Carbon::parse('2026-04-30 13:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'morning_time_in')
        ->assertHasNoErrors();

    expect(AttendanceLog::query()->where('student_id', $student->id)->exists())->toBeFalse();

    Livewire::test('pages::student.attendance')
        ->assertSee('Afternoon Time In')
        ->call('recordNextPunch', 'afternoon_time_in')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->firstOrFail();
    expect($attendance->morning_time_in)->toBeNull();
    expect($attendance->afternoon_time_in->format('H:i'))->toBe('13:00');

    Carbon::setTestNow(Carbon::parse('2026-04-30 16:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'afternoon_time_out')
        ->assertHasNoErrors();

    expect($attendance->fresh()->status)->toBe('incomplete');
    expect((float) $attendance->fresh()->hours_rendered)->toBe(3.0);
});

test('students cannot replay a stale attendance action', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:30:00'));

    $student = createStudentWithAssignment();
    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch', 'morning_time_in')
        ->call('recordNextPunch', 'morning_time_in')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->firstOrFail();
    expect($attendance->morning_time_in->format('H:i'))->toBe('07:30');
    expect($attendance->morning_time_out)->toBeNull();
});

test('students submit accomplishments after completed attendance and download report docs', function () {
    Storage::fake('local');
    Carbon::setTestNow(Carbon::parse('2026-04-30 09:00:00'));

    $student = createStudentWithAssignment();
    $assignment = OjtAssignment::query()->where('student_id', $student->id)->firstOrFail();

    $attendance = AttendanceLog::query()->create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'attendance_date' => '2026-04-30',
        'morning_time_in' => '2026-04-30 08:00:00',
        'morning_time_out' => '2026-04-30 12:00:00',
        'afternoon_time_in' => '2026-04-30 13:00:00',
        'afternoon_time_out' => '2026-04-30 17:00:00',
        'hours_rendered' => 8,
        'status' => 'present',
    ]);

    $this->actingAs($student)
        ->get(route('student.reports-submissions'))
        ->assertOk()
        ->assertSee('Accomplishment Report')
        ->assertSee('Apr 30, 2026')
        ->assertSee('Download Docs');

    $this->actingAs($student);

    Livewire::test('pages::student.reports-submissions')
        ->call('openSubmissionModal', $attendance->id)
        ->set('accomplishment', 'Prepared deployment checklist and encoded inventory updates.')
        ->set('skills_learned', 'Inventory encoding & deployment planning')
        ->set('new_photos', [UploadedFile::fake()->image('site.jpg', 800, 600)])
        ->call('saveSubmission')
        ->assertHasNoErrors();

    $submission = AttendanceSubmission::query()->where('attendance_log_id', $attendance->id)->first();

    expect($submission)->not->toBeNull();
    expect($submission->student_id)->toBe($student->id);
    expect($submission->accomplishment)->toBe('Prepared deployment checklist and encoded inventory updates.');
    expect($submission->skills_learned)->toBe('Inventory encoding & deployment planning');

    $response = $this->get(route('student.reports-submissions.download', [
        'from' => '2026-04-01',
        'to' => '2026-04-30',
    ]))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $path = tempnam(sys_get_temp_dir(), 'docx');
    file_put_contents($path, $response->getContent());
    $zip = new ZipArchive;
    $zip->open($path);
    $xml = $zip->getFromName('word/document.xml');

    expect((new DOMDocument)->loadXML($xml))->toBeTrue();
    expect($xml)
        ->toContain('WEEKLY ACCOMPLISHMENT FORM')
        ->toContain('Prepared deployment checklist and encoded inventory updates.')
        ->toContain('Inventory encoding &amp; deployment planning')
        ->toContain('Thursday')
        ->toContain('WEEKLY EVIDENCE')
        ->toContain('r:embed="rIdPhoto1"');
    expect($zip->getFromName('word/media/photo1.jpeg'))->not->toBeFalse();
    expect($zip->getFromName('word/_rels/document.xml.rels'))->toContain('Target="media/photo1.jpeg"');

    $zip->close();
    unlink($path);
});

test('accomplishment report download rejects malformed date filters', function () {
    $student = createStudentWithAssignment();

    $this->actingAs($student)
        ->get(route('student.reports-submissions.download', ['from' => 'not-a-date']))
        ->assertSessionHasErrors('from');
});

test('accomplishment photos are private to the student and assigned supervisor', function () {
    Storage::fake('local');
    $student = createStudentWithAssignment();
    $assignment = OjtAssignment::query()->where('student_id', $student->id)->firstOrFail();
    $attendance = AttendanceLog::query()->create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'attendance_date' => now()->toDateString(),
        'hours_rendered' => 0,
        'status' => 'incomplete',
    ]);
    $path = 'accomplishment-photos/'.$attendance->id.'/report.jpg';
    Storage::disk('local')->put($path, 'private report photo');
    $submission = AttendanceSubmission::query()->create([
        'attendance_log_id' => $attendance->id,
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'photo_paths' => [$path],
    ]);
    $photo = route('reports.photo', [$submission, 0]);

    $this->actingAs($student)->get($photo)->assertOk();
    $this->actingAs($assignment->companySupervisor)->get($photo)->assertOk();
    $this->actingAs(createStudentWithAssignment())->get($photo)->assertNotFound();
});

test('mentors can read assigned student report submissions', function () {
    $student = createStudentWithAssignment();
    $assignment = OjtAssignment::query()->where('student_id', $student->id)->firstOrFail();
    $mentor = $assignment->companySupervisor;

    $attendance = AttendanceLog::query()->create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'attendance_date' => '2026-04-30',
        'morning_time_in' => '2026-04-30 08:00:00',
        'morning_time_out' => '2026-04-30 12:00:00',
        'afternoon_time_in' => '2026-04-30 13:00:00',
        'afternoon_time_out' => '2026-04-30 17:00:00',
        'hours_rendered' => 8,
        'status' => 'present',
    ]);

    AttendanceSubmission::query()->create([
        'attendance_log_id' => $attendance->id,
        'student_id' => $student->id,
        'ojt_assignment_id' => $assignment->id,
        'accomplishment' => 'Configured workstation and documented tasks.',
    ]);

    $this->actingAs($mentor)
        ->get(route('supervisor.reports-submissions'))
        ->assertOk()
        ->assertSee($student->name)
        ->assertSee('Configured workstation and documented tasks.');
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

test('early time in is credited from eight am', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:30:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch', 'morning_time_in')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 12:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'morning_time_out')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 13:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'afternoon_time_in')
        ->assertHasNoErrors();

    Carbon::setTestNow(Carbon::parse('2026-04-30 17:00:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'afternoon_time_out')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->morning_time_in->format('H:i'))->toBe('07:30');
    expect($attendance->status)->toBe('present');
    expect((float) $attendance->hours_rendered)->toBe(8.0);
});

test('time in at 7:59 and exactly 8 is not late', function () {
    foreach (['07:59', '08:00'] as $time) {
        Carbon::setTestNow(Carbon::parse("2026-04-30 {$time}:00"));

        $student = createStudentWithAssignment();
        $this->actingAs($student);

        Livewire::test('pages::student.attendance')
            ->set('time_in_latitude', 14.599512)
            ->set('time_in_longitude', 120.984222)
            ->call('recordNextPunch', 'morning_time_in')
            ->assertHasNoErrors();

        $attendance = AttendanceLog::query()->where('student_id', $student->id)->firstOrFail();
        expect($attendance->isLate())->toBeFalse();
        expect($attendance->morning_time_in->format('H:i'))->toBe($time);
    }
});

test('students are marked late after eight am', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 08:02:00'));

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch', 'morning_time_in')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->first();

    expect($attendance->status)->toBe('late');
    expect($attendance->morning_time_in->format('H:i'))->toBe('08:02');
});

test('afternoon time out is preserved but credited only through five pm', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 13:00:00'));

    $student = createStudentWithAssignment();
    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'afternoon_time_in')
        ->assertHasNoErrors();

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'afternoon_time_in')
        ->assertHasNoErrors();

    $attendance = AttendanceLog::query()->where('student_id', $student->id)->firstOrFail();
    expect($attendance->afternoon_time_in->format('H:i'))->toBe('13:00');
    expect($attendance->afternoon_time_out)->toBeNull();

    Carbon::setTestNow(Carbon::parse('2026-04-30 17:30:00'));

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'afternoon_time_out')
        ->assertHasNoErrors();

    $attendance->refresh();
    expect($attendance->morning_time_in)->toBeNull();
    expect($attendance->afternoon_time_out->format('H:i'))->toBe('17:30');
    expect((float) $attendance->hours_rendered)->toBe(4.0);
    expect($attendance->status)->toBe('incomplete');
});

test('time in is rejected after five pm', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 18:00:00'));

    $student = createStudentWithAssignment();
    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->assertSee('Attendance Closed')
        ->call('recordNextPunch', 'morning_time_in')
        ->assertHasNoErrors();

    expect(AttendanceLog::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('students cannot record attendance on weekends', function () {
    Carbon::setTestNow(Carbon::parse('2026-05-02 08:00:00')); // Saturday

    $student = createStudentWithAssignment();

    $this->actingAs($student);

    Livewire::test('pages::student.attendance')
        ->assertSee('Rest day')
        ->set('time_in_latitude', 14.5995)
        ->set('time_in_longitude', 120.9842)
        ->call('recordNextPunch', 'morning_time_in');

    expect(AttendanceLog::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('students can only record attendance inside the pinned OJT location', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-30 07:00:00'));

    $student = createStudentWithAssignment();
    $student->ojtAssignment->update([
        'geofence_latitude' => 14.599512,
        'geofence_longitude' => 120.984222,
        'geofence_radius' => 100,
    ]);

    $this->actingAs($student);

    // ~1.1 km north of the pin.
    Livewire::test('pages::student.attendance')
        ->assertSee('Location-restricted attendance')
        ->set('time_in_latitude', 14.609512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch', 'morning_time_in');

    expect(AttendanceLog::query()->where('student_id', $student->id)->exists())->toBeFalse();

    Livewire::test('pages::student.attendance')
        ->call('recordNextPunch', 'morning_time_in');

    expect(AttendanceLog::query()->where('student_id', $student->id)->exists())->toBeFalse();

    // ~50 m from the pin.
    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.599962)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch', 'morning_time_in')
        ->assertHasNoErrors();

    expect(AttendanceLog::query()->where('student_id', $student->id)->exists())->toBeTrue();

    Carbon::setTestNow(Carbon::parse('2026-04-30 12:00:00'));

    Livewire::test('pages::student.attendance')
        ->set('time_in_latitude', 14.609512)
        ->set('time_in_longitude', 120.984222)
        ->call('recordNextPunch', 'morning_time_out');

    expect(AttendanceLog::query()->where('student_id', $student->id)->first()->morning_time_out)->toBeNull();
});

test('students can download their monthly DTR with holidays marked', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-20 08:00:00'));
    Cache::put('public-holidays-ph-v5-2026', ['available' => true, 'holidays' => [
        ['date' => '2026-11-02', 'name' => "All Souls' Day"],
        ['date' => '2026-11-30', 'name' => 'Bonifacio Day'],
    ]]);

    $student = createStudentWithAssignment();
    AttendanceLog::create([
        'student_id' => $student->id,
        'ojt_assignment_id' => $student->ojtAssignment->id,
        'attendance_date' => '2026-11-03',
        'morning_time_in' => '2026-11-03 08:15:00',
        'morning_time_out' => '2026-11-03 12:00:00',
        'afternoon_time_in' => '2026-11-03 13:00:00',
        'afternoon_time_out' => '2026-11-03 17:00:00',
        'hours_rendered' => 7.75,
        'status' => 'incomplete',
    ]);

    $this->actingAs($student)->get(route('student.attendance'))
        ->assertSee(route('student.attendance.dtr', ['month' => '2026-11']), false);

    $data = \App\Support\DtrDocument::data($student, Carbon::parse('2026-11-01'));
    expect($data['rows'][2])->toMatchArray(['arrival' => '8:15 AM', 'departure' => '5:00 PM', 'late' => 15, 'hours' => 7.75])
        ->and($data['rows'][1]['holiday'])->toBe("All Souls' Day")
        ->and($data['rows'][29]['holiday'])->toBe('Bonifacio Day')
        ->and($data['rows'][30]['exists'])->toBeFalse()
        ->and($data['totalHours'])->toBe(7.75);

    $this->get(route('student.attendance.dtr', ['month' => '2026-11']))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'attachment; filename="DTR-'.\Illuminate\Support\Str::slug($student->name).'-2026-11.pdf"');
});
