<?php

use App\Http\Controllers\IaController;
use Illuminate\Support\Facades\Route;

Route::prefix('ia')
    ->name('ia.')
    ->middleware('role:admin,rrhh')
    ->group(function () {
        Route::get('/', [IaController::class, 'index'])->name('index');
        Route::post('/postulaciones/{postulacion}/analizar', [IaController::class, 'analyze'])->name('analyze');
        Route::post('/postulaciones/{postulacion}/reanalizar', [IaController::class, 'reanalyze'])->name('reanalyze');
        Route::post('/postulaciones/{postulacion}/contratar', [IaController::class, 'hire'])->name('hire');
        Route::post('/postulaciones/{postulacion}/rechazar', [IaController::class, 'reject'])->name('reject');
        Route::delete('/analisis/{analisis}', [IaController::class, 'destroy'])->name('destroy');
    });
