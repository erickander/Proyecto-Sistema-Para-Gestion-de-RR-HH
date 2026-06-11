<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;

class AuditoriaController extends Controller
{
    public function index()
    {
        return view('modules.auditoria.index', [
            'auditorias' => Auditoria::with('usuario')->latest('fecha_evento')->paginate(15),
        ]);
    }
}
