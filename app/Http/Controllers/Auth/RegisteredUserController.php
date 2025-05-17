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
        // Get active schools for candidates to choose from
        $schools = \App\Models\School::where('is_active', true)->orderBy('name')->get();
        
        return view('auth.register', compact('schools'));
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
            'school_id' => ['required_if:role,candidate', 'exists:schools,id'],
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
        
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
            'approval_status' => $approvalStatus,
        ];
        
        // Set school_id for candidates
        if ($request->role === 'candidate' && $request->filled('school_id')) {
            $school = \App\Models\School::find($request->school_id);
            
            // Verify the school is active and has capacity
            if ($school && $school->is_active && $school->hasCapacity()) {
                $userData['school_id'] = $school->id;
            } else {
                $errorMessage = 'The selected school is unavailable or at capacity. Please choose another school.';
                return redirect()->back()->withErrors(['school_id' => $errorMessage])->withInput();
            }
        }

        $user = User::create($userData);

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
