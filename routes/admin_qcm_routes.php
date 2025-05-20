<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QcmReportController;

// Admin QCM Reports Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // QCM Reports
    Route::get('qcm-reports', [QcmReportController::class, 'index'])->name('qcm-reports.index');
    Route::get('qcm-reports/candidates', [QcmReportController::class, 'candidates'])->name('qcm-reports.candidates');
    Route::get('qcm-reports/candidate/{user}', [QcmReportController::class, 'candidateDetail'])->name('qcm-reports.candidate-detail');
    Route::get('qcm-reports/statistics', [QcmReportController::class, 'statistics'])->name('qcm-reports.statistics');
    Route::get('qcm-reports/export', [QcmReportController::class, 'export'])->name('qcm-reports.export');
}); 