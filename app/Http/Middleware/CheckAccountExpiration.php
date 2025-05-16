<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAccountExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if the user's account has expired and is currently active
            if ($user->hasExpired() && $user->is_active) {
                // Deactivate the account
                $user->is_active = false;
                $user->save();
                
                // Log the user out
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', __('Your account has expired and has been deactivated. Please contact an administrator for assistance.'));
            }
        }
        
        return $next($request);
    }
}
