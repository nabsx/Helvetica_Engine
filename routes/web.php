<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

// --- Public: staff login (name picker + PIN pad) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('pos.login');
Route::post('/login', [AuthController::class, 'login'])->name('pos.login.submit');

// --- Authenticated staff area ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('pos.logout');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');

    Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close');

    // Orders additionally require an OPEN shift — enforced server-side.
    Route::middleware('shift.active')->group(function () {
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    });
});
