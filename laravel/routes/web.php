<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login-test');
});

Route::get('/login-test', [AuthController::class, 'showLogin'])->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/dashboard-test', [UserDashboardController::class, 'index'])
    ->middleware('auth');

Route::post('/presensi', [PresensiController::class, 'store'])
    ->middleware('auth');

Route::get('/presensi-test', function () {
    return view('presensi-test');
})->middleware('auth');

Route::view('/history-test', 'history')->middleware('auth');

Route::view('/leaderboard-test', 'leaderboard')->middleware('auth');

Route::get('/notifikasi-test', [UserNotificationController::class, 'index'])
    ->middleware('auth');
Route::get('/notifikasi/data', [UserNotificationController::class, 'data'])
    ->middleware('auth');
Route::get('/notifikasi/belum-dibaca', [
    UserNotificationController::class,
    'unreadCount',
])->middleware('auth');
Route::post('/notifikasi/dibaca-semua', [
    UserNotificationController::class,
    'markAllAsRead',
])->middleware('auth');
Route::post('/notifikasi/{id}/dibaca', [
    UserNotificationController::class,
    'markAsRead',
])->whereNumber('id')->middleware('auth');

Route::view('/profil-test', 'profil')->middleware('auth');

Route::post('/presensi/cek-lokasi', [PresensiController::class, 'cekLokasi'])
    ->middleware('auth');

Route::get('/presensi/status-hari-ini', [PresensiController::class, 'statusHariIni'])
    ->middleware('auth');

Route::get('/presensi/riwayat-saya', [
    PresensiController::class,
    'riwayatSaya',
])->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [
        PasswordResetController::class,
        'showRequestForm',
    ])->name('password.request');
    Route::post('/forgot-password', [
        PasswordResetController::class,
        'sendCode',
    ])->middleware('throttle:5,1')->name('password.email');
    Route::get('/forgot-password/verify', [
        PasswordResetController::class,
        'showVerificationForm',
    ])->name('password.verify');
    Route::post('/forgot-password/reset', [
        PasswordResetController::class,
        'resetPassword',
    ])->middleware('throttle:10,1')->name('password.update');

    Route::get('/auth/google', [
        GoogleAuthController::class,
        'redirect',
    ])->name('google.redirect');
    Route::get('/auth/google/callback', [
        GoogleAuthController::class,
        'callback',
    ])->name('google.callback');

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
    'dashboard',
])->middleware('auth')->name('admin.dashboard');

Route::post('/admin/presensi/{id}/konfirmasi', [
    AdminDashboardController::class,
    'konfirmasiPresensi',
])->middleware('auth')->name('admin.presensi.konfirmasi');
Route::get('/admin/data-presensi', [
    AdminDashboardController::class,
    'dataPresensi',
])->middleware('auth')->name('admin.data-presensi');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/data-user', [
        AdminUserController::class,
        'index',
    ])->name('data-user');

    Route::get('/data-user/tambah', [
        AdminUserController::class,
        'create',
    ])->name('data-user.create');

    Route::post('/data-user', [
        AdminUserController::class,
        'store',
    ])->name('data-user.store');

    Route::get('/data-user/{id}/edit', [
        AdminUserController::class,
        'edit',
    ])->name('data-user.edit');

    Route::put('/data-user/{id}', [
        AdminUserController::class,
        'update',
    ])->name('data-user.update');

    Route::patch('/data-user/{id}/status', [
        AdminUserController::class,
        'toggleStatus',
    ])->name('data-user.status');
});
