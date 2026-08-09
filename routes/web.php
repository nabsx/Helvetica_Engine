<?php

use App\Http\Controllers\AdminCancellationController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderCancellationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SalesReportController;
use Illuminate\Support\Facades\Route;

// --- Public: staff login (name picker + PIN pad) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('pos.login');
Route::post('/login', [AuthController::class, 'login'])->name('pos.login.submit');

// --- Authenticated staff area ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('pos.logout');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/laporan-penjualan', [SalesReportController::class, 'index'])->name('sales-report');
        Route::redirect('/reports', '/admin/dashboard');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::resource('categories', AdminCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        // Cancellation approval queue — approving is the ONLY way an
        // order's status becomes 'cancelled'. A cashier's request alone
        // never changes it (see OrderCancellationController::store).
        Route::get('/pembatalan', [AdminCancellationController::class, 'index'])->name('cancellations.index');
        Route::post('/pembatalan/{cancellationRequest}/approve', [AdminCancellationController::class, 'approve'])->name('cancellations.approve');
        Route::post('/pembatalan/{cancellationRequest}/reject', [AdminCancellationController::class, 'reject'])->name('cancellations.reject');
    });

    Route::get('/orders/{order}/receipt', [ReceiptController::class, 'show'])
        ->name('orders.receipt');
    Route::get('/orders/{order}/receipt/escpos', [ReceiptController::class, 'escpos'])
        ->name('orders.receipt.escpos');

    // Cashier-facing: submit a request only, never a direct cancel.
    Route::post('/orders/{order}/cancellation-requests', [OrderCancellationController::class, 'store'])
        ->name('orders.cancellation-requests.store');

    Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close');

    // Orders additionally require an OPEN shift — enforced server-side.
    Route::middleware('shift.active')->group(function () {
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    });
});
