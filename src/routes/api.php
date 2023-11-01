<?php

use Illuminate\Support\Facades\Route;
use Pishgaman\CyberspaceMonitoring\Controllers\api\PayeshInformationController;

Route::group(['namespace' => 'Pishgaman\Pishgaman\Http\Controllers\Api','middleware' => [ 'auth:sanctum' ] ], function() {
    Route::prefix('api')->group(function () {
        Route::match(['get','post','put','delete'], 'payesh/information', [PayeshInformationController::class, 'action'])->name('payesh_dashboardApi');    
    });
    
});
