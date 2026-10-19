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

// Public material file route (or auth)
Route::get('/materials/file/{filename}', [JournalController::class, 'downloadMaterialFile']);

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // WhatsApp Gateway Management (Proxy ke Fonte lokal port 3000)
    Route::get('/wa-gateway/status', [AuthController::class, 'waStatus']);
    Route::post('/wa-gateway/pairing-code', [AuthController::class, 'waPairingCode']);
    Route::post('/wa-gateway/logout', [AuthController::class, 'waLogout']);

    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Attendance (Presensi)
    Route::get('/attendance/today', [AttendanceController::class, 'getToday']);
    Route::post('/attendance/draft', [AttendanceController::class, 'saveDraft']);
    Route::post('/attendance/lock', [AttendanceController::class, 'lockSession']);
    Route::post('/attendance/confirm', [AttendanceController::class, 'confirmSession']);
    Route::get('/attendance/history', [AttendanceController::class, 'studentHistory']);
    Route::get('/attendance/daily-recap', [AttendanceController::class, 'dailyRecap']);
    Route::get('/attendance/semester-recap', [AttendanceController::class, 'semesterRecap']);

    // Journals (Jurnal Kelas & Jurnal Materi)
    Route::get('/journals/class', [JournalController::class, 'getClassJournals']);
    Route::post('/journals/class', [JournalController::class, 'storeClassJournal']);
    Route::put('/journals/class/{id}', [JournalController::class, 'updateClassJournal']);
    Route::post('/journals/class/{id}', [JournalController::class, 'updateClassJournal']);
    Route::delete('/journals/class/{id}', [JournalController::class, 'deleteClassJournal']);
    Route::put('/journals/class/{id}/confirm', [JournalController::class, 'confirmClassJournal']);
    Route::post('/journals/class/{id}/confirm', [JournalController::class, 'confirmClassJournal']);
    Route::get('/journals/material', [JournalController::class, 'getMaterialJournals']);
    Route::post('/journals/material', [JournalController::class, 'storeMaterialJournal']);
    Route::put('/journals/material/{id}', [JournalController::class, 'updateMaterialJournal']);
    Route::delete('/journals/material/{id}', [JournalController::class, 'deleteMaterialJournal']);

    // Schedules (Jadwal Mengajar)
    Route::get('/schedules', [JournalController::class, 'getSchedules']);
    Route::post('/schedules', [JournalController::class, 'storeSchedule']);

    // Leave Requests (Surat Izin)
    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::put('/leave-requests/{id}/review', [LeaveRequestController::class, 'review']);
    Route::delete('/leave-requests/{id}', [LeaveRequestController::class, 'destroy']);

    // Notifications & Audit Logs
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/{id?}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);
    Route::get('/audit-logs', [NotificationController::class, 'auditLogs']);

    // User Management (Pembimbing)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::get('/classes', [UserController::class, 'getClasses']);
});
