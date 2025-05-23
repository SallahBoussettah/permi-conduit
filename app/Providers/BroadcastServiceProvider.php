<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the Broadcast routes
        Broadcast::routes(['middleware' => ['web', 'auth']]);
        
        // Log when the BroadcastServiceProvider is booted
        Log::info('BroadcastServiceProvider booted');
        
        // Register the broadcast channels
        require base_path('routes/channels.php');
        
        // Log registered channels
        Log::info('Broadcast channels registered', [
            'channel' => 'notifications.*',
            'driver' => config('broadcasting.default')
        ]);
    }
} 