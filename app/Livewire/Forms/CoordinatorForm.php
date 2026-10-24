<?php

namespace App\Livewire\Forms;

use App\Enums\Role as EnumsRole;
use App\Models\User;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\WithFileUploads;

class CoordinatorForm extends Form
{
    use WithFileUploads;

    public ?User $coordinator = null;

    public $photo = null;

    public ?string $existing_photo_path = null;

    public ?string $existing_photo_url = null;

    public string $first_name = '';

    public ?string $middle_name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $contact_number = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $department = '';

    public function rules(): array
    {
        $userId = $this->coordinator?->id;
        return [
            'photo' => ['nullable', 'image', 'max:2048'],
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
            'password' => [$this->coordinator ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'department' => ['required', 'string', 'max:255'],
        ];
    }

    protected function coordinatorAuditData(?User $user = null): array
    {
        $user ??= $this->coordinator?->load('coordinatorProfile');

        return [
            'name' => $user?->name,
            'first_name' => $user?->first_name,
            'middle_name' => $user?->middle_name,
            'last_name' => $user?->last_name,
            'email' => $user?->email,
            'contact_number' => $user?->contact_number,
            'status' => $user?->status,
            'department' => $user?->coordinatorProfile?->department,
            'position' => $user?->coordinatorProfile?->position,
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'contact_number' => 'contact number',
            'department' => 'department',
        ];
    }

    public function setCoordinator(User $coordinator): void
    {
        $coordinator->load('coordinatorProfile');

        $this->coordinator = $coordinator;
        $this->existing_photo_path = $coordinator->profile_photo_path;
        $this->existing_photo_url = $coordinator->profile_photo_path
            ? Storage::url($coordinator->profile_photo_path)
            : null;

        $this->first_name = $coordinator->first_name ?? '';
        $this->middle_name = $coordinator->middle_name ?? '';
        $this->last_name = $coordinator->last_name ?? '';
        $this->email = $coordinator->email ?? '';
        $this->contact_number = $coordinator->contact_number ?? '';
        $this->department = $coordinator->coordinatorProfile?->department ?? '';

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
                ? $this->photo->store('coordinators/photos', 'public')
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

            $user->assignRole(EnumsRole::OJTCOORDINATOR->value);

            $user->coordinatorProfile()->create([
                'department' => $this->department,
                'position' => 'OJT Coordinator',
            ]);

            $user = $user->fresh('coordinatorProfile');

            AuditLogger::log(
                module: 'OJT Coordinators',
                action: 'created',
                target: $user,
                targetLabel: $user->name,
                description: 'Created an OJT Coordinator account.',
                newValues: $this->coordinatorAuditData($user),
            );
        });

        $this->resetForm();

        Flux::toast(
            variant: 'success',
            heading: 'Coordinator created.',
            text: 'The coordinator record has been added successfully.'
        );

        Flux::modal('create-coordinator')->close();
    }

    public function update(): void
    {
        if (! $this->coordinator) {
            return;
        }

        $this->validate();

        DB::transaction(function () {
            $user = $this->coordinator->load('coordinatorProfile');

            $oldValues = $this->coordinatorAuditData($user);

            $photoPath = $user->profile_photo_path;

            if ($this->photo) {
                $newPhotoPath = $this->photo->store('coordinators/photos', 'public');

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

            $user->syncRoles([EnumsRole::OJTCOORDINATOR->value]);

            $user->coordinatorProfile()->update([
                'department' => $this->department,
                'position' => 'OJT Coordinator',
            ]);

            $user = $user->fresh('coordinatorProfile');

            AuditLogger::log(
                module: 'OJT Coordinators',
                action: 'updated',
                target: $user,
                targetLabel: $user->name,
                description: 'Updated an OJT Coordinator account.',
                oldValues: $oldValues,
                newValues: $this->coordinatorAuditData($user),
                meta: [
                    'password_changed' => filled($this->password),
                    'photo_changed' => $this->photo !== null,
                ],
            );
        });

        $this->coordinator = $this->coordinator->fresh(['coordinatorProfile']);
        $this->setCoordinator($this->coordinator);

        Flux::toast(
            variant: 'success',
            heading: 'Coordinator updated.',
            text: 'The coordinator record has been updated successfully.'
        );

        Flux::modal('edit-coordinator-'.$this->coordinator->id)->close();
    }

    public function resetForm(): void
    {
        $this->coordinator = null;
        $this->photo = null;
        $this->existing_photo_path = null;
        $this->existing_photo_url = null;
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
