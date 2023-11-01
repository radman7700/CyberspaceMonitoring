<?php

use Illuminate\Support\Facades\Route;
use Pishgaman\CyberspaceMonitoring\Controllers\web\HomeController;

Route::middleware(['web','auth'])->group(function () {
    // Route::get('/superadmin/gyms/management', [GymManagementController::class, 'index'])->name('SuperAdminGymsManagement.index');
});

Route::get('/payesh/dashboard', [HomeController::class, 'home'])->name('payesh_dashboard')->middleware(['auth','CheckMenuAccess','web']);
Route::get('/payesh/WordCount', [HomeController::class, 'WordCount'])->name('payesh_word_count')->middleware(['auth','web']);

