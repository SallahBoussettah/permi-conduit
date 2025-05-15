<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect('login');
        }

        // Check if user has the required role
        if (Auth::user()->role && Auth::user()->role->name === $role) {
            return $next($request);
        }

        // If role is a comma-separated list, check if user has any of the roles
        $roles = explode(',', $role);
        if (count($roles) > 1) {
            foreach ($roles as $r) {
                if (Auth::user()->role && Auth::user()->role->name === trim($r)) {
                    return $next($request);
                }
            }
        }

        // Redirect to home or abort with 403 Forbidden
        return abort(403, 'Unauthorized action.');
    }
}
