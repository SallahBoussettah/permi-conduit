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
        
        // Determine the redirect URL
        $redirectUrl = $this->getRedirectUrl($request);
        
        // Add a query parameter to bust cache
        $redirectUrl .= (parse_url($redirectUrl, PHP_URL_QUERY) ? '&' : '?') . 'lang=' . $locale . '&t=' . time();
        
        // Redirect with cookie
        return redirect($redirectUrl)->withCookie($cookie);
    }
    
    /**
     * Get the URL to redirect to after language change.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getRedirectUrl(Request $request)
    {
        // If the request has a redirect parameter, use that
        if ($request->has('redirect')) {
            return $request->input('redirect');
        }
        
        // Get the referer
        $referer = $request->headers->get('referer');
        
        // If referer exists and is not a language route
        if ($referer) {
            // URL decode the referer
            $referer = urldecode($referer);
            
            // Check if the referer is a language switch route
            if (!preg_match('#/language/|/en$|/fr$#', $referer)) {
                return $referer;
            }
        }
        
        // If no valid referer, fall back to the home page
        return url('/');
    }
} 