<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\AdminController;
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
    
    // Dashboard - already accessible to all authenticated users
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Admin routes
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Inspector management
        Route::get('/inspectors', [AdminController::class, 'listInspectors'])->name('inspectors');
        Route::get('/inspectors/register', [AdminController::class, 'showRegisterInspector'])->name('register.inspector');
        Route::post('/inspectors/register', [AdminController::class, 'registerInspector'])->name('register.inspector.submit');
        
        // User management
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/permit-category', [\App\Http\Controllers\Admin\UserController::class, 'updatePermitCategory'])->name('users.update-permit-category');
        Route::delete('/users/{user}/permit-category/{category}', [\App\Http\Controllers\Admin\UserController::class, 'removePermitCategory'])->name('users.remove-permit-category');
        
        // Permit Categories management
        Route::resource('permit-categories', \App\Http\Controllers\Admin\PermitCategoryController::class);
    });
    
    // Inspector routes
    Route::middleware(['auth', 'role:inspector'])->prefix('inspector')->name('inspector.')->group(function () {
        // Permit Categories - read only
        Route::get('/permit-categories', [\App\Http\Controllers\Inspector\PermitCategoryController::class, 'index'])->name('permit-categories.index');
        Route::get('/permit-categories/{permitCategory}', [\App\Http\Controllers\Inspector\PermitCategoryController::class, 'show'])->name('permit-categories.show');
        
        // Courses
        Route::get('/courses', [\App\Http\Controllers\Inspector\CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [\App\Http\Controllers\Inspector\CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [\App\Http\Controllers\Inspector\CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}', [\App\Http\Controllers\Inspector\CourseController::class, 'show'])->name('courses.show');
        Route::get('/courses/{course}/edit', [\App\Http\Controllers\Inspector\CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [\App\Http\Controllers\Inspector\CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [\App\Http\Controllers\Inspector\CourseController::class, 'destroy'])->name('courses.destroy');
        
        // Course Materials
        Route::get('/courses/{course}/materials', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'index'])->name('courses.materials.index');
        Route::get('/courses/{course}/materials/create', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'create'])->name('courses.materials.create');
        Route::post('/courses/{course}/materials', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'store'])->name('courses.materials.store');
        Route::get('/courses/{course}/materials/{material}', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'show'])->name('courses.materials.show');
        Route::get('/courses/{course}/materials/{material}/edit', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'edit'])->name('courses.materials.edit');
        Route::put('/courses/{course}/materials/{material}', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'update'])->name('courses.materials.update');
        Route::delete('/courses/{course}/materials/{material}', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'destroy'])->name('courses.materials.destroy');
        Route::post('/courses/{course}/materials/update-order', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'updateOrder'])->name('courses.materials.update-order');
        Route::get('/courses/{course}/materials/{material}/pdf', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'servePdf'])->name('courses.materials.pdf');
    });
    
    // Candidate routes
    Route::middleware(['auth', 'role:candidate'])->prefix('candidate')->name('candidate.')->group(function () {
        // Courses
        Route::get('/courses', [\App\Http\Controllers\Candidate\CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [\App\Http\Controllers\Candidate\CourseController::class, 'show'])->name('courses.show');
        
        // Course Materials
        Route::get('/courses/{course}/materials/{material}', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'show'])->name('courses.materials.show');
        Route::get('/courses/{course}/materials/{material}/pdf', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'servePdf'])->name('courses.materials.pdf');
        Route::post('/courses/{course}/materials/{material}/progress', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'updateProgress'])->name('courses.materials.progress');
        Route::post('/courses/{course}/materials/{material}/complete', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'markAsComplete'])->name('courses.materials.complete');
    });
});

require __DIR__.'/auth.php';
