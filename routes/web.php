<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Legal Pages
Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('privacy');
Route::get('/terms-of-service', [LegalController::class, 'terms'])->name('terms');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Direct language route for easier access (French only)
Route::get('/fr', function (Request $request) {
    App::setLocale('fr');
    Session::put('locale', 'fr');
    Config::set('app.locale', 'fr');
    
    $cookie = cookie('locale', 'fr', 525600); // 1 year
    
    // Get the previous URL or default to home
    $previousUrl = url()->previous();
    $baseUrl = url('/');
    
    // If the previous URL is not the current language route
    if (!str_contains($previousUrl, '/fr')) {
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
})->middleware(['auth', 'verified', 'App\Http\Middleware\CheckUserApproved'])->name('dashboard');

Route::middleware(['auth', 'App\Http\Middleware\CheckUserApproved'])->group(function () {
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
        Route::get('/inspectors/{id}/edit', [AdminController::class, 'editInspector'])->name('inspectors.edit');
        Route::put('/inspectors/{id}', [AdminController::class, 'updateInspector'])->name('inspectors.update');
        Route::post('/inspectors/{id}/toggle-active', [AdminController::class, 'toggleInspectorActive'])->name('inspectors.toggle-active');
        Route::delete('/inspectors/{id}', [AdminController::class, 'deleteInspector'])->name('inspectors.delete');
        
        // User management
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/permit-category', [\App\Http\Controllers\Admin\UserController::class, 'updatePermitCategory'])->name('users.update-permit-category');
        Route::delete('/users/{user}/permit-category/{category}', [\App\Http\Controllers\Admin\UserController::class, 'removePermitCategory'])->name('users.remove-permit-category');
        
        // User approval management
        Route::post('/users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
        Route::get('/users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'showApprove'])->name('users.show-approve');
        Route::get('/users/{user}/reject', [\App\Http\Controllers\Admin\UserController::class, 'showReject'])->name('users.show-reject');
        Route::post('/users/{user}/reject', [\App\Http\Controllers\Admin\UserController::class, 'reject'])->name('users.reject');
        Route::post('/users/{user}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
        
        // Permit Categories management
        Route::resource('permit-categories', \App\Http\Controllers\Admin\PermitCategoryController::class);
        
        // QCM Reports
        Route::get('/qcm-reports', [\App\Http\Controllers\Admin\QcmReportController::class, 'index'])->name('qcm-reports.index');
        Route::get('/qcm-reports/candidates', [\App\Http\Controllers\Admin\QcmReportController::class, 'candidates'])->name('qcm-reports.candidates');
        Route::get('/qcm-reports/candidate/{user}', [\App\Http\Controllers\Admin\QcmReportController::class, 'candidateDetail'])->name('qcm-reports.candidate-detail');
        Route::get('/qcm-reports/statistics', [\App\Http\Controllers\Admin\QcmReportController::class, 'statistics'])->name('qcm-reports.statistics');
        Route::get('/qcm-reports/export', [\App\Http\Controllers\Admin\QcmReportController::class, 'export'])->name('qcm-reports.export');
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
        Route::get('/courses/{course}/materials/{material}/audio', [\App\Http\Controllers\Inspector\CourseMaterialController::class, 'serveAudio'])->name('courses.materials.audio');
        
        // QCM Papers
        Route::resource('qcm-papers', \App\Http\Controllers\Inspector\QcmPaperController::class, [
            'parameters' => ['qcm-papers' => 'qcmPaper']
        ]);
        
        // QCM Sections
        Route::get('/qcm-papers/{qcmPaper}/sections/create', [\App\Http\Controllers\Inspector\QcmSectionController::class, 'create'])->name('qcm-papers.sections.create');
        Route::post('/qcm-papers/{qcmPaper}/sections', [\App\Http\Controllers\Inspector\QcmSectionController::class, 'store'])->name('qcm-papers.sections.store');
        Route::get('/qcm-papers/{qcmPaper}/sections/{section}/edit', [\App\Http\Controllers\Inspector\QcmSectionController::class, 'edit'])->name('qcm-papers.sections.edit');
        Route::put('/qcm-papers/{qcmPaper}/sections/{section}', [\App\Http\Controllers\Inspector\QcmSectionController::class, 'update'])->name('qcm-papers.sections.update');
        Route::delete('/qcm-papers/{qcmPaper}/sections/{section}', [\App\Http\Controllers\Inspector\QcmSectionController::class, 'destroy'])->name('qcm-papers.sections.destroy');
        Route::post('/qcm-papers/{qcmPaper}/sections/reorder', [\App\Http\Controllers\Inspector\QcmSectionController::class, 'reorder'])->name('qcm-papers.sections.reorder');
        Route::post('/qcm-papers/{qcmPaper}/sections/{section}/move-questions', [\App\Http\Controllers\Inspector\QcmSectionController::class, 'moveQuestions'])->name('qcm-papers.sections.move-questions');
        
        // QCM Questions
        Route::get('/qcm-papers/{qcmPaper}/questions', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'index'])->name('qcm-papers.questions.index');
        Route::get('/qcm-papers/{qcmPaper}/questions/create', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'create'])->name('qcm-papers.questions.create');
        Route::post('/qcm-papers/{qcmPaper}/questions', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'store'])->name('qcm-papers.questions.store');
        Route::get('/qcm-papers/{qcmPaper}/questions/{question}', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'show'])->name('qcm-papers.questions.show');
        Route::get('/qcm-papers/{qcmPaper}/questions/{question}/edit', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'edit'])->name('qcm-papers.questions.edit');
        Route::put('/qcm-papers/{qcmPaper}/questions/{question}', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'update'])->name('qcm-papers.questions.update');
        Route::delete('/qcm-papers/{qcmPaper}/questions/{question}', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'destroy'])->name('qcm-papers.questions.destroy');
        Route::get('/qcm-papers/{qcmPaper}/activate-all', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'activateAllAnswers'])->name('qcm-papers.questions.activate-all');
        
        // Bulk Import for QCM Questions
        Route::get('/qcm-papers/{qcmPaper}/import', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'showImportForm'])->name('qcm-papers.questions.import');
        Route::post('/qcm-papers/{qcmPaper}/import', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'import'])->name('qcm-papers.questions.import.submit');
        Route::get('/qcm-papers/template/download', [\App\Http\Controllers\Inspector\QcmQuestionController::class, 'downloadTemplate'])->name('qcm-papers.questions.template');
    });
    
    // Candidate routes
    Route::middleware(['auth', 'role:candidate'])->prefix('candidate')->name('candidate.')->group(function () {
        // Courses
        Route::get('/courses', [\App\Http\Controllers\Candidate\CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [\App\Http\Controllers\Candidate\CourseController::class, 'show'])->name('courses.show');
        
        // Course Materials
        Route::get('/courses/{course}/materials/{material}', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'show'])->name('courses.materials.show');
        Route::get('/courses/{course}/materials/{material}/pdf', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'servePdf'])->name('courses.materials.pdf');
        Route::get('/courses/{course}/materials/{material}/audio', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'serveAudio'])->name('courses.materials.audio');
        Route::post('/courses/{course}/materials/{material}/progress', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'updateProgress'])->name('courses.materials.progress');
        Route::post('/courses/{course}/materials/{material}/complete', [\App\Http\Controllers\Candidate\CourseMaterialController::class, 'markAsComplete'])->name('courses.materials.complete');
        
        // QCM Exams
        Route::get('/qcm-exams', [\App\Http\Controllers\Candidate\QcmExamController::class, 'index'])->name('qcm-exams.index');
        Route::get('/qcm-exams/available', [\App\Http\Controllers\Candidate\QcmExamController::class, 'available'])->name('qcm-exams.available');
        Route::post('/qcm-exams/start', [\App\Http\Controllers\Candidate\QcmExamController::class, 'start'])->name('qcm-exams.start');
        Route::get('/qcm-exams/{qcmExam}', [\App\Http\Controllers\Candidate\QcmExamController::class, 'show'])->name('qcm-exams.show');
        Route::post('/qcm-exams/{qcmExam}/answer', [\App\Http\Controllers\Candidate\QcmExamController::class, 'answer'])->name('qcm-exams.answer');
        Route::post('/qcm-exams/{qcmExam}/submit', [\App\Http\Controllers\Candidate\QcmExamController::class, 'submit'])->name('qcm-exams.submit');
        Route::get('/qcm-exams/{qcmExam}/results', [\App\Http\Controllers\Candidate\QcmExamController::class, 'results'])->name('qcm-exams.results');
    });

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
});

