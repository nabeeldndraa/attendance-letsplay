<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PresensiController;


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