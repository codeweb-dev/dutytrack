<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('admin.email');
        $name = config('admin.name');

        $password = config('admin.password');

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'profile_photo_path' => 'profile/ecc_logo.jpeg',
            ]
        );

        $admin->assignRole(Role::ADMIN->value);
    }
}
