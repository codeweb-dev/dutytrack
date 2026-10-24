<?php

namespace App\Livewire\Forms\Coordinator;

use App\Models\OjtAssignment;
use App\Models\User;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Form;

class OjtAssignmentForm extends Form
{
    public ?OjtAssignment $assignment = null;

    public ?int $student_id = null;

    public string $ojt_type = 'internal';

    public ?int $company_supervisor_id = null;

    public string $status = 'active';

    public ?string $remarks = null;

    public function rules(): array
    {
        $assignmentId = $this->assignment?->id;

        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('ojt_assignments', 'student_id')->ignore($assignmentId),
            ],
            'ojt_type' => ['required', Rule::in(['internal', 'external'])],
            'company_supervisor_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'status' => ['required', Rule::in(['pending', 'active', 'completed', 'cancelled'])],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'student_id' => 'student',
            'ojt_type' => 'OJT type',
            'company_supervisor_id' => 'supervisor',
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

    protected function ensureCompanySupervisor(?int $supervisorId = null): User
    {
        $supervisorId ??= $this->company_supervisor_id;

        abort_unless($supervisorId, 403);

        $supervisor = User::query()
            ->availableToCoordinator($this->coordinator()->id)
            ->findOrFail($supervisorId);

        abort_unless($supervisor->hasRole('supervisor'), 403);

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
            'student_name' => $assignment?->student?->name,
            'student_number' => $assignment?->student?->studentProfile?->student_id,
            'ojt_type' => $assignment?->ojt_type,
            'company_supervisor_id' => $assignment?->company_supervisor_id,
            'company_supervisor_name' => $assignment?->companySupervisor?->name,
            'status' => $assignment?->status,
            'remarks' => $assignment?->remarks,
        ];
    }

    protected function assignmentLabel(OjtAssignment $assignment): string
    {
        $studentName = $assignment->student?->name ?? 'Student';
        $assignee = $assignment->companySupervisor?->name ?? 'Supervisor';

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
        $this->status = $assignment->status;
        $this->remarks = $assignment->remarks;

        $this->resetValidation();
    }

    public function store(): void
    {
        $this->validate();

        $student = $this->ensureManagedStudent();

        $this->ensureCompanySupervisor();

        DB::transaction(function () use ($student) {
            $assignment = OjtAssignment::create([
                'student_id' => $student->id,
                'ojt_type' => $this->ojt_type,
                'company_supervisor_id' => $this->company_supervisor_id,
                'status' => $this->status,
                'remarks' => $this->remarks ?: null,
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
            $assignment = $this->assignment->load([
                'student.studentProfile',
                'companySupervisor',
            ]);

            $oldValues = $this->assignmentAuditData($assignment);

            $assignment->update([
                'student_id' => $student->id,
                'ojt_type' => $this->ojt_type,
                'company_supervisor_id' => $this->company_supervisor_id,
                'status' => $this->status,
                'remarks' => $this->remarks ?: null,
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
        $this->status = 'active';
        $this->remarks = null;

        $this->resetValidation();
    }
}
