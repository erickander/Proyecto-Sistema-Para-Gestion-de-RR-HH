<?php

use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::prefix('reportes')
    ->name('reportes.')
    ->middleware('role:admin,rrhh')
    ->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
    });
