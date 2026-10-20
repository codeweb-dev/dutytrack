<?php

namespace Database\Seeders;

use App\Enums\Role as EnumsRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

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
            EnumsRole::SUPERVISOR->value,
            EnumsRole::STUDENT->value,
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
