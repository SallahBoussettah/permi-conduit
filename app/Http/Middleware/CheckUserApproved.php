<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // If user is not approved, redirect to pending approval page
            if ($user->approval_status === 'pending') {
                return redirect()->route('registration.pending');
            }
            
            // If user is rejected, redirect to rejected page with reason
            if ($user->approval_status === 'rejected') {
                return redirect()->route('registration.rejected')
                    ->with('reason', $user->rejection_reason);
            }
            
            // If user account is inactive, redirect to inactive page
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('account.inactive');
            }
        }
        
        return $next($request);
    }
}
