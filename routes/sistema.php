<?php

use App\Http\Controllers\SistemaController;
use Illuminate\Support\Facades\Route;

Route::prefix('sistema')
    ->name('sistema.')
    ->middleware('role:admin')
    ->group(function () {
        Route::get('/', [SistemaController::class, 'index'])->name('index');
        Route::post('/departamentos', [SistemaController::class, 'storeDepartamento'])->name('departamentos.store');
        Route::post('/respaldos', [SistemaController::class, 'backup'])->name('respaldos.store');
    });
