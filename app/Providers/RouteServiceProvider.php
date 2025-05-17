<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        // Add a custom route binding for User to enforce school-based access control
        Route::bind('user', function ($value) {
            $user = User::findOrFail($value);
            
            // Skip access control checks if not authenticated
            if (!auth()->check()) {
                return $user;
            }
            
            // Super admins can access all users
            if (auth()->user()->isSuperAdmin()) {
                return $user;
            }
            
            // Regular admins can only access users from their own school
            if (auth()->user()->isAdmin() && $user->school_id !== auth()->user()->school_id) {
                abort(403, 'You do not have permission to access users from other schools.');
            }
            
            return $user;
        });
        
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
} 