<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes - FIXED: Properly defined with all methods
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::get('/password', [ProfileController::class, 'changePassword'])->name('password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::post('/photo', [ProfileController::class, 'uploadPhoto'])->name('photo.upload');
        Route::delete('/photo', [ProfileController::class, 'removePhoto'])->name('photo.remove');
        Route::get('/activity', [ProfileController::class, 'activity'])->name('activity');
        Route::put('/notifications', [ProfileController::class, 'updateNotifications'])->name('notifications.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
    
    // Face Recognition
    Route::get('/face/register', [FaceRecognitionController::class, 'registerForm'])->name('face.register');
    Route::post('/face/register', [FaceRecognitionController::class, 'register'])->name('face.register.store');
    Route::get('/face/recognize', [FaceRecognitionController::class, 'recognizeForm'])->name('face.recognize');
    Route::post('/face/recognize', [FaceRecognitionController::class, 'recognize'])->name('face.recognize.match');
    
    // Attendance
    Route::get('/attendance/qr-scan', [AttendanceController::class, 'qrScan'])->name('attendance.qr-scan');
    Route::post('/attendance/qr-checkin', [AttendanceController::class, 'qrCheckIn'])->name('attendance.qr-checkin');
    Route::get('/attendance/employee/{employee}', [AttendanceController::class, 'employeeAttendance'])->name('attendance.employee');
    Route::get('/attendance/export/csv', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::get('/attendance/summary', [AttendanceController::class, 'summary'])->name('attendance.summary');
    Route::resource('attendance', AttendanceController::class);
    
    // Admin and Manager routes
    Route::group([], function () {
        // Employees
        Route::resource('employees', EmployeeController::class);
        Route::get('/employees/{employee}/qr-code', [EmployeeController::class, 'qrCode'])->name('employees.qr-code');
        Route::post('/employees/{employee}/qr-code/regenerate', [EmployeeController::class, 'regenerateQrCode'])->name('employees.regenerate-qr');
        Route::get('/employees/{employee}/qr-code/export', [EmployeeController::class, 'exportQrCodePdf'])->name('employees.export-qr-pdf');
        Route::post('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

        // QR Code management
        Route::get('/admin/qr-code', [\App\Http\Controllers\QrCodeController::class, 'index'])->name('qr-code.index');
        Route::post('/admin/qr-code/regenerate', [\App\Http\Controllers\QrCodeController::class, 'regenerate'])->name('qr-code.regenerate');
        Route::get('/admin/qr-code/export', [\App\Http\Controllers\QrCodeController::class, 'exportPdf'])->name('qr-code.export');
        
        // Departments
        Route::resource('departments', DepartmentController::class);
        
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // Admin only routes
    Route::group([], function () {
        // User management
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Role & permission management
        Route::resource('roles', \App\Http\Controllers\RoleController::class);
        Route::resource('permissions', \App\Http\Controllers\PermissionController::class);

        // System settings
        Route::resource('settings', \App\Http\Controllers\SettingsController::class)->only(['index', 'edit', 'update']);
    });
});

    Route::get('/api/server-time', function() {
        return response()->json(['server_time' => now()->toDateTimeString()]);
    });

// Fallback route
Route::fallback(function () {
    return redirect()->route('dashboard');
});