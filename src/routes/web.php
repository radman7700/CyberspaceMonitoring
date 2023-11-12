<?php

use Illuminate\Support\Facades\Route;
use Pishgaman\CyberspaceMonitoring\Controllers\web\HomeController;
use Pishgaman\CyberspaceMonitoring\Controllers\web\TelegramMessages;

Route::middleware(['web','auth'])->group(function () {
    // Route::get('/superadmin/gyms/management', [GymManagementController::class, 'index'])->name('SuperAdminGymsManagement.index');
});

Route::get('/payesh/dashboard', [HomeController::class, 'home'])->name('payesh_dashboard')->middleware(['auth','CheckMenuAccess','web']);
Route::get('/payesh/telegram/list', [TelegramMessages::class, 'messagesList'])->middleware(['auth','CheckMenuAccess','web'])->name('payesh_telegram_messages_list');
Route::get('/payesh/WordCount', [HomeController::class, 'WordCount'])->name('payesh_word_count')->middleware(['auth','web']);

