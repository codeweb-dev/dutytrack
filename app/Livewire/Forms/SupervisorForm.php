<?php

namespace App\Livewire\Forms;

use App\Enums\Role as EnumsRole;
use App\Models\User;
use App\Support\AuditLogger;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Form;
use Livewire\WithFileUploads;

class SupervisorForm extends Form
{
    use WithFileUploads;

    public ?User $supervisor = null;

    public $photo = null;

    public ?string $existing_photo_path = null;

    public ?string $existing_photo_url = null;

    public string $first_name = '';

    public ?string $middle_name = '';

    public string $last_name = '';

    public ?string $extension_name = '';

    public string $email = '';

    public ?string $contact_number = '';

    public string $password = '';

    public string $password_confirmation = '';

    public const INTERNAL_INSTITUTION = 'EARIST CAVITE CAMPUS';

    public const INTERNAL_ADDRESS = 'Blk. 3 Lot 2, 5 Congressional Rd, 4 Poblacion, General Mariano Alvarez, 4117 Cavite';

    public string $supervisor_type = 'internal';

    public string $company_name = self::INTERNAL_INSTITUTION;

    public ?string $company_address = self::INTERNAL_ADDRESS;

    public ?string $position_department = '';

    public function rules(): array
    {
        $userId = $this->supervisor?->id;

        return [
            'photo' => [$this->supervisor && $this->existing_photo_path ? 'nullable' : 'required', 'image', 'max:2048'],
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
            // Same strength as self sign-up; a last-name default was guessable from the supervisor's profile.
            'password' => [$this->supervisor ? 'nullable' : 'required', 'string', Password::default(), 'confirmed'],
            'supervisor_type' => ['required', Rule::in(['internal', 'external'])],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'position_department' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'contact_number' => 'contact number',
            'supervisor_type' => 'type',
            'company_name' => $this->supervisor_type === 'internal' ? 'institution' : 'company name',
            'company_address' => 'company address',
            'position_department' => 'position and department',
        ];
    }

    public function applySupervisorType(): void
    {
        if ($this->supervisor_type === 'internal') {
            $this->company_name = self::INTERNAL_INSTITUTION;
            $this->company_address = self::INTERNAL_ADDRESS;

            return;
        }

        // Clear the internal prefill so it doesn't leak into an external company.
        if ($this->company_name === self::INTERNAL_INSTITUTION) {
            $this->company_name = '';
        }

        if ($this->company_address === self::INTERNAL_ADDRESS) {
            $this->company_address = '';
        }
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
            'extension_name' => $user?->supervisorProfile?->extension_name,
            'email' => $user?->email,
            'contact_number' => $user?->contact_number,
            'status' => $user?->status,
            'supervisor_type' => $user?->supervisorProfile?->supervisor_type,
            'company_name' => $user?->supervisorProfile?->company_name,
            'company_address' => $user?->supervisorProfile?->company_address,
            'position_department' => $user?->supervisorProfile?->position_department,
        ];
    }

    public function setSupervisor(User $supervisor): void
    {
        $this->ensureSupervisorAccess($supervisor);
        $supervisor->load('supervisorProfile');

        $this->supervisor = $supervisor;
        $this->existing_photo_path = $supervisor->profile_photo_path;
        $this->existing_photo_url = $supervisor->profile_photo_path
            ? Storage::url($supervisor->profile_photo_path)
            : null;

        $this->first_name = $supervisor->first_name ?? '';
        $this->middle_name = $supervisor->middle_name ?? '';
        $this->last_name = $supervisor->last_name ?? '';
        $this->extension_name = $supervisor->supervisorProfile?->extension_name ?? '';
        $this->email = $supervisor->email ?? '';
        $this->contact_number = $supervisor->contact_number ?? '';
        $this->supervisor_type = $supervisor->supervisorProfile?->supervisor_type ?? 'external';
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
        $actor = $this->ensureSupervisorAccess();
        $this->validate();

        DB::transaction(function () use ($actor) {
            $photoPath = $this->photo
                ? $this->photo->store('supervisors/photos', 'public')
                : null;

            $fullName = $this->fullName();

            $user = User::create([
                'coordinator_id' => $actor->id,
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
                'extension_name' => $this->extension_name ?: null,
                'supervisor_type' => $this->supervisor_type,
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

        $this->ensureSupervisorAccess($this->supervisor->fresh());

        $this->validate();

        // Assignments must match their supervisor's type, so a switch would leave them unmatched.
        if ($this->supervisor_type !== $this->supervisor->supervisorProfile?->supervisor_type
            && $this->supervisor->companySupervisorAssignments()->exists()) {
            throw ValidationException::withMessages([
                'form.supervisor_type' => 'Reassign this supervisor\'s trainees before changing their type.',
            ]);
        }

        DB::transaction(function () {
            $user = $this->supervisor->load('supervisorProfile');

            $oldValues = $this->supervisorAuditData($user);

            $photoPath = $user->profile_photo_path;

            if ($this->photo) {
                $newPhotoPath = $this->photo->store('supervisors/photos', 'public');

                if ($photoPath) {
                    // After commit, so a failed save doesn't leave the account pointing at a deleted file.
                    $oldPhotoPath = $photoPath;
                    DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhotoPath));
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
                'extension_name' => $this->extension_name ?: null,
                'supervisor_type' => $this->supervisor_type,
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
        $this->first_name = '';
        $this->middle_name = '';
        $this->last_name = '';
        $this->extension_name = '';
        $this->email = '';
        $this->contact_number = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->supervisor_type = 'internal';
        $this->company_name = self::INTERNAL_INSTITUTION;
        $this->company_address = self::INTERNAL_ADDRESS;
        $this->position_department = '';

        $this->resetValidation();
    }

    protected function ensureSupervisorAccess(?User $supervisor = null): User
    {
        $actor = Auth::user();

        abort_unless($actor instanceof User && $actor->hasRole(EnumsRole::OJTCOORDINATOR->value), 403);

        // No supervisor means a new one is being created by this coordinator.
        if ($supervisor) {
            abort_unless((int) $supervisor->coordinator_id === (int) $actor->id, 403);
        }

        return $actor;
    }
}
