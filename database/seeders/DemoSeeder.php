<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\AttendanceLog;
use App\Models\AttendanceSubmission;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\StudentRequirement;
use App\Models\StudentTask;
use App\Models\User;
use App\Support\Holidays;
use App\Support\PracticumEvaluationForm;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Defense demo data, run by DatabaseSeeder after the coordinators exist:
 *   php artisan migrate:fresh --seed
 *
 * Per coordinator: 5 internal + 5 external active supervisors, plus 2 + 2 inactive ones
 * with no students. Every masterlist student (config/masterlist.php) except RESERVED gets
 * an account; per course, 4 random ones are pending approval (fresh, no data) and 3 are
 * inactive (partial attendance). Every 3rd active student has completed the required
 * hours and has a supervisor evaluation. Requirements are a mix of complete, for
 * checking and pending. Supervisors 2-5 of each type assign one task per board column
 * (revision included) to their students, skipping every 4th; supervisor 1 of each type
 * assigns none, so both empty states have data. All seeded accounts use the password "password".
 */
class DemoSeeder extends Seeder
{
    private const PROGRAMS = [
        'BSCS' => ['program' => 'Computer Science', 'hours' => 300],
        'BSIT' => ['program' => 'Information Technology', 'hours' => 500],
    ];

    // Left out entirely so they can sign up live at the defense (3 BSCS, 3 BSIT).
    private const RESERVED = [
        '2316-02393C', '2316-02394C', '2316-02395C',
        '2316-02101C', '2316-02130C', '2316-02119C',
    ];

    private const PENDING_STUDENTS = 4;

    private const INACTIVE_STUDENTS = 3;

    private const ANNOUNCEMENTS = [
        ['type' => 'urgent', 'title' => 'Submit your OJT requirements', 'message' => 'All students must upload their complete OJT requirements before the end of the month. Incomplete requirements will delay your certificate.'],
        ['type' => 'info', 'title' => 'OJT orientation schedule', 'message' => 'The OJT orientation will be held at the EARIST Cavite Campus AVR on Monday, 8:00 AM. Attendance is required for all incoming trainees.'],
        ['type' => 'warning', 'title' => 'Time in only inside your OJT location', 'message' => 'Attendance is only accepted within your assigned OJT location. Please turn on your location before you time in.'],
        ['type' => 'maintenance', 'title' => 'Scheduled system maintenance', 'message' => 'Duty Track will be unavailable on Saturday from 10:00 PM to 12:00 MN for maintenance. Please record your attendance before then.'],
        ['type' => 'info', 'title' => 'Daily accomplishment reports', 'message' => 'Remember to submit your accomplishment report and skills learned every day after your afternoon time out.'],
    ];

    // EARIST Cavite Campus, GMA.
    private const SCHOOL = ['lat' => 14.2989, 'lng' => 121.0080];

    private const INTERNAL_OFFICES = ['MIS Office', 'Registrar Office', 'Library', 'Accounting Office', 'Guidance Office'];

    private const EXTERNAL_COMPANIES = [
        ['name' => 'Cavite Data Solutions Inc.', 'address' => 'Aguinaldo Hwy, Dasmariñas, Cavite', 'lat' => 14.3294, 'lng' => 120.9367],
        ['name' => 'Imus Tech Hub Corp.', 'address' => 'Nueno Ave, Imus, Cavite', 'lat' => 14.4297, 'lng' => 120.9367],
        ['name' => 'Carmona IT Services', 'address' => 'Governor\'s Dr, Carmona, Cavite', 'lat' => 14.3132, 'lng' => 121.0576],
        ['name' => 'Silang Software Labs', 'address' => 'J.P. Rizal St, Silang, Cavite', 'lat' => 14.2306, 'lng' => 120.9750],
        ['name' => 'Bacoor Network Systems', 'address' => 'Molino Blvd, Bacoor, Cavite', 'lat' => 14.4116, 'lng' => 120.9768],
    ];

    private const TASKS = [
        'Encoded student records into the database',
        'Assisted in troubleshooting office computers',
        'Updated the inventory spreadsheet of IT equipment',
        'Helped configure network cables and switches',
        'Tested the web portal and listed found bugs',
        'Organized and scanned office documents',
        'Installed software updates on workstations',
        'Assisted users with printer and email issues',
        'Created a simple report using MS Excel',
        'Attended a team meeting and took minutes',
    ];

