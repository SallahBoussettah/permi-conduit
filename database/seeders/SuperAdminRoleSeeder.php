<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super_admin role if it doesn't exist
        $role = Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['name' => 'super_admin']
        );

        // Create a default super admin user
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }
}