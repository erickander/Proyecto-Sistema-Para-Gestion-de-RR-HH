<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Departamento;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Http\Request;

class ReclutamientoController extends Controller
{
    public function index()
    {
        return view('modules.reclutamiento.index', [
            'vacantes' => Vacante::withCount('postulaciones')->with('departamento')->latest('id_vacante')->paginate(10),
            'departamentos' => Departamento::orderBy('nombre_departamento')->get(),
            'postulaciones' => Postulacion::with(['candidato', 'vacante', 'analisisIa'])->latest('id_postulacion')->take(10)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        Vacante::create($data + ['fecha_publicacion' => now()]);

        $this->audit('CREAR_VACANTE', 'Vacante publicada: '.$data['titulo'], $request);

        return back()->with('status', 'Vacante publicada correctamente.');
    }

    public function update(Request $request, Vacante $vacante)
    {
        $data = $request->validate($this->rules());
        $data['fecha_cierre'] = in_array($data['estado'], ['CERRADA', 'PAUSADA'], true) ? now() : null;

        $vacante->update($data);

        $this->audit('ACTUALIZAR_VACANTE', 'Vacante actualizada: '.$vacante->titulo, $request);

        return back()->with('status', 'Vacante actualizada correctamente.');
    }

    private function rules(): array
    {
        return [
            'id_departamento' => ['nullable', 'exists:tbl_departamentos,id_departamento'],
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string'],
            'requisitos' => ['nullable', 'string'],
            'tipo_contrato' => ['nullable', 'string', 'max:50'],
            'salario_ofrecido' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'in:ABIERTA,CERRADA,PAUSADA'],
        ];
    }

    private function audit(string $action, string $detail, Request $request): void
    {
        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Reclutamiento',
            'accion' => $action,
            'detalle' => $detail,
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);
    }
}
