<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\AttendanceLog;
use App\Models\DutyHourLog;
use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Models\ProfessorProfile;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DemoOrganizationSeeder extends Seeder
{
    protected const COORDINATOR_COUNT = 10;

    protected const PROFESSOR_COUNT = 15;

    protected const SUPERVISOR_COUNT = 15;

    protected const STUDENT_COUNT = 120;

    protected string $defaultPassword = 'labrague123';

    public function run(): void
    {
        $coordinators = $this->seedCoordinators();
        $professors = $this->seedProfessors($coordinators);
        $supervisors = $this->seedSupervisors($coordinators);

        $professorsByCoordinator = $professors->groupBy('coordinator_id');
        $supervisorsByCoordinator = $supervisors->groupBy('coordinator_id');

        foreach (range(1, self::STUDENT_COUNT) as $index) {
            $coordinator = $coordinators[($index - 1) % $coordinators->count()];

            $availableProfessors = $professorsByCoordinator->get($coordinator->id, collect());
            $availableSupervisors = $supervisorsByCoordinator->get($coordinator->id, collect());

            $professor = $availableProfessors->isNotEmpty()
                ? $availableProfessors[($index - 1) % $availableProfessors->count()]
                : $professors[($index - 1) % $professors->count()];

            $supervisor = $availableSupervisors->isNotEmpty()
                ? $availableSupervisors[($index - 1) % $availableSupervisors->count()]
                : $supervisors[($index - 1) % $supervisors->count()];

            $student = $this->createStudent($index, $coordinator);
            $assignment = $this->createAssignment($student, $professor, $supervisor, $index);

            $this->seedStudentActivity($student, $assignment, $professor, $supervisor, $index);
        }
    }

    protected function seedCoordinators(): Collection
    {
        return collect(range(1, self::COORDINATOR_COUNT))->map(function (int $index) {
            $user = User::query()->updateOrCreate(
                ['email' => sprintf('coordinator%02d@demo.test', $index)],
                [
                    'name' => sprintf('OJT Coordinator %02d', $index),
                    'first_name' => 'Coordinator',
                    'last_name' => sprintf('%02d', $index),
                    'contact_number' => sprintf('0917000%04d', $index),
                    'email_verified_at' => now(),
                    'status' => 'Active',
                    'password' => Hash::make($this->defaultPassword),
                ],
            );

            $user->assignRole(Role::OJTCOORDINATOR->value);

            return $user->fresh();
        });
    }

    protected function seedProfessors(Collection $coordinators): Collection
    {
        $departments = [
            'Computer Studies',
            'Information Systems',
            'Software Engineering',
            'Data Analytics',
            'Business Technology',
        ];

        return collect(range(1, self::PROFESSOR_COUNT))->map(function (int $index) use ($coordinators, $departments) {
            $coordinator = $coordinators[($index - 1) % $coordinators->count()];

            $user = User::query()->updateOrCreate(
                ['email' => sprintf('professor%02d@demo.test', $index)],
                [
                    'name' => sprintf('Professor %02d', $index),
                    'first_name' => 'Professor',
                    'last_name' => sprintf('%02d', $index),
                    'contact_number' => sprintf('0928000%04d', $index),
                    'email_verified_at' => now(),
                    'status' => 'Active',
                    'coordinator_id' => $coordinator->id,
                    'password' => Hash::make($this->defaultPassword),
                ],
            );

            if ($user->coordinator_id !== $coordinator->id) {
                $user->update(['coordinator_id' => $coordinator->id]);
            }

            $user->assignRole(Role::PROFESSOR->value);

            ProfessorProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => sprintf('PRF-%03d', $index),
                    'department' => $departments[($index - 1) % count($departments)],
                    'position' => 'OJT Professor',
                ],
            );

            return $user->fresh();
        });
    }

    protected function seedSupervisors(Collection $coordinators): Collection
    {
        $companies = [
            ['name' => 'NovaTech Solutions', 'address' => 'Taguig City, Metro Manila'],
            ['name' => 'Harbor Systems Inc.', 'address' => 'Makati City, Metro Manila'],
            ['name' => 'BrightPath Digital', 'address' => 'Pasig City, Metro Manila'],
            ['name' => 'Vertex Cloudworks', 'address' => 'Quezon City, Metro Manila'],
            ['name' => 'BlueGrid Enterprises', 'address' => 'Cebu City, Cebu'],
        ];

        return collect(range(1, self::SUPERVISOR_COUNT))->map(function (int $index) use ($coordinators, $companies) {
            $coordinator = $coordinators[($index - 1) % $coordinators->count()];
            $company = $companies[($index - 1) % count($companies)];

            $user = User::query()->updateOrCreate(
                ['email' => sprintf('supervisor%02d@demo.test', $index)],
                [
                    'name' => sprintf('Company Supervisor %02d', $index),
                    'first_name' => 'Supervisor',
                    'last_name' => sprintf('%02d', $index),
                    'contact_number' => sprintf('0939000%04d', $index),
                    'email_verified_at' => now(),
                    'status' => 'Active',
                    'coordinator_id' => $coordinator->id,
                    'password' => Hash::make($this->defaultPassword),
                ],
            );

            if ($user->coordinator_id !== $coordinator->id) {
                $user->update(['coordinator_id' => $coordinator->id]);
            }

            $user->assignRole(Role::COMPANYSUPERVISOR->value);

            SupervisorProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'supervisor_id' => sprintf('SUP-%03d', $index),
                    'company_name' => $company['name'],
                    'company_address' => $company['address'],
                ],
            );

            return $user->fresh();
        });
    }

    protected function createStudent(int $index, User $coordinator): User
    {
        $courses = [
            'BS Information Technology',
            'BS Computer Science',
            'BS Information Systems',
        ];

        $sections = ['A', 'B', 'C', 'D'];
        $course = $courses[($index - 1) % count($courses)];
        $yearLevel = ((($index - 1) % 2) + 3).'th Year';

        $student = User::query()->updateOrCreate(
            ['email' => sprintf('student%03d@demo.test', $index)],
            [
                'name' => sprintf('Student %03d', $index),
                'first_name' => 'Student',
                'last_name' => sprintf('%03d', $index),
                'contact_number' => sprintf('0945000%04d', $index),
                'email_verified_at' => now(),
                'status' => 'Active',
                'coordinator_id' => $coordinator->id,
                'password' => Hash::make($this->defaultPassword),
            ],
        );

        if ($student->coordinator_id !== $coordinator->id) {
            $student->update(['coordinator_id' => $coordinator->id]);
        }

        $student->assignRole(Role::STUDENT->value);

        StudentProfile::query()->updateOrCreate(
            ['user_id' => $student->id],
            [
                'student_id' => sprintf('2026-%04d', $index),
                'course' => $course,
                'year_level' => $yearLevel,
                'section' => $sections[($index - 1) % count($sections)],
                'ojt_required_hours' => 486,
            ],
        );

        return $student->fresh();
    }

    protected function createAssignment(User $student, User $professor, User $supervisor, int $index): OjtAssignment
    {
        $ojtType = $index % 2 === 0 ? 'external' : 'internal';
        $status = match (true) {
            $index % 9 === 0 => 'pending',
            $index % 7 === 0 => 'completed',
            default => 'active',
        };

        $startDate = now()->subDays(20 + ($index % 45));
        $endDate = $status === 'completed' ? $startDate->copy()->addDays(35 + ($index % 10)) : null;

        return OjtAssignment::query()->updateOrCreate(
            ['student_id' => $student->id],
            [
                'ojt_type' => $ojtType,
                'professor_id' => $ojtType === 'internal' ? $professor->id : null,
                'company_supervisor_id' => $ojtType === 'external' ? $supervisor->id : null,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'required_hours' => 486,
                'status' => $status,
                'remarks' => 'Demo seeded assignment for development and dashboard previews.',
            ],
        );
    }

    protected function seedStudentActivity(
        User $student,
        OjtAssignment $assignment,
        User $professor,
        User $supervisor,
        int $index
    ): void {
        $attendanceEntries = match ($assignment->status) {
            'pending' => 2,
            'completed' => 14,
            default => 10 + ($index % 3),
        };

        foreach (range(0, $attendanceEntries - 1) as $offset) {
            $date = now()->subDays($attendanceEntries - $offset + ($index % 5))->startOfDay();
            $late = $offset % 6 === 0;
            $incomplete = $assignment->status === 'pending' && $offset === 0;
            $hoursRendered = $incomplete ? 4.0 : ($late ? 7.5 : 8.0);

            $attendance = AttendanceLog::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'attendance_date' => $date->toDateString(),
                ],
                [
                    'ojt_assignment_id' => $assignment->id,
                    'time_in' => $date->copy()->setTime($late ? 9 : 8, $late ? 30 : 0),
                    'time_out' => $date->copy()->setTime(17, 0),
                    'break_minutes' => 60,
                    'hours_rendered' => $hoursRendered,
                    'status' => $incomplete ? 'incomplete' : ($late ? 'late' : 'present'),
                    'source' => 'manual',
                    'remarks' => 'Seeded attendance log.',
                    'is_flagged' => $offset % 8 === 0,
                    'flag_reason' => $offset % 8 === 0 ? 'Requires supervisor review.' : null,
                    'reviewed_by' => $supervisor->id,
                ],
            );

            $approvedStatus = $assignment->status === 'pending' && $offset < 2
                ? 'pending'
                : ($offset % 10 === 0 ? 'rejected' : 'approved');

            DutyHourLog::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'ojt_assignment_id' => $assignment->id,
                    'log_date' => $date->toDateString(),
                ],
                [
                    'attendance_log_id' => $attendance->id,
                    'hours_rendered' => $hoursRendered,
                    'approved_hours' => $approvedStatus === 'approved' ? $hoursRendered : 0,
                    'status' => $approvedStatus,
                    'remarks' => 'Seeded duty hour entry.',
                    'approved_by' => $approvedStatus === 'approved' ? $supervisor->id : null,
                    'approved_at' => $approvedStatus === 'approved' ? $date->copy()->setTime(18, 0) : null,
                ],
            );
        }

        Evaluation::query()
            ->where('student_id', $student->id)
            ->where('ojt_assignment_id', $assignment->id)
            ->delete();

        if ($assignment->ojt_type === 'internal') {
            $this->seedEvaluation($student, $assignment, $professor, 'professor', $index);
        } else {
            $this->seedEvaluation($student, $assignment, $supervisor, 'company_supervisor', $index + 1);
        }
    }

    protected function seedEvaluation(
        User $student,
        OjtAssignment $assignment,
        User $evaluator,
        string $type,
        int $index
    ): void {
        $status = match (true) {
            $index % 8 === 0 => 'pending',
            $index % 5 === 0 => 'reviewed',
            default => 'submitted',
        };

        Evaluation::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'ojt_assignment_id' => $assignment->id,
                'evaluator_id' => $evaluator->id,
                'evaluator_type' => $type,
            ],
            [
                'evaluation_date' => $status === 'pending' ? null : now()->subDays(2 + ($index % 12))->toDateString(),
                'rating' => $status === 'pending' ? 0 : 88 + ($index % 12) / 10,
                'remarks' => 'Seeded evaluation for demo dashboards and development previews.',
                'status' => $status,
            ],
        );
    }
}
