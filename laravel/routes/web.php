<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;


Route::get('/', function () {
    return redirect('/login-test');
});

Route::get('/login-test', function () {
    return view('login-test');
});


Route::post('/login', [AuthController::class, 'login']);


Route::post('/logout', [AuthController::class, 'logout']);


Route::get('/dashboard-test', function () {

    return view('dashboard-test');
})->middleware('auth');

Route::post('/presensi', [PresensiController::class, 'store'])
    ->middleware('auth');

Route::get('/presensi-test', function () {
    return view('presensi-test');
})->middleware('auth');

Route::view('/history-test', 'history')->middleware('auth');

Route::view('/leaderboard-test', 'leaderboard')->middleware('auth');

Route::view('/notifikasi-test', 'notifikasi')->middleware('auth');

Route::view('/profil-test', 'profil')->middleware('auth');

Route::post('/presensi/cek-lokasi', [PresensiController::class, 'cekLokasi'])
    ->middleware('auth');

Route::get('/presensi/status-hari-ini', [PresensiController::class, 'statusHariIni'])
    ->middleware('auth');

Route::get('/presensi/riwayat-saya', [
    PresensiController::class,
    'riwayatSaya'
])->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('/register', [
        RegisterController::class,
        'showRegister',
    ])->name('register');

    Route::post('/register', [
        RegisterController::class,
        'register',
    ])->name('register.store');
});

Route::get('/admin/dashboard', [
    AdminDashboardController::class,
    'dashboard'
])->middleware('auth')->name('admin.dashboard');

Route::post('/admin/presensi/{id}/konfirmasi', [
    AdminDashboardController::class,
    'konfirmasiPresensi'
])->middleware('auth')->name('admin.presensi.konfirmasi');
Route::get('/admin/data-presensi', [
    \App\Http\Controllers\AdminDashboardController::class,
    'dataPresensi'
])->middleware('auth')->name('admin.data-presensi');


Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/data-user', [
        AdminUserController::class,
        'index'
    ])->name('data-user');

    Route::get('/data-user/tambah', [
        AdminUserController::class,
        'create'
    ])->name('data-user.create');

    Route::post('/data-user', [
        AdminUserController::class,
        'store'
    ])->name('data-user.store');

    Route::get('/data-user/{id}/edit', [
        AdminUserController::class,
        'edit'
    ])->name('data-user.edit');

    Route::put('/data-user/{id}', [
        AdminUserController::class,
        'update'
    ])->name('data-user.update');

    Route::patch('/data-user/{id}/status', [
        AdminUserController::class,
        'toggleStatus'
    ])->name('data-user.status');

    Route::view('/leaderboard', 'admin.leaderboard')->name('leaderboard');
    // Route::view('/notifikasi', 'admin.notifikasi')->name('notifikasi');
});
