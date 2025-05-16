<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:candidate,inspector'],
            'terms' => ['required', 'accepted'],
        ]);

        // Get role ID from role name
        $roleId = Role::where('name', $request->role)->first()->id ?? null;
        
        if (!$roleId) {
            // Create the role if it doesn't exist
            $role = Role::create(['name' => $request->role]);
            $roleId = $role->id;
        }

        // Only set inspectors to automatically approved if registered by an admin
        // Candidates will always be pending approval
        $approvalStatus = 'pending';
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
            'approval_status' => $approvalStatus,
        ]);

        event(new Registered($user));

        // If user is a candidate, redirect to pending approval page
        if ($request->role === 'candidate') {
            return redirect()->route('registration.pending');
        }

        // Only automatically log in approved users
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
    
    /**
     * Display the registration pending approval page.
     */
    public function showPendingApproval(): View
    {
        return view('auth.pending-approval');
    }
}
