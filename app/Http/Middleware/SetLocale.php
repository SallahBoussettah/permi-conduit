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
        // Always use French
        $locale = 'fr';
        
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        Log::debug('SetLocale Middleware: Locale set to French for path: ' . $request->path());
        
        return $next($request);
    }
} 