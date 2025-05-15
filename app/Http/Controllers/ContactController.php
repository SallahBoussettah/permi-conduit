<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\ContactFormMail;
use App\Mail\ContactConfirmationMail;

class ContactController extends Controller
{
    /**
     * Show the contact form.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('contact');
    }

    /**
     * Handle the contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        // Validate form data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Admin email address - in production, this would be your actual admin email
        $adminEmail = env('ADMIN_EMAIL', '1899rp.store@gmail.com');

        // Send email
        try {
            // Send the contact form message to admin, appearing to be from the sender
            Mail::to($adminEmail)->send(new ContactFormMail($request->all()));
            
            // Send confirmation email to the sender
            Mail::to($request->email)->send(new ContactConfirmationMail($request->all()));
            
            return redirect()->back()->with('success', __('app.contact_success'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('app.contact_error'))
                ->withInput();
        }
    }
} 