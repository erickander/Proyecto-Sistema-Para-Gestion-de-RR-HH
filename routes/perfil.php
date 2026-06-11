<?php

use App\Http\Controllers\PerfilEmpleadoController;
use Illuminate\Support\Facades\Route;

Route::put('/mi-perfil', [PerfilEmpleadoController::class, 'update'])
    ->middleware('role:empleado')
    ->name('perfil.update');
