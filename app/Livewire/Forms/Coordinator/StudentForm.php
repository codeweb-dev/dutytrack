<?php

namespace App\Livewire\Forms\Coordinator;

use App\Enums\Role as EnumsRole;
use App\Models\StudentRequirement;
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

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $course = '';

    public string $year_level = '';

    public ?string $section = '';

    public ?string $contact_number = '';

    public ?string $middle_name = '';

    public int $ojt_required_hours = 0;

    public array $requirementChecks = [];

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
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'password' => [$this->student ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'course' => ['required', 'string', 'max:50'],
            'year_level' => ['required', 'string', 'max:20'],
            'section' => ['nullable', 'string', 'max:50'],
            'ojt_required_hours' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'student_id' => 'student ID',
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'contact_number' => 'contact number',
            'year_level' => 'year level',
            'ojt_required_hours' => 'OJT required hours',
        ];
    }

    protected function studentAuditData(?User $user = null): array
    {
        $user ??= $this->student?->load('studentProfile', 'coordinator');

        return [
            'name' => $user?->name,
            'first_name' => $user?->first_name,
            'middle_name' => $user?->middle_name,
            'last_name' => $user?->last_name,
            'email' => $user?->email,
            'contact_number' => $user?->contact_number,
            'status' => $user?->status,
            'student_id' => $user?->studentProfile?->student_id,
            'course' => $user?->studentProfile?->course,
            'year_level' => $user?->studentProfile?->year_level,
            'section' => $user?->studentProfile?->section,
            'ojt_required_hours' => $user?->studentProfile?->ojt_required_hours,
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

    public function setStudent(User $student): void
    {
        $this->ensureManagedByCurrentCoordinator($student);

        $student->load('studentProfile', 'coordinator');

        $this->student = $student;
        $this->existing_photo_path = $student->profile_photo_path;
        $this->existing_photo_url = $student->profile_photo_path
            ? Storage::url($student->profile_photo_path)
            : null;

        $this->first_name = $student->first_name ?? '';
        $this->middle_name = $student->middle_name ?? '';
        $this->last_name = $student->last_name ?? '';
        $this->email = $student->email ?? '';
        $this->contact_number = $student->contact_number ?? '';

        $this->student_id = $student->studentProfile?->student_id ?? '';
        $this->course = $student->studentProfile?->course ?? '';
        $this->year_level = $student->studentProfile?->year_level ?? '';
        $this->section = $student->studentProfile?->section ?? '';
        $this->ojt_required_hours = (int) ($student->studentProfile?->ojt_required_hours ?? 0);

        $this->password = '';
        $this->password_confirmation = '';
        $this->photo = null;

        $this->resetValidation();
    }

    public function store(): void
    {
        $coordinator = $this->coordinator();

        $this->validate();

        DB::transaction(function () use ($coordinator) {
            $photoPath = $this->photo
                ? $this->photo->store('students/photos', 'public')
                : null;

            $fullName = trim(collect([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
            ])->filter()->implode(' '));

            $user = User::create([
                'coordinator_id' => $coordinator->id,
                'name' => $fullName,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name ?: null,
                'last_name' => $this->last_name,
                'contact_number' => $this->contact_number ?: null,
                'email' => $this->email,
                'password' => $this->password,
                'profile_photo_path' => $photoPath,
                'status' => 'active',
            ]);

            $user->assignRole(EnumsRole::STUDENT->value);

            $user->studentProfile()->create([
                'student_id' => $this->student_id,
                'course' => $this->course,
                'year_level' => $this->year_level,
                'section' => $this->section ?: null,
                'ojt_required_hours' => $this->ojt_required_hours ?: 0,
            ]);

            StudentRequirement::ensureForStudent($user);

            $checkedRequirements = array_is_list($this->requirementChecks)
                ? collect($this->requirementChecks)->filter()->values()->all()
                : collect($this->requirementChecks)
                    ->filter(fn ($checked): bool => (bool) $checked)
                    ->keys()
                    ->all();

            if ($checkedRequirements !== []) {
                StudentRequirement::query()
                    ->where('student_id', $user->id)
                    ->whereIn('key', $checkedRequirements)
                    ->update([
                        'is_completed' => true,
                        'completed_at' => now(),
                        'completed_by' => $coordinator->id,
                    ]);
            }

            $user = $user->fresh(['studentProfile', 'coordinator']);

            AuditLogger::log(
                module: 'Students',
                action: 'created',
                target: $user,
                targetLabel: $user->name,
                description: 'Created a Student account.',
                newValues: $this->studentAuditData($user),
            );
        });

        $this->resetForm();

        Flux::toast(
            variant: 'success',
            heading: 'Student created.',
            text: 'The student has been added successfully.'
        );

        Flux::modal('create-student')->close();
    }

    public function update(): void
    {
        if (! $this->student) {
            return;
        }

        $this->ensureManagedByCurrentCoordinator();

        $this->validate();

        DB::transaction(function () {
            $user = $this->student->load('studentProfile', 'coordinator');

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
                'course' => $this->course,
                'year_level' => $this->year_level,
                'section' => $this->section ?: null,
                'ojt_required_hours' => $this->ojt_required_hours ?: 0,
            ]);

            $user = $user->fresh(['studentProfile', 'coordinator']);

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
        $this->first_name = '';
        $this->middle_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->contact_number = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->course = '';
        $this->year_level = '';
        $this->section = '';
        $this->ojt_required_hours = 0;
        $this->requirementChecks = [];

        $this->resetValidation();
    }
}
