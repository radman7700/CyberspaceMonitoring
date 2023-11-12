<?php

use Illuminate\Support\Facades\Route;
use Pishgaman\CyberspaceMonitoring\Controllers\api\PayeshInformationController;
use Pishgaman\CyberspaceMonitoring\Controllers\api\PayeshTelegramController;

Route::group(['namespace' => 'Pishgaman\Pishgaman\Http\Controllers\Api','middleware' => [ 'auth:sanctum' ] ], function() {
    Route::prefix('api')->group(function () {
        Route::match(['get','post','put','delete'], 'payesh/information', [PayeshInformationController::class, 'action'])->name('payesh_dashboardApi');    
        Route::match(['get','post','put','delete'], 'payesh/telegram', [PayeshTelegramController::class, 'action'])->name('payesh_telegram_messages_listApi');    
    });    
});
