<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Services\GeminiCvAnalyzer;
use Illuminate\Http\Request;

class PortalEmpleoController extends Controller
{
    public function index()
    {
        return view('portal.index', [
            'vacantes' => Vacante::where('estado', 'ABIERTA')->latest('fecha_publicacion')->take(6)->get(),
        ]);
    }

    public function vacantes()
    {
        return view('portal.vacantes', [
            'vacantes' => Vacante::where('estado', 'ABIERTA')->latest('fecha_publicacion')->paginate(9),
        ]);
    }

    public function show(Vacante $vacante)
    {
        abort_if($vacante->estado !== 'ABIERTA', 404);

        return view('portal.postular', compact('vacante'));
    }

    public function store(Request $request, Vacante $vacante, GeminiCvAnalyzer $analyzer)
    {
        abort_if($vacante->estado !== 'ABIERTA', 404);

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'cedula' => ['nullable', 'string', 'max:20'],
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'consentimiento_ia' => ['accepted'],
        ]);

        $path = $request->file('cv')->store('cvs', 'public');

        $candidato = Candidato::updateOrCreate(
            ['correo' => $data['correo']],
            [
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'cedula' => $data['cedula'],
                'telefono' => $data['telefono'],
                'direccion' => $data['direccion'],
                'cv_url' => $path,
                'fecha_registro' => now(),
                'estado' => 'ACTIVO',
            ]
        );

        $postulacion = Postulacion::create([
            'id_candidato' => $candidato->id_candidato,
            'id_vacante' => $vacante->id_vacante,
            'fecha_postulacion' => now(),
            'estado' => 'RECIBIDA',
            'observaciones' => 'Postulacion registrada desde portal publico.',
        ]);

        $analyzer->analyze($postulacion->load('vacante'), $request->file('cv'));

        return redirect()->route('portal.gracias')->with('status', 'Postulacion enviada correctamente. RRHH revisara el resultado del analisis.');
    }

    public function gracias()
    {
        return view('portal.gracias');
    }
}
