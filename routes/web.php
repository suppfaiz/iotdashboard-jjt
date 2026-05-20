<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SystemConfigController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Require authentication for ALL dashboard and IoT routes
Route::middleware('auth')->group(function () {
    
    // Both Admin and Standard User can access the main Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', function () {
        return redirect()->route('dashboard');
    });

    // Profile routes (Standard Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-Only Routes
    Route::middleware('admin')->group(function () {
        // Devices Management & Telemetry Detail
        Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
        Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
        Route::get('/devices/{device}', [DeviceController::class, 'show'])->name('devices.show');
        Route::get('/devices/{device}/provisioning', [DeviceController::class, 'provisioning'])->name('devices.provisioning');
        Route::get('/devices/{device}/ping', [DeviceController::class, 'ping'])->name('devices.ping');
        Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');
        Route::post('/devices/{device}/firmware', [DeviceController::class, 'uploadFirmware'])->name('devices.upload_firmware');
        Route::post('/devices/{device}/trigger-ota', [DeviceController::class, 'triggerOta'])->name('devices.trigger_ota');

        // Logs
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

        // Settings
        Route::get('/settings', [SystemConfigController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SystemConfigController::class, 'update'])->name('settings.update');
        Route::post('/settings/users', [SystemConfigController::class, 'storeUser'])->name('settings.users.store');
        Route::delete('/settings/users/{user}', [SystemConfigController::class, 'destroyUser'])->name('settings.users.destroy');

        // Documentation
        Route::get('/docs', function () {
            return view('docs.index');
        })->name('docs.index');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/download/{date}', [ReportController::class, 'download'])->name('reports.download');
    });
});

require __DIR__.'/auth.php';
