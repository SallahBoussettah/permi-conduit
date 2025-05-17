<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $routeSchoolId = $request->route('school') ? $request->route('school')->id : null;
        
        // Super admins can access all schools
        if ($user->isSuperAdmin()) {
            return $next($request);
        }
        
        // If the user is not associated with any school
        if (!$user->school_id) {
            return redirect('/dashboard')->with('error', 'You are not associated with any school.');
        }
        
        // If a specific school is requested, check if the user belongs to it
        if ($routeSchoolId && $user->school_id != $routeSchoolId) {
            return redirect('/dashboard')->with('error', 'You do not have permission to access this school.');
        }
        
        return $next($request);
    }
}