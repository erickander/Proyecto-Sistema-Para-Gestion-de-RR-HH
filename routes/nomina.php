<?php

use App\Http\Controllers\NominaController;
use Illuminate\Support\Facades\Route;

Route::prefix('nomina')
    ->name('nomina.')
    ->middleware('role:admin,rrhh')
    ->group(function () {
        Route::get('/', [NominaController::class, 'index'])->name('index');
        Route::post('/', [NominaController::class, 'store'])->name('store');
        Route::patch('/{nomina}/estado', [NominaController::class, 'updateEstado'])->name('estado');
    });
