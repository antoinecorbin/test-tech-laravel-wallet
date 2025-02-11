<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecurringTransferController;
use App\Http\Controllers\SendMoneyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/send-money', [SendMoneyController::class, '__invoke'])->name('send-money');

    Route::prefix('recurring-transfers')->name('recurring-transfers.')->group(function (){
        Route::get('/', [RecurringTransferController::class, 'index'])->name('index');
        Route::post('/store', [RecurringTransferController::class, 'store'])->name('store');
        Route::delete('/destroy/{recurringTransfer}', [RecurringTransferController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
