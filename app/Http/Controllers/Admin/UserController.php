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
        $statusFilter = $request->get('status');
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
        
        // Apply approval status filter
        if ($statusFilter) {
            if ($statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($statusFilter === 'inactive') {
                $query->where('is_active', false);
            } elseif (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
                $query->where('approval_status', $statusFilter);
            }
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
        
        return view('admin.users.index', compact('users', 'roles', 'permitCategories', 'roleFilter', 'permitCategoryFilter', 'statusFilter', 'search'));
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
            'approval_status' => ['nullable', 'in:pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'required_if:approval_status,rejected'],
            'is_active' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        
        // Only update password if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        // Update approval status and active status if provided
        if (isset($validated['approval_status'])) {
            $user->approval_status = $validated['approval_status'];
            
            // If status is changing to approved, set approved_at and approved_by
            if ($validated['approval_status'] === 'approved' && $user->approval_status !== 'approved') {
                $user->approved_at = now();
                $user->approved_by = auth()->id();
            }
            
            // If status is changing to rejected, set rejection reason
            if ($validated['approval_status'] === 'rejected') {
                $user->rejection_reason = $validated['rejection_reason'] ?? null;
            }
        }
        
        // Update active status if provided
        if (isset($validated['is_active'])) {
            $user->is_active = $validated['is_active'];
        }
        
        // Update expiration date if provided
        if (isset($validated['expires_at'])) {
            $user->expires_at = $validated['expires_at'];
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

    /**
     * Approve a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, User $user)
    {
        $validated = $request->validate([
            'expiration_type' => ['required', 'in:none,days,date'],
            'expires_after' => ['nullable', 'integer', 'min:1', 'required_if:expiration_type,days'],
            'expiration_date' => ['nullable', 'date', 'required_if:expiration_type,date'],
        ]);
        
        $user->approval_status = 'approved';
        $user->approved_at = now();
        $user->approved_by = auth()->id();
        $user->rejection_reason = null;
        $user->is_active = true;
        
        // Set expiration date based on the selected option
        if ($validated['expiration_type'] === 'days' && isset($validated['expires_after']) && $validated['expires_after'] > 0) {
            $user->expires_at = now()->addDays($validated['expires_after']);
        } elseif ($validated['expiration_type'] === 'date' && isset($validated['expiration_date'])) {
            $user->expires_at = $validated['expiration_date'];
        } else {
            // No expiration (none)
            $user->expires_at = null;
        }
        
        $user->save();
        
        // Here you can add code to send an approval notification email
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User has been approved successfully.' . 
                ($user->expires_at ? ' Account will expire on ' . $user->expires_at->format('Y-m-d') : ''));
    }

    /**
     * Show the approval form for a user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function showApprove(User $user)
    {
        return view('admin.users.approve', compact('user'));
    }

    /**
     * Show the rejection form for a user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function showReject(User $user)
    {
        return view('admin.users.reject', compact('user'));
    }

    /**
     * Reject a user with a reason.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, User $user)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);
        
        $user->approval_status = 'rejected';
        $user->rejection_reason = $validated['rejection_reason'];
        $user->save();
        
        // Here you can add code to send a rejection notification email
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User has been rejected.');
    }

    /**
     * Toggle the active status of a user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "User account has been {$status}.");
    }
} 