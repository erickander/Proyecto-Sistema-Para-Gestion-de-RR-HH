<?php

use App\Http\Controllers\EmpleadoController;
use Illuminate\Support\Facades\Route;

Route::prefix('empleados')
    ->name('empleados.')
    ->middleware('role:admin,rrhh')
    ->group(function () {
        Route::get('/', [EmpleadoController::class, 'index'])->name('index');
        Route::post('/', [EmpleadoController::class, 'store'])->name('store');
        Route::put('/{empleado}', [EmpleadoController::class, 'update'])->name('update');
        Route::delete('/{empleado}', [EmpleadoController::class, 'destroy'])->name('destroy');
    });