    private const SKILLS = [
        'Data entry accuracy', 'Basic hardware troubleshooting', 'Network cabling',
        'Spreadsheet formulas', 'Software testing', 'Customer service', 'Time management',
        'Documentation', 'Teamwork', 'SQL basics',
    ];

    // One task group per entry, shared by all of a supervisor's tasked students. due = days from today.
    private const STUDENT_TASKS = [
        ['status' => 'pending', 'priority' => 'normal', 'due' => 7, 'title' => 'Prepare the monthly IT equipment inventory', 'description' => 'List every computer, printer and UPS in the office with its property number and condition.'],
        ['status' => 'in_progress', 'priority' => 'high', 'due' => 3, 'title' => 'Encode the new enrollee records', 'description' => 'Encode the forms in the blue folder into the student information system. Double check the student numbers.'],
        ['status' => 'in_review', 'priority' => 'normal', 'due' => 1, 'title' => 'Write a user guide for the office printer', 'description' => 'Short step-by-step guide on printing, scanning and clearing paper jams. Include screenshots.'],
        ['status' => 'revision', 'priority' => 'high', 'due' => 2, 'title' => 'Test the online request form', 'description' => 'Try every field of the request form and list the bugs you find.', 'revision_note' => 'Please add the steps to reproduce each bug and a screenshot. Some items are missing the browser used.'],
        ['status' => 'done', 'priority' => 'low', 'due' => -4, 'title' => 'Label the network cables in the server room', 'description' => 'Label both ends of each cable with the switch port number.'],
        ['status' => 'done', 'priority' => 'normal', 'due' => -9, 'title' => 'Clean up the shared drive folders', 'description' => 'Sort the scanned documents into folders per month and remove duplicates.'],
    ];

    public function run(): void
    {
        $coordinators = User::role(Role::OJTCOORDINATOR->value)->with('coordinatorProfile')->get()
            ->keyBy(fn (User $user) => $user->coordinatorProfile?->program);

        // Only this seeder creates supervisors.
        if (User::role(Role::SUPERVISOR->value)->exists()) {
            $this->command?->warn('Demo data already seeded; run migrate:fresh --seed first to reseed.');

            return;
        }

        DB::transaction(function () use ($coordinators) {
            $password = Hash::make('password');
            // ponytail: one shared sample PDF body; each requirement still gets its own file so resubmits can delete theirs.
            $sampleFile = Pdf::loadHTML('<h1>Sample requirement</h1><p>Demo file generated by DemoSeeder.</p>')->output();

            foreach (self::ANNOUNCEMENTS as $i => $announcement) {
                Announcement::forceCreate([...$announcement, 'created_at' => now()->subDays($i * 3), 'updated_at' => now()->subDays($i * 3)]);
            }

            foreach (self::PROGRAMS as $course => $config) {
                $coordinator = $coordinators->get($config['program'])
                    ?? throw new RuntimeException("No coordinator for {$config['program']}. Run CoordinatorSeeder first.");

                $supervisors = $this->seedSupervisors($coordinator, $password);
                $taskAssignments = [];

                $students = collect(config('masterlist'))
                    ->where('course', $course)
                    ->except(self::RESERVED);

                foreach ($students->keys()->shuffle()->values() as $i => $studentId) {
                    $status = match (true) {
                        $i < self::PENDING_STUDENTS => 'pending',
                        $i < self::PENDING_STUDENTS + self::INACTIVE_STUDENTS => 'inactive',
                        default => 'active',
                    };
                    $student = $this->seedStudent($coordinator, $config['hours'], $studentId, $students[$studentId], $password, $status);

                    // Pending approval = fresh sign-up: no assignment, attendance or requirement files.
                    if ($status === 'pending') {
                        continue;
                    }

                    $completed = $status === 'active' && $i % 3 === 0;
                    ['user' => $supervisor, 'location' => $location, 'tasks' => $tasks] = $supervisors->random();
                    $assignment = $this->assign($student, $supervisor, $location);

                    if ($tasks && $i % 4 !== 1) {
                        $taskAssignments[$supervisor->id][] = $assignment;
                    }
                    [$from, $to] = $this->seedAttendance($assignment, $completed ? $config['hours'] : null);

                    $this->seedRequirements($student, $coordinator, $completed ? 'complete' : collect(['complete', 'checking', 'pending'])->random(), $sampleFile);

                    if ($completed) {
                        $this->seedEvaluation($assignment, $supervisor, $coordinator, $from, $to, $i % 2 === 0 ? 'reviewed' : 'submitted');
                    }
                }

                foreach ($taskAssignments as $supervisorId => $assignments) {
                    $this->seedTasks($supervisorId, $assignments);
                }
            }
        });
    }

