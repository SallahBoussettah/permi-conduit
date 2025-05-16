<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate user accounts that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();
        
        // Find all active users whose accounts have expired
        $expiredUsers = User::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where(function ($query) use ($today) {
                $query->whereDate('expires_at', '<', $today);
            })
            ->get();
            
        $count = $expiredUsers->count();
        
        if ($count === 0) {
            $this->info('No expired accounts found.');
            return 0;
        }
        
        foreach ($expiredUsers as $user) {
            // Deactivate the account
            $user->is_active = false;
            $user->save();
            
            // Log the deactivation
            Log::info("User account {$user->id} ({$user->email}) deactivated due to expiration on {$user->expires_at->format('Y-m-d')}");
            
            // You could also send an email notification here
        }
        
        $this->info("Successfully deactivated {$count} expired user accounts.");
        
        return 0;
    }
}
