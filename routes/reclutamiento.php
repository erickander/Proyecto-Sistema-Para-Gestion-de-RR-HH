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
        Route::post('/vacantes/{vacante}/tests', [ReclutamientoController::class, 'storeTest'])->name('tests.store');
        Route::put('/tests/{test}', [ReclutamientoController::class, 'updateTest'])->name('tests.update');
        Route::post('/tests/{test}/preguntas', [ReclutamientoController::class, 'storePregunta'])->name('preguntas.store');
        Route::put('/preguntas/{pregunta}', [ReclutamientoController::class, 'updatePregunta'])->name('preguntas.update');
        Route::delete('/preguntas/{pregunta}', [ReclutamientoController::class, 'destroyPregunta'])->name('preguntas.destroy');
    });
