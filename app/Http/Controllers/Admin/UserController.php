<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\PermitCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $roleFilter = $request->get('role');
        $permitCategoryFilter = $request->get('permit_category');
        $search = $request->get('search');
        
        $query = User::with(['role', 'permitCategory']);
        
        // Apply filters
        if ($roleFilter) {
            $query->where('role_id', $roleFilter);
        }
        
        if ($permitCategoryFilter) {
            $query->where('permit_category_id', $permitCategoryFilter);
        }
        
        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('name')->paginate(10);
        $roles = Role::orderBy('name')->pluck('name', 'id');
        $permitCategories = PermitCategory::orderBy('name')->pluck('name', 'id');
        
        return view('admin.users.index', compact('users', 'roles', 'permitCategories', 'roleFilter', 'permitCategoryFilter', 'search'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name', 'id');
        $permitCategories = PermitCategory::where('status', true)->orderBy('name')->pluck('name', 'id');
        
        return view('admin.users.edit', compact('user', 'roles', 'permitCategories'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'role_id' => ['required', 'exists:roles,id'],
            'permit_category_id' => ['nullable', 'exists:permit_categories,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        $user->permit_category_id = $validated['permit_category_id'];
        
        // Only update password if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Update only the permit category for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePermitCategory(Request $request, User $user)
    {
        $validated = $request->validate([
            'permit_category_id' => ['nullable', 'exists:permit_categories,id'],
        ]);
        
        $user->permit_category_id = $validated['permit_category_id'];
        $user->save();
        
        return redirect()->back()
            ->with('success', 'Permit category updated successfully.');
    }
} 