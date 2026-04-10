<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use App\Enums\Role as EnumsRole;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            EnumsRole::ADMIN->value,
            EnumsRole::OJTCOORDINATOR->value,
            EnumsRole::COMPANYSUPERVISOR->value,
            EnumsRole::STUDENT->value,
            EnumsRole::PROFESSOR->value,
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
