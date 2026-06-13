<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaystationUnitController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return redirect('/admin');
});

// Auth & Admin Routes
Route::prefix('admin')->group(function () {
    // Auth Routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Routes (Admin/Kasir Only)
    Route::middleware(['auth'])->group(function () {
        
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/dashboard/metrics', [DashboardController::class, 'apiMetrics'])->name('dashboard.metrics');
        Route::get('/billing', [DashboardController::class, 'billing'])->name('billing');
        
        // AJAX Quick Actions on Dashboard
        Route::post('/dashboard/start-play', [DashboardController::class, 'startPlay'])->name('dashboard.start-play');
        Route::post('/dashboard/end-play', [DashboardController::class, 'endPlay'])->name('dashboard.end-play');
        Route::post('/dashboard/return-rental', [DashboardController::class, 'returnRental'])->name('dashboard.return-rental');
        Route::post('/api/transactions/onsite/order', [DashboardController::class, 'addOrder'])->name('dashboard.add-order');
        
        // Kelola Unit PS
        Route::get('/units', [PlaystationUnitController::class, 'index'])->name('units.index');
        Route::get('/api/units', [PlaystationUnitController::class, 'apiIndex']);
        Route::post('/api/units', [PlaystationUnitController::class, 'store']);
        Route::put('/api/units/{id}', [PlaystationUnitController::class, 'update']);
        Route::delete('/api/units/{id}', [PlaystationUnitController::class, 'destroy']);
        
        // Kelola Tarif
        Route::get('/rates', [RateController::class, 'index'])->name('rates.index');
        Route::get('/api/rates', [RateController::class, 'apiIndex']);
        Route::post('/api/rates', [RateController::class, 'store']);
        Route::put('/api/rates/{id}', [RateController::class, 'update']);
        Route::delete('/api/rates/{id}', [RateController::class, 'destroy']);
        
        // Kelola Produk / F&B
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/api/products', [ProductController::class, 'apiList']);
        Route::post('/api/products', [ProductController::class, 'store']);
        Route::put('/api/products/{product}', [ProductController::class, 'update']);
        Route::delete('/api/products/{product}', [ProductController::class, 'destroy']);
        
        // Transaksi Sewa & Riwayat
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/api/transactions/filter', [TransactionController::class, 'filter']);
        Route::post('/api/transactions/rental', [TransactionController::class, 'storeRental']);
        
        // Laporan Pendapatan
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/api/reports/filter', [ReportController::class, 'filter']);
        
        // Pengaturan Aplikasi
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/api/settings/update', [SettingController::class, 'update']);
        Route::post('/api/settings/security', [SettingController::class, 'updateSecurity'])->name('settings.security');
    });
});

