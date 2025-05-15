<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Config;

class LanguageController extends Controller
{
    /**
     * Change the application language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request, $locale)
    {
        // Force the locale to be one of our available locales
        if (!in_array($locale, ['en', 'fr'])) {
            $locale = 'fr'; // Default to French
        }
        
        // Set the locale in the session
        Session::put('locale', $locale);
        
        // Set the application locale
        App::setLocale($locale);
        Config::set('app.locale', $locale);
        
        // Create a cookie that lasts for 1 year
        $cookie = cookie('locale', $locale, 525600); // 1 year in minutes
        
        // Log the change
        Log::info('Language switched to: ' . $locale);
        
        // Get the URL to redirect to
        $redirect = $request->query('redirect', null);
        
        // If no redirect URL is specified, use the referer
        if (!$redirect) {
            $referer = $request->headers->get('referer');
            
            // If referer is available and not a language route
            if ($referer && !str_contains($referer, '/language/') && !str_contains($referer, '/en') && !str_contains($referer, '/fr')) {
                $redirect = $referer;
            } else {
                // Default to home page
                $redirect = url('/');
            }
        }
        
        // Add a query parameter to bust cache
        $redirectUrl = $redirect . (parse_url($redirect, PHP_URL_QUERY) ? '&' : '?') . 'lang=' . $locale . '&t=' . time();
        
        // Redirect with cookie
        return redirect($redirectUrl)->withCookie($cookie);
    }
} 