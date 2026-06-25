<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Departamento;
use App\Models\Postulacion;
use App\Models\TestPregunta;
use App\Models\TestVacante;
use App\Models\Vacante;
use Illuminate\Http\Request;

class ReclutamientoController extends Controller
{
    public function index()
    {
        return view('modules.reclutamiento.index', [
            'vacantes' => Vacante::withCount('postulaciones')
                ->with(['departamento', 'testActivo.preguntas'])
                ->latest('id_vacante')
                ->paginate(10),
            'departamentos' => Departamento::orderBy('nombre_departamento')->get(),
            'postulaciones' => Postulacion::with(['candidato', 'vacante', 'analisisIa', 'respuestasTest'])
                ->latest('id_postulacion')
                ->take(10)
                ->get(),
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

    public function storeTest(Request $request, Vacante $vacante)
    {
        $data = $request->validate($this->testRules());

        if ($data['estado'] === 'ACTIVO') {
            $vacante->tests()->update(['estado' => 'INACTIVO']);
        }

        $vacante->tests()->create($data);

        $this->audit('CREAR_TEST_VACANTE', 'Test creado para vacante: '.$vacante->titulo, $request);

        return back()->with('status', 'Test creado para la vacante.');
    }

    public function updateTest(Request $request, TestVacante $test)
    {
        $data = $request->validate($this->testRules());

        if ($data['estado'] === 'ACTIVO') {
            TestVacante::where('id_vacante', $test->id_vacante)
                ->where('id_test', '!=', $test->id_test)
                ->update(['estado' => 'INACTIVO']);
        }

        $test->update($data);

        $this->audit('ACTUALIZAR_TEST_VACANTE', 'Test actualizado: '.$test->titulo, $request);

        return back()->with('status', 'Test actualizado correctamente.');
    }

    public function storePregunta(Request $request, TestVacante $test)
    {
        $preguntas = $this->validatedPreguntas($request);

        foreach ($preguntas as $data) {
            $test->preguntas()->create($data);
        }

        $this->audit('CREAR_PREGUNTA_TEST', count($preguntas).' pregunta(s) agregada(s) al test: '.$test->titulo, $request);

        return back()->with('status', count($preguntas).' pregunta(s) agregada(s) al test.');
    }

    public function updatePregunta(Request $request, TestPregunta $pregunta)
    {
        $pregunta->update($this->validatedPregunta($request));

        $this->audit('ACTUALIZAR_PREGUNTA_TEST', 'Pregunta de test actualizada ID '.$pregunta->id_pregunta, $request);

        return back()->with('status', 'Pregunta actualizada correctamente.');
    }

    public function destroyPregunta(Request $request, TestPregunta $pregunta)
    {
        $pregunta->delete();

        $this->audit('ELIMINAR_PREGUNTA_TEST', 'Pregunta de test eliminada ID '.$pregunta->id_pregunta, $request);

        return back()->with('status', 'Pregunta eliminada del test.');
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

    private function testRules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ];
    }

    private function validatedPregunta(Request $request): array
    {
        $data = $request->validate([
            'pregunta' => ['required', 'string'],
            'opciones' => ['nullable', 'array'],
            'opciones.*' => ['nullable', 'string', 'max:255'],
            'opciones_texto' => ['nullable', 'string'],
            'puntaje_maximo' => ['required', 'numeric', 'min:1', 'max:100'],
            'orden' => ['required', 'integer', 'min:1'],
        ]);

        return $this->normalizePregunta($data);
    }

    private function validatedPreguntas(Request $request): array
    {
        if (! $request->has('preguntas')) {
            return [$this->validatedPregunta($request)];
        }

        $data = $request->validate([
            'preguntas' => ['required', 'array', 'min:1'],
            'preguntas.*.pregunta' => ['required', 'string'],
            'preguntas.*.opciones' => ['required', 'array', 'min:2'],
            'preguntas.*.opciones.*' => ['required', 'string', 'max:255'],
            'preguntas.*.puntaje_maximo' => ['required', 'numeric', 'min:1', 'max:100'],
            'preguntas.*.orden' => ['required', 'integer', 'min:1'],
        ]);

        return collect($data['preguntas'])
            ->map(fn ($pregunta) => $this->normalizePregunta($pregunta))
            ->values()
            ->all();
    }

    private function normalizePregunta(array $data): array
    {
        $rawOptions = $data['opciones'] ?? preg_split('/\r\n|\r|\n/', (string) ($data['opciones_texto'] ?? ''));
        $opciones = collect($rawOptions)
            ->map(fn ($option) => trim($option))
            ->filter()
            ->values()
            ->all();

        if (count($opciones) < 2) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'opciones_texto' => 'Debe ingresar al menos dos opciones de respuesta.',
            ]);
        }

        return [
            'pregunta' => $data['pregunta'],
            'tipo' => 'OPCION_MULTIPLE',
            'opciones' => $opciones,
            'puntaje_maximo' => $data['puntaje_maximo'],
            'orden' => $data['orden'],
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
