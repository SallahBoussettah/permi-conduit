<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Display the privacy policy page.
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('legal.privacy');
    }

    /**
     * Display the terms of service page.
     *
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view('legal.terms');
    }
} 