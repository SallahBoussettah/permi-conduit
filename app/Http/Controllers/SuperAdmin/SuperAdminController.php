<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SuperAdminController extends Controller
{
    /**
     * Display the super admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Get counts for the dashboard
        $schoolsCount = School::count();
        $adminsCount = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->count();
        $inspectorsCount = User::whereHas('role', function($q) {
            $q->where('name', 'inspector');
        })->count();
        $candidatesCount = User::whereHas('role', function($q) {
            $q->where('name', 'candidate');
        })->count();
        
        return view('super_admin.dashboard', compact(
            'schoolsCount', 
            'adminsCount', 
            'inspectorsCount', 
            'candidatesCount'
        ));
    }
    
    /**
     * Show the school management page.
     *
     * @return \Illuminate\View\View
     */
    public function schools()
    {
        $schools = School::withCount(['users', 'candidates', 'admins', 'inspectors'])
            ->orderBy('name')
            ->paginate(10);
            
        return view('super_admin.schools.index', compact('schools'));
    }
    
    /**
     * Show the form to create a new school.
     *
     * @return \Illuminate\View\View
     */
    public function createSchool()
    {
        return view('super_admin.schools.create');
    }
    
    /**
     * Store a newly created school.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSchool(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schools,name',
            'slug' => 'nullable|string|max:255|unique:schools,slug',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'candidate_limit' => 'required|integer|min:1',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($request->name) . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs('logos', $logoName, 'public');
            $validated['logo_path'] = 'logos/' . $logoName;
        }
        
        // Set created_by
        $validated['created_by'] = Auth::id();
        
        // Create the school
        $school = School::create($validated);
        
        return redirect()->route('super_admin.schools')
            ->with('success', 'School created successfully.');
    }
    
    /**
     * Show the form to edit a school.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\View\View
     */
    public function editSchool(School $school)
    {
        return view('super_admin.schools.edit', compact('school'));
    }
    
    /**
     * Update the specified school.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\School  $school
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSchool(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schools,name,' . $school->id,
            'slug' => 'nullable|string|max:255|unique:schools,slug,' . $school->id,
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'candidate_limit' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }
            
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($request->name) . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs('logos', $logoName, 'public');
            $validated['logo_path'] = 'logos/' . $logoName;
        }
        
        // Set updated_by
        $validated['updated_by'] = Auth::id();
        
        // Update the school
        $school->update($validated);
        
        return redirect()->route('super_admin.schools')
            ->with('success', 'School updated successfully.');
    }
    
    /**
     * Delete the specified school.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroySchool(School $school)
    {
        // Check if the school has any users
        if ($school->users()->count() > 0) {
            return redirect()->route('super_admin.schools')
                ->with('error', 'Cannot delete school with users. Please remove all users first.');
        }
        
        // Delete logo if exists
        if ($school->logo_path) {
            Storage::disk('public')->delete($school->logo_path);
        }
        
        // Delete the school
        $school->delete();
        
        return redirect()->route('super_admin.schools')
            ->with('success', 'School deleted successfully.');
    }
    
    /**
     * Show the school admins management page.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\View\View
     */
    public function schoolAdmins(School $school)
    {
        $admins = $school->admins()->paginate(10);
        return view('super_admin.schools.admins', compact('school', 'admins'));
    }
    
    /**
     * Show the form to assign a new admin to a school.
     *
     * @param  \App\Models\School  $school
     * @return \Illuminate\View\View
     */
    public function assignAdmin(School $school)
    {
        return view('super_admin.schools.assign_admin', compact('school'));
    }
    
    /**
     * Store a newly created admin for a school.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\School  $school
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAdmin(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Get admin role ID
        $adminRoleId = Role::where('name', 'admin')->first()->id;
        
        // Create admin user
        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $adminRoleId,
            'school_id' => $school->id,
            'is_active' => true,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);
        
        return redirect()->route('super_admin.school.admins', $school)
            ->with('success', 'Admin assigned successfully.');
    }
}