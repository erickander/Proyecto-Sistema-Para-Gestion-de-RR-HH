<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PortalEmpleoController;

Route::get('/', [PortalEmpleoController::class, 'index'])->name('portal.index');
Route::get('/vacantes', [PortalEmpleoController::class, 'vacantes'])->name('portal.vacantes');
Route::get('/vacantes/{vacante}', [PortalEmpleoController::class, 'show'])->name('portal.vacantes.show');
Route::post('/vacantes/{vacante}/postular', [PortalEmpleoController::class, 'store'])->name('portal.postular');
Route::get('/postulaciones/{postulacion}/test/{token}', [PortalEmpleoController::class, 'showTest'])->name('portal.test.show');
Route::post('/postulaciones/{postulacion}/test/{token}', [PortalEmpleoController::class, 'submitTest'])->name('portal.test.submit');
Route::get('/postulacion-enviada', [PortalEmpleoController::class, 'gracias'])->name('portal.gracias');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    require __DIR__.'/dashboard.php';
    require __DIR__.'/usuarios.php';
    require __DIR__.'/sistema.php';
    require __DIR__.'/empleados.php';
    require __DIR__.'/reclutamiento.php';
    require __DIR__.'/nomina.php';
    require __DIR__.'/vacaciones.php';
    require __DIR__.'/notificaciones.php';
    require __DIR__.'/auditoria.php';
    require __DIR__.'/reportes.php';
    require __DIR__.'/ia.php';
    require __DIR__.'/perfil.php';
});
Route::get('/test-gemini', function () {

    $apiKey = env('GEMINI_API_KEY');

    $response = Http::withoutVerifying()->post(
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
        [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => "Responde solo FUNCIONANDO"
                        ]
                    ]
                ]
            ]
        ]
    );

    return $response->json();

});
Route::get('/gemini-modelos', function () {
    $apiKey = env('GEMINI_API_KEY');

    $response = Http::withoutVerifying()->get(
        "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}"
    );

    return $response->json();
});
