<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('role:admin')->name('dashboard.admin');
Route::get('/dashboard/rrhh', [DashboardController::class, 'rrhh'])->middleware('role:admin,rrhh')->name('dashboard.rrhh');
Route::get('/dashboard/empleado', [DashboardController::class, 'empleado'])->middleware('role:empleado')->name('dashboard.empleado');