    private function seedSupervisors(User $coordinator, string $password)
    {
        $supervisors = collect();

        foreach (['internal', 'external'] as $type) {
            // 6-7 are inactive and never get students.
            for ($n = 1; $n <= 7; $n++) {
                $company = self::EXTERNAL_COMPANIES[($n - 1) % 5];
                $active = $n <= 5;
                $first = fake()->firstName();
                $last = fake()->lastName();

                $user = User::create([
                    'coordinator_id' => $coordinator->id,
                    'name' => "$first $last",
                    'first_name' => $first,
                    'last_name' => $last,
                    'contact_number' => '09'.fake()->numerify('#########'),
                    'email' => $this->demoEmail($last, '###'),
                    'profile_photo_path' => $this->demoPhoto('supervisor'),
                    'password' => $password,
                    'status' => $active ? 'active' : 'inactive',
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
                $user->assignRole(Role::SUPERVISOR->value);

                $user->supervisorProfile()->create($type === 'internal' ? [
                    'supervisor_type' => 'internal',
                    'company_name' => 'EARIST Cavite Campus - '.self::INTERNAL_OFFICES[($n - 1) % 5],
                    'company_address' => 'Brgy. Pulo, General Mariano Alvarez, Cavite',
                    'position_department' => 'Office Head',
                ] : [
                    'supervisor_type' => 'external',
                    'company_name' => $company['name'],
                    'company_address' => $company['address'],
                    'position_department' => 'IT Supervisor',
                ]);

                if ($active) {
                    $supervisors->push(['user' => $user, 'location' => $type === 'internal' ? self::SCHOOL : $company, 'tasks' => $n !== 1]);
                }
            }
        }

        return $supervisors;
    }

    // e.g. "De Ramos" + '##' -> deramos42@gmail.com; unique() avoids clashes on shared last names.
    private function demoEmail(string $lastName, string $digits): string
    {
        return fake()->unique()->numerify(Str::slug($lastName, '').$digits).'@gmail.com';
    }

    // Own copy per user: replacing a photo deletes the old file, so seeded users can't share one.
    private function demoPhoto(string $role): string
    {
        return Storage::disk('public')->putFile("{$role}s/photos", new File(collect(glob(public_path("{$role}/*.png")))->random()));
    }

    private function seedStudent(User $coordinator, int $hours, string $studentId, array $row, string $password, string $status): User
    {
        $user = User::create([
            'coordinator_id' => $coordinator->id,
            'name' => collect([$row['first_name'], $row['middle_name'], $row['last_name']])->filter()->implode(' '),
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'],
            'last_name' => $row['last_name'],
            'contact_number' => isset($row['contact_number']) ? '0'.$row['contact_number'] : null,
            // BSIT rows (and a few BSCS) have no email in the masterlist.
            'email' => $row['email'] ?? $this->demoEmail($row['last_name'], '##'),
            'profile_photo_path' => $this->demoPhoto('student'),
            'password' => $password,
            'status' => $status,
        ]);
        $user->forceFill(['email_verified_at' => now(), 'has_completed_onboarding' => $status !== 'pending'])->save();
        $user->assignRole(Role::STUDENT->value);

        $user->studentProfile()->create([
            'student_id' => $studentId,
            'extension_name' => $row['extension_name'],
            'birthdate' => $row['birthdate'] ?? null,
            'course' => $row['course'],
            'ojt_required_hours' => $hours,
            'address' => $row['address'] ?? null,
            'guardian_name' => $row['guardian_name'] ?? null,
            'guardian_address' => $row['guardian_address'] ?? null,
            'guardian_contact_number' => $row['guardian_contact_number'] ?? null,
        ]);

        StudentRequirement::ensureForStudent($user);

        return $user;
    }

    private function assign(User $student, User $supervisor, array $location): OjtAssignment
    {
        return OjtAssignment::create([
            'student_id' => $student->id,
            'ojt_type' => $supervisor->supervisorProfile->supervisor_type,
            'company_supervisor_id' => $supervisor->id,
            'geofence_latitude' => $location['lat'],
            'geofence_longitude' => $location['lng'],
            'geofence_radius' => 150,
        ]);
    }

    /**
     * Walks weekdays back from yesterday. With $targetHours the student keeps going
     * until the requirement is met; otherwise they get 10-30 days (still in progress).
     */
    private function seedAttendance(OjtAssignment $assignment, ?int $targetHours): array
    {
        $days = $targetHours ? PHP_INT_MAX : random_int(10, 30);
        $total = 0.0;
        $date = Carbon::yesterday();
        $first = $last = null;

        for ($count = 0; $count < $days && (! $targetHours || round($total, 2) < $targetHours); $count++) {
            while ($date->isWeekend() || collect(Holidays::forYear($date->year)['holidays'])->contains('date', $date->toDateString())) {
                $date->subDay();
            }

            $total += $this->seedDay($assignment, $date->copy(), $targetHours ? $targetHours - $total : null);
            $last ??= $date->copy();
            $first = $date->copy();
            $date->subDay();
        }

        return [$first, $last];
    }

    /**
     * complete = all approved; checking = some approved, a few uploaded and waiting;
     * pending = some approved, the rest not uploaded yet.
     */
    private function seedRequirements(User $student, User $coordinator, string $state, string $sampleFile): void
    {
        $requirements = StudentRequirement::where('student_id', $student->id)->orderBy('sort_order')->get();
        $approved = $state === 'complete' ? $requirements->count() : random_int(2, $requirements->count() - 3);

        foreach ($requirements->values() as $i => $requirement) {
            $isApproved = $i < $approved;
            $isUploaded = $isApproved || ($state === 'checking' && $i < $approved + 2);

            if (! $isUploaded) {
                continue;
            }

            $path = "student-requirements/{$student->id}/{$requirement->key}.pdf";
            Storage::disk('local')->put($path, $sampleFile);
            $submittedAt = now()->subDays(random_int(5, 60));

            $requirement->update([
                'file_path' => $path,
                'submitted_at' => $submittedAt,
                'is_completed' => $isApproved,
                'completed_at' => $isApproved ? $submittedAt->copy()->addDay() : null,
                'completed_by' => $isApproved ? $coordinator->id : null,
            ]);
        }
    }

    private function seedTasks(int $supervisorId, array $assignments): void
    {
        foreach (self::STUDENT_TASKS as $task) {
            $groupId = (string) Str::uuid();
            $due = today()->addDays($task['due']);
            $createdAt = today()->subDays(14)->setTime(9, 0);

            foreach ($assignments as $assignment) {
                // ponytail: sort_order left at 0; the board breaks ties by priority, then due date.
                StudentTask::forceCreate([
                    'student_id' => $assignment->student_id,
                    'assigned_by' => $supervisorId,
                    'ojt_assignment_id' => $assignment->id,
                    'task_group_id' => $groupId,
                    'title' => $task['title'],
                    'description' => $task['description'],
                    'revision_note' => $task['revision_note'] ?? null,
                    'status' => $task['status'],
                    'priority' => $task['priority'],
                    'due_date' => $due->toDateString(),
                    'completed_at' => $task['status'] === 'done' ? $due->copy()->subDay()->setTime(16, 0) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function seedEvaluation(OjtAssignment $assignment, User $supervisor, User $coordinator, Carbon $from, Carbon $to, string $status): void
    {
        $ratings = PracticumEvaluationForm::defaultRatings();
        array_walk_recursive($ratings, function (&$score) {
            $score = random_int(3, 5);
        });

        Evaluation::create([
            'student_id' => $assignment->student_id,
            'ojt_assignment_id' => $assignment->id,
            'evaluator_id' => $supervisor->id,
            'evaluator_type' => 'company_supervisor',
            'evaluation_date' => $to->copy()->addDay()->toDateString(),
            'inclusive_from' => $from->toDateString(),
            'inclusive_to' => $to->toDateString(),
            'cooperating_program' => $coordinator->name,
            'ratings' => $ratings,
            'rating' => PracticumEvaluationForm::total($ratings),
            'remarks' => collect(['Very dependable and eager to learn.', 'Shows good work attitude and initiative.', 'Works well with the team. Keep it up!'])->random(),
            'status' => $status,
        ]);
    }

    private function seedDay(OjtAssignment $assignment, Carbon $date, ?float $remaining = null): float
    {
        $roll = random_int(1, 100);
        $status = match (true) {
            $roll <= 70 => 'present',
            $roll <= 82 => 'late',
            $roll <= 90 => 'incomplete',
            default => 'absent',
        };

        $at = fn (int $h, int $m) => $date->copy()->setTime($h, $m);
        $times = match ($status) {
            'present' => [$at(7, random_int(30, 59)), $at(12, random_int(0, 5)), $at(12, random_int(45, 59)), $at(17, random_int(0, 15))],
            'late' => [$at(8, random_int(5, 45)), $at(12, random_int(0, 5)), $at(12, random_int(30, 50)), $at(17, random_int(0, 15))],
            // Half day: morning only, never came back after lunch.
            'incomplete' => [$at(7, random_int(40, 59)), $at(12, random_int(0, 5)), null, null],
            'absent' => [null, null, null, null],
        };
        [$morningIn, $morningOut, $afternoonIn, $afternoonOut] = $times;

        $hours = round(($this->minutes($morningIn, $morningOut, $at(8, 0), $at(12, 0)) + $this->minutes($afternoonIn, $afternoonOut, $at(13, 0), $at(17, 0))) / 60, 2);
        $hours = $remaining === null ? $hours : min($hours, round($remaining, 2));
        $stamp = $morningOut ?? $date->copy()->setTime(17, 0);

        $log = AttendanceLog::forceCreate([
            'student_id' => $assignment->student_id,
            'ojt_assignment_id' => $assignment->id,
            'attendance_date' => $date->toDateString(),
            'morning_time_in' => $morningIn,
            'morning_time_out' => $morningOut,
            'afternoon_time_in' => $afternoonIn,
            'afternoon_time_out' => $afternoonOut,
            'time_in_latitude' => $morningIn ? $assignment->geofence_latitude + random_int(-30, 30) / 100000 : null,
            'time_in_longitude' => $morningIn ? $assignment->geofence_longitude + random_int(-30, 30) / 100000 : null,
            'time_in_location_accuracy' => $morningIn ? random_int(5, 25) : null,
            'hours_rendered' => $hours,
            'status' => $status,
            'created_at' => $morningIn ?? $stamp,
            'updated_at' => $afternoonOut ?? $stamp,
        ]);

        // ~10% of worked days have no report yet, so "Missing Report" has data.
        if ($status !== 'absent' && random_int(1, 10) > 1) {
            AttendanceSubmission::forceCreate([
                'attendance_log_id' => $log->id,
                'student_id' => $assignment->student_id,
                'ojt_assignment_id' => $assignment->id,
                'accomplishment' => collect(self::TASKS)->random(2)->implode(".\n").'.',
                'skills_learned' => collect(self::SKILLS)->random(2)->implode(', '),
                'created_at' => $stamp,
                'updated_at' => $stamp,
            ]);
        }

        return $hours;
    }

    // Same rule as the student attendance page: count 8–12 and 1–5 only (8 hrs max a day).
    private function minutes(?Carbon $in, ?Carbon $out, Carbon $earliestStart, Carbon $latestEnd): int
    {
        if (! $in || ! $out) {
            return 0;
        }

        $start = $in->copy()->max($earliestStart);
        $end = $out->copy()->min($latestEnd);

        return $end->greaterThan($start) ? (int) $start->diffInMinutes($end) : 0;
    }
}