// Super Admin routes
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super_admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    
    // School management
    Route::get('/schools', [SuperAdminController::class, 'schools'])->name('schools');
    Route::get('/schools/create', [SuperAdminController::class, 'createSchool'])->name('schools.create');
    Route::post('/schools', [SuperAdminController::class, 'storeSchool'])->name('schools.store');
    Route::get('/schools/{school}/edit', [SuperAdminController::class, 'editSchool'])->name('schools.edit');
    Route::put('/schools/{school}', [SuperAdminController::class, 'updateSchool'])->name('schools.update');
    Route::delete('/schools/{school}', [SuperAdminController::class, 'destroySchool'])->name('schools.destroy');
    
    // School admin management
    Route::get('/schools/{school}/admins', [SuperAdminController::class, 'schoolAdmins'])->name('school.admins');
    Route::get('/schools/{school}/admins/create', [SuperAdminController::class, 'assignAdmin'])->name('school.admins.create');
    Route::post('/schools/{school}/admins', [SuperAdminController::class, 'storeAdmin'])->name('school.admins.store');
});

// Add a debug route to check the latest logs
Route::get('/debug-latest-logs', function() {
    if (app()->environment('local')) {
        $logFile = storage_path('logs/laravel.log');
        $logContent = '';
        
        if (file_exists($logFile)) {
            $logLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logLines = array_slice($logLines, -100); // Get the last 100 lines
            $logContent = implode("\n", $logLines);
        }
        
        return response($logContent, 200)
            ->header('Content-Type', 'text/plain');
    }
    
    return 'Debug logs not available in production.';
});

