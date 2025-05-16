<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class AssignDefaultRolesToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign the default candidate role to all users without a role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the candidate role
        $candidateRole = Role::where('name', 'candidate')->first();
        
        if (!$candidateRole) {
            $this->error('Candidate role not found! Please run the RoleSeeder first.');
            return 1;
        }
        
        // Find all users without a role
        $usersWithoutRole = User::whereNull('role_id')->get();
        $count = $usersWithoutRole->count();
        
        if ($count === 0) {
            $this->info('All users already have roles assigned.');
            return 0;
        }
        
        // Assign the candidate role to each user
        foreach ($usersWithoutRole as $user) {
            $user->role_id = $candidateRole->id;
            $user->save();
            $this->line("Assigned candidate role to user: {$user->name} ({$user->email})");
        }
        
        $this->info("Successfully assigned the candidate role to {$count} users.");
        return 0;
    }
} 