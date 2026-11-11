<?php

namespace App\Livewire\Forms\Coordinator;

use App\Models\OjtAssignment;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class OjtAssignmentForm extends Form
{
    public ?OjtAssignment $assignment = null;

    public ?int $student_id = null;

    public string $ojt_type = 'internal';

    public ?int $company_supervisor_id = null;

    public ?float $geofence_latitude = null;

    public ?float $geofence_longitude = null;

    public ?int $geofence_radius = null;

    public function rules(): array
    {
        $assignmentId = $this->assignment?->id;

        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('ojt_assignments', 'student_id')->whereNull('deleted_at')->ignore($assignmentId),
            ],
            'ojt_type' => ['required', Rule::in(['internal', 'external'])],
            'company_supervisor_id' => [
                'required',
                'integer',
                Rule::exists('supervisor_profiles', 'user_id')->where('supervisor_type', $this->ojt_type),
            ],
            'geofence_latitude' => ['required', 'numeric', 'between:-90,90'],
            'geofence_longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius' => ['required', 'integer', 'min:10', 'max:5000'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'student_id' => 'student',
            'ojt_type' => 'OJT type',
            'company_supervisor_id' => 'supervisor',
            'geofence_latitude' => 'pinned latitude',
            'geofence_longitude' => 'pinned longitude',
            'geofence_radius' => 'attendance radius',
        ];
    }

    protected function coordinator(): User
    {
        $coordinator = Auth::user();

        abort_unless(
            $coordinator instanceof User && $coordinator->hasRole('ojt_coordinator'),
            403
        );

        return $coordinator;
    }

    protected function ensureManagedStudent(?int $studentId = null): User
    {
        $studentId ??= $this->student_id;

        abort_unless($studentId, 403);

        $student = User::query()
            ->with('studentProfile')
            ->findOrFail($studentId);

        abort_unless((int) $student->coordinator_id === (int) $this->coordinator()->id, 403);

        return $student;
    }

    protected function lockAndEnsureSingleAssignment(User $student, ?int $ignoreAssignmentId = null): void
    {
        User::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();

        $assignmentExists = OjtAssignment::query()
            ->where('student_id', $student->id)
            ->when($ignoreAssignmentId, fn ($query) => $query->where('id', '!=', $ignoreAssignmentId))
            ->exists();

        if ($assignmentExists) {
            throw ValidationException::withMessages([
                'form.student_id' => 'This student already has an active OJT assignment.',
            ]);
        }
    }

    protected function ensureCompanySupervisor(?int $supervisorId = null): User
    {
        $supervisorId ??= $this->company_supervisor_id;

        abort_unless($supervisorId, 403);

        $supervisor = User::query()
            ->availableToCoordinator($this->coordinator()->id)
            ->findOrFail($supervisorId);

        abort_unless($supervisor->hasRole('supervisor'), 403);
        abort_unless($supervisor->supervisorProfile?->supervisor_type === $this->ojt_type, 403);

        return $supervisor;
    }

    protected function assignmentAuditData(?OjtAssignment $assignment = null): array
    {
        $assignment ??= $this->assignment?->load([
            'student.studentProfile',
            'companySupervisor',
        ]);

        return [
            'student_id' => $assignment?->student_id,
            'student_name' => $assignment?->student?->display_name,
            'student_number' => $assignment?->student?->studentProfile?->student_id,
            'ojt_type' => $assignment?->ojt_type,
            'company_supervisor_id' => $assignment?->company_supervisor_id,
            'company_supervisor_name' => $assignment?->companySupervisor?->display_name,
            'geofence_latitude' => $assignment?->geofence_latitude,
            'geofence_longitude' => $assignment?->geofence_longitude,
            'geofence_radius' => $assignment?->geofence_radius,
        ];
    }

    protected function assignmentLabel(OjtAssignment $assignment): string
    {
        $studentName = $assignment->student?->display_name ?? 'Student';
        $assignee = $assignment->companySupervisor?->display_name ?? 'Supervisor';

        return $studentName.' -> '.$assignee;
    }

    public function setAssignment(OjtAssignment $assignment): void
    {
        $assignment->load([
            'student.studentProfile',
            'companySupervisor',
        ]);

        $this->ensureManagedStudent($assignment->student_id);

        $this->assignment = $assignment;
        $this->student_id = $assignment->student_id;
        $this->ojt_type = $assignment->ojt_type;
        $this->company_supervisor_id = $assignment->company_supervisor_id;
        $this->geofence_latitude = $assignment->geofence_latitude;
        $this->geofence_longitude = $assignment->geofence_longitude;
        $this->geofence_radius = $assignment->geofence_radius;

        $this->resetValidation();
    }

    public function store(): void
    {
        $this->validate();

        $student = $this->ensureManagedStudent();

        $this->ensureCompanySupervisor();

        DB::transaction(function () use ($student) {
            $this->lockAndEnsureSingleAssignment($student);

            $assignment = OjtAssignment::create([
                'student_id' => $student->id,
                'ojt_type' => $this->ojt_type,
                'company_supervisor_id' => $this->company_supervisor_id,
                'geofence_latitude' => $this->geofence_latitude,
                'geofence_longitude' => $this->geofence_longitude,
                'geofence_radius' => $this->geofence_radius,
            ]);

            $assignment = $assignment->fresh([
                'student.studentProfile',
                'companySupervisor',
            ]);

            AuditLogger::log(
                module: 'OJT Assignments',
                action: 'created',
                target: $assignment,
                targetLabel: $this->assignmentLabel($assignment),
                description: 'Created an OJT assignment.',
                newValues: $this->assignmentAuditData($assignment),
            );

            $assignment->student?->notify(new SystemNotification(
                'OJT assignment created', 'Supervisor: '.($assignment->companySupervisor?->display_name ?? 'Not set'), route('student.dashboard'), 'briefcase',
            ));
            $assignment->companySupervisor?->notify(new SystemNotification(
                'New trainee assigned to you', $assignment->student?->display_name, route('supervisor.assigned-students'), 'user-group',
            ));
        });

        $this->resetForm();

        Flux::toast(
            variant: 'success',
            heading: 'Assignment created.',
            text: 'The student assignment has been added successfully.'
        );

        Flux::modal('create-ojt-assignment')->close();
    }

    public function update(): void
    {
        if (! $this->assignment) {
            return;
        }

        $this->ensureManagedStudent($this->assignment->student_id);

        $this->validate();

        $student = $this->ensureManagedStudent();

        $this->ensureCompanySupervisor();

        DB::transaction(function () use ($student) {
            $this->lockAndEnsureSingleAssignment($student, $this->assignment?->id);

            $assignment = $this->assignment->load([
                'student.studentProfile',
                'companySupervisor',
            ]);

            $oldValues = $this->assignmentAuditData($assignment);

            $assignment->update([
                'student_id' => $student->id,
                'ojt_type' => $this->ojt_type,
                'company_supervisor_id' => $this->company_supervisor_id,
                'geofence_latitude' => $this->geofence_latitude,
                'geofence_longitude' => $this->geofence_longitude,
                'geofence_radius' => $this->geofence_radius,
            ]);

            $assignment = $assignment->fresh([
                'student.studentProfile',
                'companySupervisor',
            ]);

            AuditLogger::log(
                module: 'OJT Assignments',
                action: 'updated',
                target: $assignment,
                targetLabel: $this->assignmentLabel($assignment),
                description: 'Updated an OJT assignment.',
                oldValues: $oldValues,
                newValues: $this->assignmentAuditData($assignment),
            );

            $assignment->student?->notify(new SystemNotification(
                'OJT assignment updated', 'Supervisor: '.($assignment->companySupervisor?->display_name ?? 'Not set'), route('student.dashboard'), 'briefcase',
            ));
            $assignment->companySupervisor?->notify(new SystemNotification(
                'Trainee assignment updated', $assignment->student?->display_name, route('supervisor.assigned-students'), 'user-group',
            ));
        });

        Flux::toast(
            variant: 'success',
            heading: 'Assignment updated.',
            text: 'The student assignment has been updated successfully.'
        );

        Flux::modal('edit-ojt-assignment-'.$this->assignment->id)->close();
    }

    public function resetForm(): void
    {
        $this->assignment = null;
        $this->student_id = null;
        $this->ojt_type = 'internal';
        $this->company_supervisor_id = null;
        $this->geofence_latitude = null;
        $this->geofence_longitude = null;
        $this->geofence_radius = null;

        $this->resetValidation();
    }
}
