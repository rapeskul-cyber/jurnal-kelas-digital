<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;

// Public Auth routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/quick-login', [AuthController::class, 'quickLogin']);
Route::post('/auth/forgot-password/step-1', [AuthController::class, 'forgotPasswordStep1']);
Route::post('/auth/forgot-password/step-2', [AuthController::class, 'forgotPasswordStep2']);
Route::post('/auth/forgot-password/step-3', [AuthController::class, 'forgotPasswordStep3']);

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Attendance (Presensi)
    Route::get('/attendance/today', [AttendanceController::class, 'getToday']);
    Route::post('/attendance/draft', [AttendanceController::class, 'saveDraft']);
    Route::post('/attendance/lock', [AttendanceController::class, 'lockSession']);
    Route::get('/attendance/history', [AttendanceController::class, 'studentHistory']);
    Route::get('/attendance/daily-recap', [AttendanceController::class, 'dailyRecap']);
    Route::get('/attendance/semester-recap', [AttendanceController::class, 'semesterRecap']);

    // Journals (Jurnal Kelas & Jurnal Materi)
    Route::get('/journals/class', [JournalController::class, 'getClassJournals']);
    Route::post('/journals/class', [JournalController::class, 'storeClassJournal']);
    Route::put('/journals/class/{id}/confirm', [JournalController::class, 'confirmClassJournal']);
    Route::get('/journals/material', [JournalController::class, 'getMaterialJournals']);
    Route::post('/journals/material', [JournalController::class, 'storeMaterialJournal']);

    // Schedules (Jadwal Mengajar)
    Route::get('/schedules', [JournalController::class, 'getSchedules']);
    Route::post('/schedules', [JournalController::class, 'storeSchedule']);

    // Leave Requests (Surat Izin)
    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::put('/leave-requests/{id}/review', [LeaveRequestController::class, 'review']);

    // Notifications & Audit Logs
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/{id?}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/audit-logs', [NotificationController::class, 'auditLogs']);

    // User Management (Pembimbing)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::get('/classes', [UserController::class, 'getClasses']);
});
