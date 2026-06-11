<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Nomina;
use App\Models\Postulacion;

class ReporteController extends Controller
{
    public function index()
    {
        return view('modules.reportes.index', [
            'totalEmpleados' => Empleado::count(),
            'totalNomina' => Nomina::sum('total_pagar'),
            'postulaciones' => Postulacion::count(),
        ]);
    }
}
