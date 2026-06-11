<?php

use App\Http\Controllers\AuditoriaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auditoria')
    ->name('auditoria.')
    ->middleware('role:admin')
    ->group(function () {
        Route::get('/', [AuditoriaController::class, 'index'])->name('index');
    });
