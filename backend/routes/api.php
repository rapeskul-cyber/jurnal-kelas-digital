<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Dashboard & health check
    Route::get('/health', [DashboardController::class, 'health']);

    // Public authentication (add OAuth or Sanctum later)
    Route::post('/auth/login', [DashboardController::class, 'login']);

    // Journals
    Route::get('/journals', [JournalController::class, 'index']);
    Route::post('/journals', [JournalController::class, 'store']);

    // Attendance
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance', [AttendanceController::class, 'index']);

    // Teacher Ratings
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings/{teacherId}', [RatingController::class, 'show']);
});