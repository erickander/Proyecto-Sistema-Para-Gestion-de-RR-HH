<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/recuperar-contrasena', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/recuperar-contrasena', [AuthController::class, 'sendRecoveryToken'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
