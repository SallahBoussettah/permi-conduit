<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MySQL key length issue
        Schema::defaultStringLength(191);
        
        // Ensure locale is set correctly at application boot
        $this->setApplicationLocale();

        // Register the role middleware
        $this->app['router']->aliasMiddleware('role', \App\Http\Middleware\CheckRole::class);
    }
    
    /**
     * Set the application locale based on session, cookie, or URL parameter
     */
    protected function setApplicationLocale(): void
    {
        // Only run this in web requests
        if (!$this->app->runningInConsole()) {
            $request = Request::instance();
            
            // Check URL parameter first (highest priority)
            $locale = $request->query('lang');
            
            // If not in URL, check session
            if (!$locale && Session::has('locale')) {
                $locale = Session::get('locale');
            }
            
            // If not in session, check cookie
            if (!$locale && $request->hasCookie('locale')) {
                $locale = $request->cookie('locale');
            }
            
            // If still no locale, use default
            if (!$locale) {
                $locale = 'fr'; // Hardcoded default to French
            }
            
            // Validate locale - force to be either 'en' or 'fr'
            if (!in_array($locale, ['en', 'fr'])) {
                $locale = 'fr';
            }
            
            // Set the locale in all possible places
            App::setLocale($locale);
            Config::set('app.locale', $locale);
            
            // Log the locale being used
            \Log::debug('AppServiceProvider: Setting locale to ' . $locale);
        }
    }
}
