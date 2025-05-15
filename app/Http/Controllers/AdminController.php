<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Show form to register a new inspector.
     *
     * @return \Illuminate\View\View
     */
    public function showRegisterInspector()
    {
        return view('admin.register-inspector');
    }

    /**
     * Register a new inspector.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerInspector(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        // Get inspector role ID
        $role = Role::where('name', 'inspector')->first();
        if (!$role) {
            $role = Role::create(['name' => 'inspector']);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'email_verified_at' => now(), // Auto-verify inspector emails
        ]);

        event(new Registered($user));

        return redirect()->route('admin.inspectors')
            ->with('success', __('Inspector registered successfully.'));
    }

    /**
     * Show list of inspectors.
     *
     * @return \Illuminate\View\View
     */
    public function listInspectors()
    {
        $inspectorRole = Role::where('name', 'inspector')->first();
        
        if (!$inspectorRole) {
            $inspectors = collect();
        } else {
            $inspectors = User::where('role_id', $inspectorRole->id)->paginate(10);
        }

        return view('admin.inspectors', compact('inspectors'));
    }
} 