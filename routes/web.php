<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Legal Pages
Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('privacy');
Route::get('/terms-of-service', [LegalController::class, 'terms'])->name('terms');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Direct language routes for easier access
Route::get('/en', function (Request $request) {
    App::setLocale('en');
    Session::put('locale', 'en');
    Config::set('app.locale', 'en');
    
    $cookie = cookie('locale', 'en', 525600); // 1 year
    
    // Get the previous URL or default to home
    $previousUrl = url()->previous();
    $baseUrl = url('/');
    
    // If the previous URL is not the current language route
    if (!str_contains($previousUrl, '/en') && !str_contains($previousUrl, '/fr')) {
        $redirectUrl = $previousUrl;
    } else {
        // If it was a language route, redirect to the home page
        $redirectUrl = $baseUrl;
    }
    
    // Add cache-busting parameter
    $redirectUrl .= (parse_url($redirectUrl, PHP_URL_QUERY) ? '&' : '?') . 'lang=en&t=' . time();
    
    return redirect($redirectUrl)->withCookie($cookie);
})->name('english');

Route::get('/fr', function (Request $request) {
    App::setLocale('fr');
    Session::put('locale', 'fr');
    Config::set('app.locale', 'fr');
    
    $cookie = cookie('locale', 'fr', 525600); // 1 year
    
    // Get the previous URL or default to home
    $previousUrl = url()->previous();
    $baseUrl = url('/');
    
    // If the previous URL is not the current language route
    if (!str_contains($previousUrl, '/en') && !str_contains($previousUrl, '/fr')) {
        $redirectUrl = $previousUrl;
    } else {
        // If it was a language route, redirect to the home page
        $redirectUrl = $baseUrl;
    }
    
    // Add cache-busting parameter
    $redirectUrl .= (parse_url($redirectUrl, PHP_URL_QUERY) ? '&' : '?') . 'lang=fr&t=' . time();
    
    return redirect($redirectUrl)->withCookie($cookie);
})->name('french');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
