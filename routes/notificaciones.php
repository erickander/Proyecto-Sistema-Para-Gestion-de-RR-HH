<?php

use App\Http\Controllers\NotificacionController;
use Illuminate\Support\Facades\Route;

Route::prefix('notificaciones')
    ->name('notificaciones.')
    ->middleware('role:admin,rrhh,empleado')
    ->group(function () {
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::post('/', [NotificacionController::class, 'store'])->middleware('role:admin,rrhh')->name('store');
        Route::patch('/{notificacion}/leer', [NotificacionController::class, 'markRead'])->name('read');
        Route::patch('/{notificacion}/no-leida', [NotificacionController::class, 'markUnread'])->middleware('role:admin,rrhh')->name('unread');
        Route::patch('/marcar-todas/leidas', [NotificacionController::class, 'markAllRead'])->name('read-all');
        Route::delete('/{notificacion}', [NotificacionController::class, 'destroy'])->name('destroy');
    });