// Temporary debug route to check course materials
Route::get('/debug-materials', function () {
    $materials = \App\Models\CourseMaterial::all();
    return $materials->map(function ($material) {
        return [
            'id' => $material->id,
            'title' => $material->title,
            'material_type' => $material->material_type,
            'content_path_or_url' => $material->content_path_or_url,
        ];
    });
});

// Route to debug form submissions for course material creation
Route::post('/debug-material-creation', function (Request $request) {
    \Log::info('Material Creation Debug', ['request_data' => $request->all()]);
    return response()->json([
        'submitted_data' => $request->all(),
        'material_type' => $request->material_type,
        'has_video_url' => $request->has('video_url') ? 'Yes' : 'No',
        'video_url' => $request->video_url ?? 'Not provided',
        'has_pdf_file' => $request->hasFile('pdf_file') ? 'Yes' : 'No'
    ]);
})->name('debug.material.creation');

// Route to fix material types
Route::get('/fix-material-types', function () {
    $materials = \App\Models\CourseMaterial::all();
    $fixed = 0;
    
    foreach ($materials as $material) {
        $contentPath = $material->content_path_or_url;
        $correctType = null;
        
        // Check if it's likely a YouTube video ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $contentPath)) {
            $correctType = 'video';
        } 
        // Check if it's a PDF file (likely has .pdf extension or is stored in the pdfs directory)
        else if (str_contains($contentPath, '.pdf') || str_contains($contentPath, 'pdfs/')) {
            $correctType = 'pdf';
        }
        
        // Update if we determined a different type
        if ($correctType && $material->material_type !== $correctType) {
            $material->material_type = $correctType;
            $material->save();
            $fixed++;
        }
    }
    
    // Get the updated list of materials
    $updatedMaterials = \App\Models\CourseMaterial::all()->map(function ($material) {
        return [
            'id' => $material->id,
            'title' => $material->title,
            'material_type' => $material->material_type,
            'content_path_or_url' => $material->content_path_or_url,
        ];
    });
    
    return [
        'message' => "Fixed $fixed material types",
        'materials' => $updatedMaterials
    ];
});

// Route to manually set a material type
Route::get('/set-material-type/{id}/{type}', function ($id, $type) {
    if (!in_array($type, ['pdf', 'video'])) {
        return [
            'error' => 'Invalid type. Must be "pdf" or "video".'
        ];
    }
    
    $material = \App\Models\CourseMaterial::find($id);
    
    if (!$material) {
        return [
            'error' => 'Material not found.'
        ];
    }
    
    $oldType = $material->material_type;
    $material->material_type = $type;
    $material->save();
    
    return [
        'message' => "Updated material #{$id} from '{$oldType}' to '{$type}'",
        'material' => [
            'id' => $material->id,
            'title' => $material->title,
            'material_type' => $material->material_type,
            'content_path_or_url' => $material->content_path_or_url,
        ]
    ];
});

// Debug route to check the last created material
Route::get('/debug-last-material', function () {
    $material = \App\Models\CourseMaterial::latest()->first();
    
    if (!$material) {
        return ['error' => 'No materials found'];
    }
    
    return [
        'id' => $material->id,
        'title' => $material->title,
        'material_type' => $material->material_type,
        'content_path_or_url' => $material->content_path_or_url,
        'created_at' => $material->created_at->format('Y-m-d H:i:s'),
    ];
});

/**
 * Admin QCM Reports Routes
 */
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Add the missing route for candidate detail
    Route::get('qcm-reports/candidate/{user}', [App\Http\Controllers\Admin\QcmReportController::class, 'candidateDetail'])->name('qcm-reports.candidate-detail');
});

