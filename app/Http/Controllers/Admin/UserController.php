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
        
        $query = User::with(['role', 'permitCategories']);
        
        // Apply filters
        if ($roleFilter) {
            $query->where('role_id', $roleFilter);
        }
        
        if ($permitCategoryFilter) {
            $query->whereHas('permitCategories', function($q) use ($permitCategoryFilter) {
                $q->where('permit_categories.id', $permitCategoryFilter);
            });
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
            'permit_category_ids' => ['nullable', 'array'],
            'permit_category_ids.*' => ['exists:permit_categories,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        
        // Only update password if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        // Sync permit categories
        if (isset($validated['permit_category_ids'])) {
            $user->permitCategories()->sync($validated['permit_category_ids']);
        } else {
            $user->permitCategories()->detach();
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Update the permit categories for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePermitCategory(Request $request, User $user)
    {
        $validated = $request->validate([
            'permit_category_ids' => ['nullable', 'array'],
            'permit_category_ids.*' => ['exists:permit_categories,id'],
        ]);
        
        // Sync permit categories
        if (isset($validated['permit_category_ids'])) {
            $user->permitCategories()->sync($validated['permit_category_ids']);
        } else {
            $user->permitCategories()->detach();
        }
        
        return redirect()->back()
            ->with('success', 'Permit categories updated successfully.');
    }

    /**
     * Remove a specific permit category from a user.
     *
     * @param  \App\Models\User  $user
     * @param  int  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removePermitCategory(User $user, $category)
    {
        // First, check if the user has this permit category
        if ($user->hasPermitCategory($category)) {
            // Detach only this specific permit category
            $user->permitCategories()->detach($category);
            
            return redirect()->back()
                ->with('success', 'Permit category removed successfully.');
        }
        
        return redirect()->back()
            ->with('error', 'User does not have this permit category.');
    }
} 