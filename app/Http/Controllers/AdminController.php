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

        // Create user data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'email_verified_at' => now(), // Auto-verify inspector emails
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ];
        
        // Set the school_id to the same as the admin's school
        if (auth()->user()->school_id) {
            $userData['school_id'] = auth()->user()->school_id;
        }

        // Create the user
        $user = User::create($userData);

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
        $user = auth()->user();
        $query = User::whereHas('role', function ($query) {
            $query->where('name', 'inspector');
        });
        
        // Scope to the admin's school
        if (!$user->isSuperAdmin() && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }
        
        $inspectors = $query->orderBy('name')->paginate(10);
        
        return view('admin.inspectors.index', compact('inspectors'));
    }

    /**
     * Show form to edit an inspector.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function editInspector($id)
    {
        $inspector = User::findOrFail($id);
        
        // Make sure the user is an inspector
        if (!$inspector->hasRole('inspector')) {
            return redirect()->route('admin.inspectors')
                ->with('error', __('Cet utilisateur n\'est pas un inspecteur.'));
        }
        
        return view('admin.inspectors.edit', compact('inspector'));
    }

    /**
     * Update the specified inspector.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateInspector(Request $request, $id)
    {
        $inspector = User::findOrFail($id);
        
        // Make sure the user is an inspector
        if (!$inspector->hasRole('inspector')) {
            return redirect()->route('admin.inspectors')
                ->with('error', __('Cet utilisateur n\'est pas un inspecteur.'));
        }
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($inspector->id)],
        ];
        
        // Only validate password if it's being updated
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }
        
        $request->validate($rules);
        
        // Update basic info
        $inspector->name = $request->name;
        $inspector->email = $request->email;
        
        // Update password if provided
        if ($request->filled('password')) {
            $inspector->password = Hash::make($request->password);
        }
        
        $inspector->save();
        
        return redirect()->route('admin.inspectors')
            ->with('success', __('Inspecteur mis à jour avec succès.'));
    }

    /**
     * Toggle the active status of an inspector.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleInspectorActive($id)
    {
        $inspector = User::findOrFail($id);
        
        // Make sure the user is an inspector
        if (!$inspector->hasRole('inspector')) {
            return redirect()->route('admin.inspectors')
                ->with('error', __('Cet utilisateur n\'est pas un inspecteur.'));
        }
        
        $inspector->is_active = !$inspector->is_active;
        $inspector->save();
        
        $message = $inspector->is_active 
            ? __('Inspecteur activé avec succès.')
            : __('Inspecteur désactivé avec succès.');
            
        return redirect()->route('admin.inspectors')
            ->with('success', $message);
    }

    /**
     * Delete an inspector.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteInspector($id)
    {
        $inspector = User::findOrFail($id);
        
        // Make sure the user is an inspector
        if (!$inspector->hasRole('inspector')) {
            return redirect()->route('admin.inspectors')
                ->with('error', __('Cet utilisateur n\'est pas un inspecteur.'));
        }
        
        // Check if the inspector has any related records before deleting
        $canDelete = true;
        $reason = '';
        
        // Delete the inspector if safe to do so
        if ($canDelete) {
            $name = $inspector->name;
            $inspector->delete();
            
            return redirect()->route('admin.inspectors')
                ->with('success', __('Inspecteur ":name" supprimé avec succès.', ['name' => $name]));
        } else {
            return redirect()->route('admin.inspectors')
                ->with('error', __('Impossible de supprimer l\'inspecteur: :reason', ['reason' => $reason]));
        }
    }
} 