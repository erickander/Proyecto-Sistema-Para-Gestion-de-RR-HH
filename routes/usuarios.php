<?php

use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('usuarios')
    ->name('usuarios.')
    ->middleware('role:admin')
    ->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::put('/{user}', [UsuarioController::class, 'update'])->name('update');
        Route::delete('/{user}', [UsuarioController::class, 'destroy'])->name('destroy');
    });
