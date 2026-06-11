<?php

use App\Http\Controllers\NominaController;
use Illuminate\Support\Facades\Route;

Route::prefix('nomina')
    ->name('nomina.')
    ->middleware('role:admin,rrhh')
    ->group(function () {
        Route::get('/', [NominaController::class, 'index'])->name('index');
    });
