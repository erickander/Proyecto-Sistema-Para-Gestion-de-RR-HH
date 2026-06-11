<?php

use App\Http\Controllers\NotificacionController;
use Illuminate\Support\Facades\Route;

Route::prefix('notificaciones')
    ->name('notificaciones.')
    ->middleware('role:admin,rrhh,empleado')
    ->group(function () {
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::post('/', [NotificacionController::class, 'store'])->middleware('role:admin,rrhh')->name('store');
    });
