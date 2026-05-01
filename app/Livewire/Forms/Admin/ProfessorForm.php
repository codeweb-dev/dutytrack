<?php

namespace App\Livewire\Forms\Admin;

use App\Enums\Role as EnumsRole;
use App\Models\User;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\WithFileUploads;

class ProfessorForm extends Form
{
    use WithFileUploads;

    public ?User $professor = null;

    public $photo = null;
    public ?string $existing_photo_path = null;
    public ?string $existing_photo_url = null;

    public string $employee_id = '';
    public string $first_name = '';
    public ?string $middle_name = '';
    public string $last_name = '';
    public string $email = '';
    public ?string $contact_number = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $department = 'Computer Science';

    public function rules(): array
    {
        $userId = $this->professor?->id;
        $profileId = $this->professor?->professorProfile?->id;

        return [
            'photo' => ['nullable', 'image', 'max:2048'],
            'employee_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('professor_profiles', 'employee_id')->ignore($profileId),
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
            'password' => [$this->professor ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'department' => ['required', 'string', 'max:255'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'employee_id' => 'employee ID',
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'contact_number' => 'contact number',
            'department' => 'department',
        ];
    }

    protected function professorAuditData(?User $user = null): array
    {
        $user ??= $this->professor?->load('professorProfile');

        return [
            'name' => $user?->name,
            'first_name' => $user?->first_name,
            'middle_name' => $user?->middle_name,
            'last_name' => $user?->last_name,
            'email' => $user?->email,
            'contact_number' => $user?->contact_number,
            'status' => $user?->status,
            'employee_id' => $user?->professorProfile?->employee_id,
            'department' => $user?->professorProfile?->department,
            'position' => $user?->professorProfile?->position,
        ];
    }

    public function setProfessor(User $professor): void
    {
        $professor->load('professorProfile');

        $this->professor = $professor;
        $this->existing_photo_path = $professor->profile_photo_path;
        $this->existing_photo_url = $professor->profile_photo_path
            ? Storage::url($professor->profile_photo_path)
            : null;

        $this->employee_id = $professor->professorProfile?->employee_id ?? '';
        $this->first_name = $professor->first_name ?? '';
        $this->middle_name = $professor->middle_name ?? '';
        $this->last_name = $professor->last_name ?? '';
        $this->email = $professor->email ?? '';
        $this->contact_number = $professor->contact_number ?? '';
        $this->department = $professor->professorProfile?->department ?? '';

        $this->password = '';
        $this->password_confirmation = '';
        $this->photo = null;

        $this->resetValidation();
    }

    public function store(): void
    {
        $this->validate();

        DB::transaction(function () {
            $photoPath = $this->photo
                ? $this->photo->store('professors/photos', 'public')
                : null;

            $fullName = trim(collect([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
            ])->filter()->implode(' '));

            $user = User::create([
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

            $user->assignRole(EnumsRole::PROFESSOR->value);

            $user->professorProfile()->create([
                'employee_id' => $this->employee_id,
                'department' => $this->department,
                'position' => 'Professor',
            ]);

            $user = $user->fresh('professorProfile');

            AuditLogger::log(
                module: 'Professors',
                action: 'created',
                target: $user,
                targetLabel: $user->name,
                description: 'Created a Professor account.',
                newValues: $this->professorAuditData($user),
            );
        });

        $this->resetForm();

        Flux::toast(
            variant: 'success',
            heading: 'Professor created.',
            text: 'The professor record has been added successfully.'
        );

        Flux::modal('create-professor')->close();
    }

    public function update(): void
    {
        if (! $this->professor) {
            return;
        }

        $this->validate();

        DB::transaction(function () {
            $user = $this->professor->load('professorProfile');

            $oldValues = $this->professorAuditData($user);

            $photoPath = $user->profile_photo_path;

            if ($this->photo) {
                $newPhotoPath = $this->photo->store('professors/photos', 'public');

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

            $user->syncRoles([EnumsRole::PROFESSOR->value]);

            $user->professorProfile()->update([
                'employee_id' => $this->employee_id,
                'department' => $this->department,
                'position' => 'Professor',
            ]);

            $user = $user->fresh('professorProfile');

            AuditLogger::log(
                module: 'Professors',
                action: 'updated',
                target: $user,
                targetLabel: $user->name,
                description: 'Updated a Professor account.',
                oldValues: $oldValues,
                newValues: $this->professorAuditData($user),
                meta: [
                    'password_changed' => filled($this->password),
                    'photo_changed' => $this->photo !== null,
                ],
            );
        });

        $this->professor = $this->professor->fresh(['professorProfile']);
        $this->setProfessor($this->professor);

        Flux::toast(
            variant: 'success',
            heading: 'Professor updated.',
            text: 'The professor record has been updated successfully.'
        );

        Flux::modal('edit-professor-' . $this->professor->id)->close();
    }

    public function resetForm(): void
    {
        $this->professor = null;
        $this->photo = null;
        $this->existing_photo_path = null;
        $this->existing_photo_url = null;
        $this->employee_id = '';
        $this->first_name = '';
        $this->middle_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->contact_number = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->department = '';

        $this->resetValidation();
    }
}
