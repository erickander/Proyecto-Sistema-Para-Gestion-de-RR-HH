<?php

use App\Http\Controllers\ReclutamientoController;
use Illuminate\Support\Facades\Route;

Route::prefix('reclutamiento')
    ->name('reclutamiento.')
    ->middleware('role:admin,rrhh')
    ->group(function () {
        Route::get('/', [ReclutamientoController::class, 'index'])->name('index');
        Route::post('/vacantes', [ReclutamientoController::class, 'store'])->name('vacantes.store');
        Route::put('/vacantes/{vacante}', [ReclutamientoController::class, 'update'])->name('vacantes.update');
    });
