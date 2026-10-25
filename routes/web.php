<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\Peran;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $u = auth()->user();
    if (! $u) return redirect()->route('login');
    return redirect()->route($u->isStaf() ? 'admin.dashboard' : 'beranda');
});

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'show'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.proses');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.proses');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', Peran::class . ':pelanggan'])->get('/beranda', [BerandaController::class, 'index'])->name('beranda');

Route::middleware(['auth', Peran::class . ':admin,kasir'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
});
