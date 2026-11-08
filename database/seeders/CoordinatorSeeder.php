<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class CoordinatorSeeder extends Seeder
{
    /**
     * OJT coordinators per program. Students signing up are routed by course:
     * BSCS => Computer Science, BSIT => Information Technology.
     */
    public function run(): void
    {
        $coordinators = [
            [...config('app.coordinator_accounts.bscs'), 'program' => 'Computer Science'],
            [...config('app.coordinator_accounts.bsit'), 'program' => 'Information Technology'],
        ];

        Validator::make($coordinators, [
            '*.name' => ['required', 'string', 'max:255'],
            '*.email' => ['required', 'email', 'max:255'],
            '*.password' => ['required', 'string', 'min:12'],
        ])->validate();

        foreach ($coordinators as $coordinator) {
            $user = User::firstOrCreate(
                ['email' => $coordinator['email']],
                [
                    'name' => $coordinator['name'],
                    'password' => $coordinator['password'],
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
            );

            if (! $user->hasRole(Role::OJTCOORDINATOR->value) && $user->roles()->exists()) {
                throw new RuntimeException('A configured coordinator email already belongs to another role.');
            }

            $user->assignRole(Role::OJTCOORDINATOR->value);

            $user->coordinatorProfile()->updateOrCreate([], [
                'department' => 'Computer Studies',
                'program' => $coordinator['program'],
                'position' => 'OJT Coordinator',
            ]);
        }
    }
}
