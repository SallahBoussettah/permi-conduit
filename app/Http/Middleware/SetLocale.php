<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
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
        
        // If still no locale, use default from config
        if (!$locale) {
            $locale = config('app.locale', 'en');
        }
        
        // Validate locale - force to be either 'en' or 'fr'
        if (!in_array($locale, ['en', 'fr'])) {
            $locale = config('app.fallback_locale', 'en');
        }
        
        // IMPORTANT: Set the locale in all possible places
        App::setLocale($locale);
        Session::put('locale', $locale);
        Config::set('app.locale', $locale);
        
        // Force config to update available_locales if needed
        if (!isset(Config::get('app.available_locales')[$locale])) {
            $availableLocales = Config::get('app.available_locales', []);
            $availableLocales[$locale] = $locale === 'en' ? 'English' : 'Français';
            Config::set('app.available_locales', $availableLocales);
        }
        
        // Log the locale being used
        Log::debug('Using locale: ' . $locale . ' for path: ' . $request->path());
        
        // Get the response
        $response = $next($request);
        
        // If it's a view response, we can check if the locale was properly applied
        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            if (is_string($content)) {
                // Force replace any hardcoded locale references in the HTML
                $content = preg_replace('/<html lang="[^"]*"/', '<html lang="' . $locale . '"', $content);
                $response->setContent($content);
            }
        }
        
        return $response;
    }
} 