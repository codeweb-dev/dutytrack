<?php

namespace App\Livewire\Forms\Coordinator;

use App\Enums\Role as EnumsRole;
use App\Models\OjtAssignment;
use App\Models\User;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\WithFileUploads;

class StudentForm extends Form
{
    use WithFileUploads;

    public ?User $student = null;

    public $photo = null;

    public ?string $existing_photo_path = null;

    public ?string $existing_photo_url = null;

    public string $student_id = '';

    public ?string $birthdate = null;

    public string $first_name = '';

    public string $last_name = '';

    public ?string $extension_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $course = '';

    public ?string $contact_number = '';

    public ?string $middle_name = '';

    public int $ojt_required_hours = 0;

    public ?int $company_supervisor_id = null;

    public function rules(): array
    {
        $userId = $this->student?->id;
        $studentProfileId = $this->student?->studentProfile?->id;

        return [
            'photo' => ['nullable', 'image', 'max:2048'],
            'student_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('student_profiles', 'student_id')->ignore($studentProfileId),
            ],
            'birthdate' => ['required', 'date', 'before:today'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'extension_name' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'])],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'password' => [$this->student ? 'nullable' : 'required', 'string', 'min:3', 'confirmed'],
            'course' => ['required', 'string', 'max:50'],
            'ojt_required_hours' => ['nullable', 'integer', 'min:0'],
            'company_supervisor_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'student_id' => 'student ID',
            'birthdate' => 'birthdate',
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'contact_number' => 'contact number',
            'ojt_required_hours' => 'OJT required hours',
        ];
    }

    protected function studentAuditData(?User $user = null): array
    {
        $user ??= $this->student?->load('studentProfile', 'coordinator', 'ojtAssignment.companySupervisor');

        return [
            'name' => $user?->name,
            'first_name' => $user?->first_name,
            'middle_name' => $user?->middle_name,
            'last_name' => $user?->last_name,
            'extension_name' => $user?->studentProfile?->extension_name,
            'email' => $user?->email,
            'contact_number' => $user?->contact_number,
            'status' => $user?->status,
            'student_id' => $user?->studentProfile?->student_id,
            'birthdate' => $user?->studentProfile?->birthdate,
            'course' => $user?->studentProfile?->course,
            'ojt_required_hours' => $user?->studentProfile?->ojt_required_hours,
            'company_supervisor_id' => $user?->ojtAssignment?->company_supervisor_id,
            'company_supervisor_name' => $user?->ojtAssignment?->companySupervisor?->name,
            'coordinator_id' => $user?->coordinator_id,
            'coordinator_name' => $user?->coordinator?->name,
        ];
    }

    protected function coordinator(): User
    {
        $coordinator = Auth::user();

        abort_unless(
            $coordinator instanceof User && $coordinator->hasRole(EnumsRole::OJTCOORDINATOR->value),
            403
        );

        return $coordinator;
    }

    protected function ensureManagedByCurrentCoordinator(?User $student = null): void
    {
        $student ??= $this->student;

        if (! $student) {
            return;
        }

        abort_unless((int) $student->coordinator_id === (int) $this->coordinator()->id, 403);
    }

    protected function ensureCompanySupervisor(): void
    {
        $coordinator = $this->coordinator();

        abort_unless(
            User::query()->availableToCoordinator($coordinator->id)->role('supervisor')->whereKey($this->company_supervisor_id)->exists(),
            403
        );
    }

    public function setStudent(User $student): void
    {
        $this->ensureManagedByCurrentCoordinator($student);

        $student->load('studentProfile', 'coordinator', 'ojtAssignment');

        $this->student = $student;
        $this->existing_photo_path = $student->profile_photo_path;
        $this->existing_photo_url = $student->profile_photo_path
            ? Storage::url($student->profile_photo_path)
            : null;

        $this->first_name = $student->first_name ?? '';
        $this->middle_name = $student->middle_name ?? '';
        $this->last_name = $student->last_name ?? '';
        $this->extension_name = $student->studentProfile?->extension_name ?? '';
        $this->email = $student->email ?? '';
        $this->contact_number = $student->contact_number ?? '';

        $this->student_id = $student->studentProfile?->student_id ?? '';
        $this->birthdate = $student->studentProfile?->birthdate;
        $this->course = $student->studentProfile?->course ?? '';
        $this->ojt_required_hours = (int) ($student->studentProfile?->ojt_required_hours ?? 0);
        $this->company_supervisor_id = $student->ojtAssignment?->company_supervisor_id;

        $this->password = '';
        $this->password_confirmation = '';
        $this->photo = null;

        $this->resetValidation();
    }

    public function update(): void
    {
        if (! $this->student) {
            return;
        }

        $this->ensureManagedByCurrentCoordinator();

        $this->validate();
        $this->ensureCompanySupervisor();

        DB::transaction(function () {
            $user = $this->student->load('studentProfile', 'coordinator', 'ojtAssignment.companySupervisor');

            $oldValues = $this->studentAuditData($user);

            $photoPath = $user->profile_photo_path;

            if ($this->photo) {
                $newPhotoPath = $this->photo->store('students/photos', 'public');

                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }

                $photoPath = $newPhotoPath;
            }

            $this->existing_photo_path = $photoPath;
            $this->existing_photo_url = $photoPath ? Storage::url($photoPath) : null;

            $fullName = trim(collect([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
            ])->filter()->implode(' '));

            $user->update([
                'name' => $fullName,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name ?: null,
                'last_name' => $this->last_name,
                'contact_number' => $this->contact_number ?: null,
                'email' => $this->email,
                'profile_photo_path' => $photoPath,
            ]);

            if (filled($this->password)) {
                $user->update([
                    'password' => $this->password,
                ]);
            }

            $user->studentProfile()->update([
                'student_id' => $this->student_id,
                'extension_name' => $this->extension_name ?: null,
                'birthdate' => $this->birthdate,
                'course' => $this->course,
                'ojt_required_hours' => $this->ojt_required_hours ?: 0,
            ]);

            if ($user->ojtAssignment) {
                $user->ojtAssignment->update(['company_supervisor_id' => $this->company_supervisor_id]);
            } else {
                OjtAssignment::create([
                    'student_id' => $user->id,
                    'ojt_type' => 'internal',
                    'company_supervisor_id' => $this->company_supervisor_id,
                    'status' => 'active',
                ]);
            }

            $user = $user->fresh(['studentProfile', 'coordinator', 'ojtAssignment.companySupervisor']);

            AuditLogger::log(
                module: 'Students',
                action: 'updated',
                target: $user,
                targetLabel: $user->name,
                description: 'Updated a Student account.',
                oldValues: $oldValues,
                newValues: $this->studentAuditData($user),
                meta: [
                    'password_changed' => filled($this->password),
                    'photo_changed' => $this->photo !== null,
                ],
            );
        });

        $this->student = $this->student->fresh(['studentProfile']);
        $this->setStudent($this->student);

        Flux::toast(
            variant: 'success',
            heading: 'Student updated.',
            text: 'The student has been updated successfully.'
        );

        Flux::modal('edit-student-'.$this->student->id)->close();
    }

    public function resetForm(): void
    {
        $this->student = null;
        $this->photo = null;
        $this->existing_photo_path = null;
        $this->existing_photo_url = null;
        $this->student_id = '';
        $this->birthdate = null;
        $this->first_name = '';
        $this->middle_name = '';
        $this->last_name = '';
        $this->extension_name = '';
        $this->email = '';
        $this->contact_number = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->course = '';
        $this->ojt_required_hours = 0;
        $this->company_supervisor_id = null;

        $this->resetValidation();
    }
}
