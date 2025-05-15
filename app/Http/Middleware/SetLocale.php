<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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
        $locale = null;
        $source = 'init'; // For logging the source of the locale

        // 1. Check URL parameter (highest priority)
        if ($request->has('lang')) {
            $urlLocale = $request->query('lang');
            if (in_array($urlLocale, config('app.available_locales_codes', ['en', 'fr']))) {
                $locale = $urlLocale;
                $source = 'url';
            }
        }

        // 2. If not from URL, check Cookie (second priority)
        if (!$locale && $request->hasCookie('locale')) {
            $cookieLocale = $request->cookie('locale');
            if (in_array($cookieLocale, config('app.available_locales_codes', ['en', 'fr']))) {
                $locale = $cookieLocale;
                $source = 'cookie';
            }
        }
        
        // 3. If not from URL or Cookie, check Session (third priority)
        if (!$locale && Session::has('locale')) {
            $sessionLocale = Session::get('locale');
            if (in_array($sessionLocale, config('app.available_locales_codes', ['en', 'fr']))) {
                $locale = $sessionLocale;
                $source = 'session';
            }
        }
        
        // 4. If still no locale, use fallback from config
        if (!$locale) {
            $locale = config('app.fallback_locale', 'en');
            $source = 'fallback_config';
        }
        
        // Final validation (e.g., ensure $locale is one of the allowed codes)
        if (!in_array($locale, config('app.available_locales_codes', ['en', 'fr']))) {
            $original_locale_before_force_fallback = $locale;
            $locale = config('app.fallback_locale', 'en'); 
            $source .= '_forced_to_fallback (original: ' . $original_locale_before_force_fallback . ')';
        }
        
        App::setLocale($locale);
        // ALWAYS update session to the determined locale for consistency
        Session::put('locale', $locale); 
        
        Log::debug('SetLocale Middleware: Locale determined from ' . $source . ', set to: ' . $locale . ' for path: ' . $request->path());
        
        return $next($request);
    }
} 