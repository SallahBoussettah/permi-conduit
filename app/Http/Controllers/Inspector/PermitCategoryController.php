<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\PermitCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermitCategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:inspector']);
    }

    /**
     * Display a listing of the permit categories.
     */
    public function index()
    {
        // Make sure to retrieve all permit categories regardless of status
        $permitCategories = PermitCategory::orderBy('name')->paginate(10);
        return view('inspector.permit-categories.index', compact('permitCategories'));
    }

    /**
     * Display the specified permit category.
     */
    public function show(PermitCategory $permitCategory)
    {
        return view('inspector.permit-categories.show', compact('permitCategory'));
    }
} 