// Temporary route to check PHP upload settings
Route::get('/check-upload-settings', function () {
    return response()->json([
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'memory_limit' => ini_get('memory_limit'),
        'error_reporting' => ini_get('error_reporting'),
        'display_errors' => ini_get('display_errors'),
        'file_uploads' => ini_get('file_uploads'),
        'max_input_time' => ini_get('max_input_time'),
        'max_execution_time' => ini_get('max_execution_time'),
        'audio_directory_exists' => is_dir(storage_path('app/public/audio')),
        'audio_directory_writable' => is_writable(storage_path('app/public/audio')),
        'public_dir_writable' => is_writable(public_path()),
        'storage_path' => storage_path('app/public/audio'),
        'public_path' => public_path(),
    ]);
});

// Add this before the final require
Route::post('/laravel-upload-test', function (\Illuminate\Http\Request $request) {
    // Check if we have a file
    if (!$request->hasFile('test_file')) {
        return response()->json([
            'success' => false,
            'message' => 'No file uploaded',
            'request_data' => $request->all(),
            'files_data' => $request->allFiles()
        ]);
    }

    $file = $request->file('test_file');
    $validFile = $file->isValid();
    $error = $file->getError();

    // Map error codes to messages
    $errorMessages = [
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];

    $errorMessage = isset($errorMessages[$error]) ? $errorMessages[$error] : 'Unknown upload error';

    // Build a more detailed response with debug info
    $response = [
        'success' => $validFile,
        'message' => $validFile ? 'File uploaded successfully' : 'Upload failed: ' . $errorMessage,
        'error_code' => $error,
        'error_message' => $errorMessage,
        'file_data' => [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'size_mb' => round($file->getSize() / (1024 * 1024), 2) . 'MB',
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
        ],
        'php_settings' => [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
        ],
        'system_info' => [
            'temp_dir' => sys_get_temp_dir(),
            'temp_dir_writable' => is_writable(sys_get_temp_dir()) ? 'Yes' : 'No'
        ]
    ];

    if ($validFile) {
        try {
            // Store the file
            $path = $file->store('test-uploads', 'public');
            $response['stored_path'] = $path;
            
            // Try to get actual file info
            $storedFilePath = storage_path('app/public/' . $path);
            if (file_exists($storedFilePath)) {
                $response['stored_file_info'] = [
                    'exists' => true,
                    'size' => filesize($storedFilePath),
                    'size_mb' => round(filesize($storedFilePath) / (1024 * 1024), 2) . 'MB',
                ];
            } else {
                $response['stored_file_info'] = [
                    'exists' => false
                ];
            }
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Exception during file storage: ' . $e->getMessage();
            $response['exception'] = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
    }
    
    return response()->json($response);
});

// Create a form for the above route
Route::get('/laravel-upload-test', function () {
    return view('laravel-upload-test');
});

// Add a test route for real-time notifications
Route::middleware(['auth'])->get('/test-notification', function () {
    try {
        \Log::info('Starting test notification');
        $user = auth()->user();
        
        \Log::info('Creating notification for user', ['user_id' => $user->id]);
        $notification = new \App\Models\Notification([
            'user_id' => $user->id,
            'message' => 'This is a test real-time notification! ' . date('H:i:s'),
            'type' => \App\Models\Notification::TYPE_SYSTEM,
            'link' => route('dashboard'),
            'data' => ['test' => true],
        ]);
        
        \Log::info('Saving notification');
        $notification->save();
        
        \Log::info('Preparing to broadcast notification', ['notification_id' => $notification->id]);
        
        // Check if broadcasting is configured
        \Log::info('Broadcast driver: ' . config('broadcasting.default'));
        \Log::info('Pusher config', [
            'key' => config('broadcasting.connections.pusher.key') ? 'Set' : 'Not set',
            'app_id' => config('broadcasting.connections.pusher.app_id') ? 'Set' : 'Not set',
            'secret' => config('broadcasting.connections.pusher.secret') ? 'Set' : 'Not set',
            'cluster' => config('broadcasting.connections.pusher.options.cluster')
        ]);
        
        // Broadcast the new notification
        event(new \App\Events\NewNotification($notification));
        \Log::info('Notification broadcasted successfully');
        
        return response()->json([
            'success' => true,
            'message' => 'Test notification sent!',
            'notification_id' => $notification->id,
            'config' => [
                'broadcast_driver' => config('broadcasting.default'),
                'pusher_key_set' => !empty(config('broadcasting.connections.pusher.key')),
                'pusher_app_id_set' => !empty(config('broadcasting.connections.pusher.app_id')),
                'pusher_secret_set' => !empty(config('broadcasting.connections.pusher.secret')),
                'pusher_cluster' => config('broadcasting.connections.pusher.options.cluster')
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Test notification error', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error sending notification: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('test-notification');

require __DIR__.'/auth.php';
