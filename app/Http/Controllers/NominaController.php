<?php

namespace App\Http\Controllers;

use App\Models\Nomina;

class NominaController extends Controller
{
    public function index()
    {
        return view('modules.nomina.index', [
            'nominas' => Nomina::with('empleado')->latest('fecha_generacion')->paginate(10),
        ]);
    }
}
