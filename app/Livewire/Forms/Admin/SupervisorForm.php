<?php

namespace App\Livewire\Forms\Admin;

use App\Enums\Role as EnumsRole;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\StaffDirectory;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\WithFileUploads;

class SupervisorForm extends Form
{
    use WithFileUploads;

    public ?User $supervisor = null;

    public $photo = null;

    public ?string $existing_photo_path = null;

    public ?string $existing_photo_url = null;

    public string $supervisor_id = '';

    public string $first_name = '';

    public ?string $middle_name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $contact_number = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $company_name = '';

    public ?string $company_address = '';

    public ?string $position_department = '';

    public function rules(): array
    {
        $userId = $this->supervisor?->id;
        $supervisorProfileId = $this->supervisor?->supervisorProfile?->id;

        return [
            'photo' => ['nullable', 'image', 'max:2048'],
            'supervisor_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('supervisor_profiles', 'supervisor_id')->ignore($supervisorProfileId),
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
            'password' => [$this->supervisor ? 'nullable' : 'required', 'string', 'min:2', 'confirmed'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'position_department' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'supervisor_id' => 'supervisor ID',
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'contact_number' => 'contact number',
            'company_name' => 'company name',
            'company_address' => 'company address',
            'position_department' => 'position and department',
        ];
    }

    public function fillRandom(): void
    {
        $this->fillFromDirectoryRecord(StaffDirectory::randomSupervisor());
    }

    public function fillFromStaffDirectory(): bool
    {
        $supervisor = StaffDirectory::findSupervisor($this->supervisor_id);

        if (! $supervisor) {
            return false;
        }

        $this->fillFromDirectoryRecord($supervisor);

        return true;
    }

    public function fillPasswordFromLastName(): void
    {
        if ($this->supervisor) {
            return;
        }

        $this->password = trim($this->last_name);
        $this->password_confirmation = trim($this->last_name);
    }

    private function fillFromDirectoryRecord(array $supervisor): void
    {
        $this->supervisor_id = $supervisor['supervisor_id'];
        $this->first_name = $supervisor['first_name'];
        $this->middle_name = $supervisor['middle_name'];
        $this->last_name = $supervisor['last_name'];
        $this->email = $supervisor['email'];
        $this->contact_number = $supervisor['contact_number'];
        $this->company_name = $supervisor['company_name'];
        $this->company_address = $supervisor['company_address'];
        $this->fillPasswordFromLastName();

        $this->resetValidation();
    }

    protected function fullName(): string
    {
        return trim(collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])->filter()->implode(' '));
    }

    protected function supervisorAuditData(?User $user = null): array
    {
        $user ??= $this->supervisor?->load('supervisorProfile');

        return [
            'name' => $user?->name,
            'first_name' => $user?->first_name,
            'middle_name' => $user?->middle_name,
            'last_name' => $user?->last_name,
            'email' => $user?->email,
            'contact_number' => $user?->contact_number,
            'status' => $user?->status,
            'supervisor_id' => $user?->supervisorProfile?->supervisor_id,
            'company_name' => $user?->supervisorProfile?->company_name,
            'company_address' => $user?->supervisorProfile?->company_address,
            'position_department' => $user?->supervisorProfile?->position_department,
        ];
    }

    public function setSupervisor(User $supervisor): void
    {
        $supervisor->load('supervisorProfile');

        $this->supervisor = $supervisor;
        $this->existing_photo_path = $supervisor->profile_photo_path;
        $this->existing_photo_url = $supervisor->profile_photo_path
            ? Storage::url($supervisor->profile_photo_path)
            : null;

        $this->supervisor_id = $supervisor->supervisorProfile?->supervisor_id ?? '';
        $this->first_name = $supervisor->first_name ?? '';
        $this->middle_name = $supervisor->middle_name ?? '';
        $this->last_name = $supervisor->last_name ?? '';
        $this->email = $supervisor->email ?? '';
        $this->contact_number = $supervisor->contact_number ?? '';
        $this->company_name = $supervisor->supervisorProfile?->company_name ?? '';
        $this->company_address = $supervisor->supervisorProfile?->company_address ?? '';
        $this->position_department = $supervisor->supervisorProfile?->position_department ?? '';

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
                ? $this->photo->store('supervisors/photos', 'public')
                : null;

            $fullName = $this->fullName();

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

            $user->assignRole(EnumsRole::SUPERVISOR->value);

            $user->supervisorProfile()->create([
                'supervisor_id' => $this->supervisor_id,
                'company_name' => $this->company_name,
                'company_address' => $this->company_address ?: null,
                'position_department' => $this->position_department ?: null,
            ]);

            $user = $user->fresh('supervisorProfile');

            AuditLogger::log(
                module: 'Supervisors',
                action: 'created',
                target: $user,
                targetLabel: $user->name,
                description: 'Created a Supervisor account.',
                newValues: $this->supervisorAuditData($user),
            );
        });

        $this->resetForm();

        Flux::toast(
            variant: 'success',
            heading: 'Supervisor created.',
            text: 'The supervisor has been added successfully.'
        );

        Flux::modal('create-supervisor')->close();
    }

    public function update(): void
    {
        if (! $this->supervisor) {
            return;
        }

        $this->validate();

        DB::transaction(function () {
            $user = $this->supervisor->load('supervisorProfile');

            $oldValues = $this->supervisorAuditData($user);

            $photoPath = $user->profile_photo_path;

            if ($this->photo) {
                $newPhotoPath = $this->photo->store('supervisors/photos', 'public');

                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }

                $photoPath = $newPhotoPath;
            }

            $this->existing_photo_path = $photoPath;
            $this->existing_photo_url = $photoPath ? Storage::url($photoPath) : null;

            $fullName = $this->fullName();

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

            $user->syncRoles([EnumsRole::SUPERVISOR->value]);

            $user->supervisorProfile()->update([
                'supervisor_id' => $this->supervisor_id,
                'company_name' => $this->company_name,
                'company_address' => $this->company_address ?: null,
                'position_department' => $this->position_department ?: null,
            ]);

            $user = $user->fresh('supervisorProfile');

            AuditLogger::log(
                module: 'Supervisors',
                action: 'updated',
                target: $user,
                targetLabel: $user->name,
                description: 'Updated a Supervisor account.',
                oldValues: $oldValues,
                newValues: $this->supervisorAuditData($user),
                meta: [
                    'password_changed' => filled($this->password),
                    'photo_changed' => $this->photo !== null,
                ],
            );
        });

        $this->supervisor = $this->supervisor->fresh(['supervisorProfile']);
        $this->setSupervisor($this->supervisor);

        Flux::toast(
            variant: 'success',
            heading: 'Supervisor updated.',
            text: 'The supervisor has been updated successfully.'
        );

        Flux::modal('edit-supervisor-'.$this->supervisor->id)->close();
    }

    public function resetForm(): void
    {
        $this->supervisor = null;
        $this->photo = null;
        $this->existing_photo_path = null;
        $this->existing_photo_url = null;
        $this->supervisor_id = '';
        $this->first_name = '';
        $this->middle_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->contact_number = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->company_name = '';
        $this->company_address = '';
        $this->position_department = '';

        $this->resetValidation();
    }
}
