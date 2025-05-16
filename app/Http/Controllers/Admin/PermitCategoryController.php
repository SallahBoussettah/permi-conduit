<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermitCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermitCategoryController extends Controller
{
    /**
     * Display a listing of the permit categories.
     */
    public function index()
    {
        $permitCategories = PermitCategory::orderBy('name')->paginate(10);
        return view('admin.permit-categories.index', compact('permitCategories'));
    }

    /**
     * Show the form for creating a new permit category.
     */
    public function create()
    {
        return view('admin.permit-categories.create');
    }

    /**
     * Store a newly created permit category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:permit_categories,code',
            'description' => 'nullable|string',
        ]);

        // Explicitly handle the status checkbox
        $validated['status'] = $request->has('status');

        $permitCategory = PermitCategory::create($validated);

        return redirect()->route('admin.permit-categories.index')
            ->with('success', 'Permit category created successfully.');
    }

    /**
     * Display the specified permit category.
     */
    public function show(PermitCategory $permitCategory)
    {
        return view('admin.permit-categories.show', compact('permitCategory'));
    }

    /**
     * Show the form for editing the specified permit category.
     */
    public function edit(PermitCategory $permitCategory)
    {
        return view('admin.permit-categories.edit', compact('permitCategory'));
    }

    /**
     * Update the specified permit category in storage.
     */
    public function update(Request $request, PermitCategory $permitCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:permit_categories,code,' . $permitCategory->id,
            'description' => 'nullable|string',
        ]);

        // Explicitly handle the status checkbox
        $validated['status'] = $request->has('status');

        $permitCategory->update($validated);

        return redirect()->route('admin.permit-categories.index')
            ->with('success', 'Permit category updated successfully.');
    }

    /**
     * Remove the specified permit category from storage.
     */
    public function destroy(PermitCategory $permitCategory)
    {
        // Check if permit category is used by any courses
        if ($permitCategory->courses()->count() > 0) {
            return back()->with('error', 'Cannot delete permit category that is used by courses.');
        }

        // Check if permit category is used by any users
        if ($permitCategory->users()->count() > 0) {
            return back()->with('error', 'Cannot delete permit category that is assigned to users.');
        }

        $permitCategory->delete();

        return redirect()->route('admin.permit-categories.index')
            ->with('success', 'Permit category deleted successfully.');
    }
} 