<?php

use Illuminate\Support\Facades\Route;
use Pishgaman\CyberspaceMonitoring\Controllers\web\HomeController;
use Pishgaman\CyberspaceMonitoring\Controllers\web\TelegramMessages;
use Pishgaman\CyberspaceMonitoring\Controllers\web\TelegramController;

Route::middleware(['web','auth'])->group(function () {
    // Route::get('/superadmin/gyms/management', [GymManagementController::class, 'index'])->name('SuperAdminGymsManagement.index');
});

Route::get('/payesh/telegram/group/dashboard', [HomeController::class, 'home'])->name('payesh_dashboard')->middleware(['auth','CheckMenuAccess','web']);
Route::get('/payesh/telegram/channel/dashboard', [HomeController::class, 'channelHome'])->name('payesh_telegram_channel_dashboard')->middleware(['auth','CheckMenuAccess','web']);
Route::get('/payesh/telegram//group/list', [TelegramMessages::class, 'messagesList'])->name('payesh_telegram_messages_list')->middleware(['auth','CheckMenuAccess','web']);
Route::get('/payesh/telegram/channel/list', [TelegramMessages::class, 'telegramChannelMessagesList'])->name('payesh_telegram_channel_messages_list')->middleware(['auth','CheckMenuAccess','web']);
Route::get('/payesh/WordCount', [HomeController::class, 'WordCount'])->name('payesh_word_count')->middleware(['auth','web']);

Route::get('/webservice/CyberspaceMonitoring/telegram/saveNewMsg', [TelegramController::class, 'saveNewMsg'])->middleware(['webservice']);
Route::get('/webservice/CyberspaceMonitoring/telegram/saveGroup', [TelegramController::class, 'saveGroup'])->middleware(['webservice']);
Route::get('/webservice/CyberspaceMonitoring/telegram/saveUser', [TelegramController::class, 'saveUser'])->middleware(['webservice']);


