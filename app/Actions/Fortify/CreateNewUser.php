<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\Role;
use App\Models\CoordinatorProfile;
use App\Models\StudentRequirement;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Course => coordinator program (routes the student to their coordinator) and required OJT hours.
     */
    public const COURSES = [
        'BSCS' => ['program' => 'Computer Science', 'hours' => 300],
        'BSIT' => ['program' => 'Information Technology', 'hours' => 500],
    ];

    /**
     * Validate and create a newly registered student.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'photo' => ['nullable', 'image', 'max:2048'],
            'student_id' => ['required', 'string', 'max:255', Rule::unique('student_profiles', 'student_id')],
            'birthdate' => ['required', 'date', 'before:today'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'extension_name' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'])],
            'email' => $this->emailRules(),
            'contact_number' => ['required', 'string', 'max:20'],
            'course' => ['required', Rule::in(array_keys(self::COURSES))],
            'address' => ['required', 'string', 'max:255'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_address' => ['required', 'string', 'max:255'],
            'guardian_contact_number' => ['required', 'string', 'max:20'],
            'password' => $this->passwordRules(),
        ], [
            'email.unique' => 'An account with this email already exists.',
            'student_id.unique' => 'An account with this student ID already exists.',
        ])->validate();

        return DB::transaction(function () use ($input) {
            // ponytail: first coordinator whose program matches the course; unassigned (null) if none.
            $coordinatorId = CoordinatorProfile::query()
                ->where('program', self::COURSES[$input['course']]['program'])
                ->value('user_id');

            $user = User::create([
                'coordinator_id' => $coordinatorId,
                'name' => trim(collect([$input['first_name'], $input['middle_name'] ?? null, $input['last_name']])->filter()->implode(' ')),
                'first_name' => $input['first_name'],
                'middle_name' => ($input['middle_name'] ?? null) ?: null,
                'last_name' => $input['last_name'],
                'contact_number' => $input['contact_number'],
                'email' => $input['email'],
                'password' => $input['password'],
                'profile_photo_path' => isset($input['photo']) ? $input['photo']->store('students/photos', 'public') : null,
                'status' => 'pending',
            ]);

            $user->assignRole(Role::STUDENT->value);

            $user->studentProfile()->create([
                'student_id' => $input['student_id'],
                'extension_name' => $input['extension_name'] ?? null,
                'birthdate' => $input['birthdate'],
                'course' => $input['course'],
                'ojt_required_hours' => self::COURSES[$input['course']]['hours'],
                'address' => $input['address'],
                'guardian_name' => $input['guardian_name'],
                'guardian_address' => $input['guardian_address'],
                'guardian_contact_number' => $input['guardian_contact_number'],
            ]);

            StudentRequirement::ensureForStudent($user);

            AuditLogger::log(
                module: 'Students',
                action: 'created',
                target: $user,
                targetLabel: $user->name,
                description: 'Student signed up.',
            );

            return $user;
        });
    }
}
