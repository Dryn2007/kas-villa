<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// 1. Route Dashboard kita ubah agar mengarah ke Controller yang menghitung tagihan
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 2. Tambahkan Route khusus untuk Google Login di sini
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Route Baru untuk fitur Claim KK
Route::get('pilih-kk', [GoogleController::class, 'showPilihKk'])->name('pilih-kk');
Route::post('pilih-kk', [GoogleController::class, 'claimKk'])->name('claim-kk');

// Route untuk Dummy Payment
Route::post('/dummy-pay-bulk', [DashboardController::class, 'dummyPayBulk'])->name('dummy.pay.bulk');
Route::get('/riwayat-lengkap', [DashboardController::class, 'history'])->name('riwayat');

// Route Khusus Admin
Route::get('/admin-panel', [AdminController::class, 'index'])->name('admin.index');
Route::post('/admin-panel/approve/{id}', [AdminController::class, 'approve'])->name('admin.approve');
Route::post('/admin-panel/reject/{id}', [AdminController::class, 'reject'])->name('admin.reject');

require __DIR__ . '/auth.php';
