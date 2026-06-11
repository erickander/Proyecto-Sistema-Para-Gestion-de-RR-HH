<?php

use App\Http\Controllers\VacacionesController;
use Illuminate\Support\Facades\Route;

Route::prefix('vacaciones')
    ->name('vacaciones.')
    ->middleware('role:admin,rrhh,empleado')
    ->group(function () {
        Route::get('/', [VacacionesController::class, 'index'])->name('index');
        Route::post('/', [VacacionesController::class, 'store'])->name('store');
        Route::patch('/{solicitud}/aprobar', [VacacionesController::class, 'approve'])->middleware('role:admin,rrhh')->name('approve');
        Route::patch('/{solicitud}/rechazar', [VacacionesController::class, 'reject'])->middleware('role:admin,rrhh')->name('reject');
    });
