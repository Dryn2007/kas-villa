<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\DuitkuController;
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

    // Routes untuk upload gambar
    Route::post('/upload-profile-image', [ImageController::class, 'uploadProfileImage'])->name('upload.profile.image');
    Route::post('/upload-file', [ImageController::class, 'uploadFile'])->name('upload.file');
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
Route::post('/admin-panel/add-kk', [AdminController::class, 'addKk'])->name('admin.addKk');
Route::delete('/admin-panel/delete-kk/{id}', [AdminController::class, 'deleteKk'])->name('admin.deleteKk');
Route::post('/admin-panel/manual-pay/{id}', [AdminController::class, 'manualPay'])->name('admin.manualPay');

// Duitku Payment Routes
Route::post('/api/duitku/callback', [DuitkuController::class, 'callback'])->name('duitku.callback');
Route::get('/duitku/return', [DuitkuController::class, 'return'])->name('duitku.return');

Route::get('/export/pdf', [ExportController::class, 'exportPdf'])->name('export.pdf')->middleware('auth');
Route::get('/export/excel', [ExportController::class, 'exportExcel'])->name('export.excel')->middleware('auth');

require __DIR__ . '/auth.php';
