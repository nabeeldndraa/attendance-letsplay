<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PresensiController;


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
