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
        
        // Exclude super admin users from being shown to regular admins
        if (!auth()->user()->isSuperAdmin()) {
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                $query->where('role_id', '!=', $superAdminRole->id);
            }
            
            // If the user is a regular admin, scope to only their school
            if (auth()->user()->isAdmin()) {
                $schoolId = auth()->user()->school_id;
                $query->where('school_id', $schoolId);
            }
        }
        
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
        
        // If not a super admin, remove super_admin role from the dropdown list
        if (!auth()->user()->isSuperAdmin()) {
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole && isset($roles[$superAdminRole->id])) {
                $roles = $roles->forget($superAdminRole->id);
            }
        }
        
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
        // Prevent regular admins from editing super admins
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to edit super admin users.');
        }
        
        // Prevent admins from editing users from other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to edit users from other schools.');
        }
        
        $roles = Role::orderBy('name')->pluck('name', 'id');
        
        // If not a super admin, remove super_admin role from the dropdown
        if (!auth()->user()->isSuperAdmin()) {
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole && isset($roles[$superAdminRole->id])) {
                $roles = $roles->forget($superAdminRole->id);
            }
        }
        
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
        // Prevent regular admins from updating super admins
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to update super admin users.');
        }
        
        // Prevent admins from updating users from other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to update users from other schools.');
        }
        
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
        
        // Prevent regular admins from setting a user's role to super admin
        if (!auth()->user()->isSuperAdmin()) {
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole && isset($validated['role_id']) && $validated['role_id'] == $superAdminRole->id) {
                return redirect()->route('admin.users.edit', $user)
                    ->with('error', 'You do not have permission to assign the super admin role.')
                    ->withInput();
            }
        }
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        
        // Ensure school_id remains the same for admins
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            // Regular admins can only assign users to their own school
            $user->school_id = auth()->user()->school_id;
        }
        
        // Only update password if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        // Update approval status and active status if provided
        if (isset($validated['approval_status'])) {
            // If changing to approved and user is a candidate, check school capacity
            if ($validated['approval_status'] === 'approved' && 
                $user->approval_status !== 'approved' && 
                $user->isCandidate() && 
                $user->school_id) {
                
                $school = \App\Models\School::find($user->school_id);
                if ($school && !$school->hasCapacity()) {
                    return redirect()->route('admin.users.edit', $user)
                        ->with('error', 'Cannot approve candidate. School has reached its candidate limit. Please contact a super admin to increase the limit.')
                        ->withInput();
                }
            }
            
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
            // If activating a candidate, check school capacity
            if ($validated['is_active'] && !$user->is_active && $user->isCandidate() && $user->school_id) {
                $school = \App\Models\School::find($user->school_id);
                if ($school && !$school->hasCapacity()) {
                    return redirect()->route('admin.users.edit', $user)
                        ->with('error', 'Cannot activate candidate. School has reached its candidate limit. Please contact a super admin to increase the limit.')
                        ->withInput();
                }
            }
            
            $user->is_active = $validated['is_active'];
        }
        
        // Update expiration date if provided
        if (isset($validated['expires_at'])) {
            $user->expires_at = $validated['expires_at'];
        }
        
        // Update permissions if provided
        if (isset($validated['permit_category_ids'])) {
            $user->permitCategories()->sync($validated['permit_category_ids'] ?? []);
        }
        
        $user->save();
        
        // Update the school's active candidate count if this is a candidate
        if ($user->isCandidate() && $user->school_id) {
            $school = \App\Models\School::find($user->school_id);
            if ($school) {
                $school->updateActiveCandidateCount();
            }
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
        // Prevent regular admins from updating super admin permit categories
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->back()
                ->with('error', 'You do not have permission to update permit categories for super admin users.');
        }
        
        // Prevent admins from updating permit categories for users from other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->back()
                ->with('error', 'You do not have permission to update permit categories for users from other schools.');
        }
        
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
        // Prevent regular admins from removing super admin permit categories
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->back()
                ->with('error', 'You do not have permission to remove permit categories from super admin users.');
        }
        
        // Prevent admins from removing permit categories from users of other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->back()
                ->with('error', 'You do not have permission to remove permit categories from users of other schools.');
        }
        
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
     * Show the approval form for a user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showApprove(User $user)
    {
        // Prevent regular admins from approving super admins
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to approve super admin users.');
        }
        
        // Prevent admins from approving users from other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to approve users from other schools.');
        }
        
        return view('admin.users.approve', compact('user'));
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
        // Prevent regular admins from approving super admins
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to approve super admin users.');
        }
        
        // Prevent admins from approving users from other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to approve users from other schools.');
        }
        
        // Check if the user is a candidate and their school has capacity
        if ($user->isCandidate() && $user->school_id) {
            $school = \App\Models\School::find($user->school_id);
            
            if ($school && !$school->hasCapacity()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot approve candidate. School has reached its candidate limit. Please contact a super admin to increase the limit.');
            }
        }
        
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
        
        // If this is a candidate and they were approved, update the school's active candidate count
        if ($user->isCandidate() && $user->school_id) {
            $school = \App\Models\School::find($user->school_id);
            if ($school) {
                $school->updateActiveCandidateCount();
            }
        }
        
        // Here you can add code to send an approval notification email
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User approved successfully.');
    }

    /**
     * Show the rejection form for a user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showReject(User $user)
    {
        // Prevent regular admins from rejecting super admins
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to reject super admin users.');
        }
        
        // Prevent admins from rejecting users from other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to reject users from other schools.');
        }
        
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
        // Prevent regular admins from rejecting super admins
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to reject super admin users.');
        }
        
        // Prevent admins from rejecting users from other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to reject users from other schools.');
        }
        
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
        // Prevent regular admins from toggling super admin status
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to change the status of super admin users.');
        }
        
        // Prevent admins from toggling status of users from other schools
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $user->school_id !== auth()->user()->school_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to change the status of users from other schools.');
        }
        
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "User account has been {$status}.");
    }
} 