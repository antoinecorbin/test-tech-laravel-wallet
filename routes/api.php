<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\RecurringTransferController;
use App\Http\Controllers\Api\V1\SendMoneyController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/login', LoginController::class)->middleware(['guest:sanctum', 'throttle:api.login']);

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v1')->group(function () {
    Route::get('/account', AccountController::class);
    Route::post('/wallet/send-money', SendMoneyController::class);

    Route::prefix('recurring-transfers')->group(function (){
        Route::get('/', [RecurringTransferController::class, 'index']);
        Route::post('/store', [RecurringTransferController::class, 'store']);
        Route::delete('/delete/{recurringTransfer}', [RecurringTransferController::class, 'destroy']);
    });
});
