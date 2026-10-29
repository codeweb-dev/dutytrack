<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoordinatorSeeder extends Seeder
{
    /**
     * OJT coordinators per program. Students signing up are routed by course:
     * BSCS => Computer Science, BSIT => Information Technology.
     */
    private const COORDINATORS = [
        ['last_name' => 'Dumpit', 'email' => 'dumpit@dutytrack.test', 'program' => 'Computer Science'],
        ['last_name' => 'Rowe', 'email' => 'rowe@dutytrack.test', 'program' => 'Information Technology'],
    ];

    public function run(): void
    {
        foreach (self::COORDINATORS as $coordinator) {
            $user = User::firstOrCreate(
                ['email' => $coordinator['email']],
                [
                    'name' => $coordinator['last_name'],
                    'last_name' => $coordinator['last_name'],
                    'password' => 'password',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
            );

            $user->assignRole(Role::OJTCOORDINATOR->value);

            $user->coordinatorProfile()->updateOrCreate([], [
                'department' => 'Computer Studies',
                'program' => $coordinator['program'],
                'position' => 'OJT Coordinator',
            ]);
        }
    }
}
