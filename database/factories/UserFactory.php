<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\ProfessorProfile;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function coordinator(): static
    {
        return $this
            ->state(fn (array $attributes) => [
                'status' => 'Active',
            ])
            ->afterCreating(function (User $user): void {
                $user->assignRole(Role::OJTCOORDINATOR->value);
            });
    }

    public function professor(): static
    {
        return $this
            ->state(fn (array $attributes) => [
                'status' => 'Active',
            ])
            ->afterCreating(function (User $user): void {
                $user->assignRole(Role::PROFESSOR->value);

                ProfessorProfile::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_id' => 'PRF-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                        'department' => 'Computer Studies',
                        'position' => 'Professor',
                    ],
                );
            });
    }

    public function companySupervisor(): static
    {
        return $this
            ->state(fn (array $attributes) => [
                'status' => 'Active',
            ])
            ->afterCreating(function (User $user): void {
                $user->assignRole(Role::COMPANYSUPERVISOR->value);

                SupervisorProfile::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'supervisor_id' => 'SUP-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                        'company_name' => 'Demo Company',
                        'company_address' => 'Makati City, Metro Manila',
                    ],
                );
            });
    }

    public function student(): static
    {
        return $this
            ->state(fn (array $attributes) => [
                'status' => 'Active',
            ])
            ->afterCreating(function (User $user): void {
                $user->assignRole(Role::STUDENT->value);

                StudentProfile::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_id' => 'STU-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                        'course' => 'BS Information Technology',
                        'year_level' => '4th Year',
                        'section' => 'A',
                        'ojt_required_hours' => 486,
                    ],
                );
            });
    }
}
