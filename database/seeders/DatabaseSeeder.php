<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the RoleSeeder first
        $this->call(RoleSeeder::class);

        // Create an admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);

        // Create an inspector user
        User::factory()->create([
            'name' => 'Inspector User',
            'email' => 'inspector@example.com',
            'role_id' => Role::where('name', 'inspector')->first()->id,
        ]);

        // Create a candidate user
        User::factory()->create([
            'name' => 'Candidate User',
            'email' => 'candidate@example.com',
            'role_id' => Role::where('name', 'candidate')->first()->id,
        ]);

        $this->call([
            ExamTypeSeeder::class,    // Must run before ExamSectionSeeder
            ExamSectionSeeder::class,
            CourseSeeder::class,
        ]);
    }
}
