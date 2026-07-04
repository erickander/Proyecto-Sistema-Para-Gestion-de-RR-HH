<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    public function store(Request $request, Vacante $vacante)
    {
        abort_if($vacante->estado !== 'ABIERTA', 404);

        $vacante->load('testActivo.preguntas');
        $test = $vacante->testActivo;
        $request->merge([
            'nombres' => trim((string) $request->input('nombres')),
            'apellidos' => trim((string) $request->input('apellidos')),
            'cedula' => $request->filled('cedula') ? trim((string) $request->input('cedula')) : null,
            'correo' => Str::lower(trim((string) $request->input('correo'))),
            'telefono' => $request->filled('telefono') ? trim((string) $request->input('telefono')) : null,
            'direccion' => $request->filled('direccion') ? trim((string) $request->input('direccion')) : null,
        ]);

        $data = $request->validate([
            'nombres' => ['required', 'string', 'min:2', 'max:100'],
            'apellidos' => ['required', 'string', 'min:2', 'max:100'],
            'cedula' => ['nullable', 'string', 'min:6', 'max:20', 'regex:/^[0-9A-Za-z-]+$/'],
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'min:7', 'max:30'],
            'direccion' => ['nullable', 'string', 'min:5', 'max:255'],
            'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'consentimiento_ia' => ['accepted'],
        ], [
            'nombres.required' => 'Ingrese sus nombres.',
            'nombres.min' => 'Los nombres deben tener al menos :min caracteres.',
            'apellidos.required' => 'Ingrese sus apellidos.',
            'apellidos.min' => 'Los apellidos deben tener al menos :min caracteres.',
            'cedula.min' => 'La cedula debe tener al menos :min caracteres.',
            'cedula.regex' => 'La cedula solo puede contener letras, numeros o guiones.',
            'correo.required' => 'Ingrese su correo.',
            'correo.email' => 'Ingrese un correo valido.',
            'telefono.min' => 'El telefono debe tener al menos :min caracteres.',
            'direccion.min' => 'La direccion debe tener al menos :min caracteres.',
            'cv.required' => 'Debe cargar su CV en PDF.',
            'cv.file' => 'El CV debe ser un archivo valido.',
            'cv.mimes' => 'El CV debe estar en formato PDF.',
            'cv.max' => 'El CV no debe superar 5 MB.',
            'consentimiento_ia.accepted' => 'Debe autorizar el analisis IA para continuar.',
        ]);

        $candidato = $this->validatedCandidateForVacancy($data, $vacante);

        $path = null;

        try {
            $path = $request->file('cv')->store('cvs', 'public');

            $candidato->fill([
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'cedula' => $data['cedula'] ?: $candidato->cedula,
                'telefono' => $data['telefono'] ?: $candidato->telefono,
                'direccion' => $data['direccion'] ?: $candidato->direccion,
                'cv_url' => $path,
                'fecha_registro' => now(),
                'estado' => 'ACTIVO',
            ])->save();

            $postulacion = Postulacion::create([
                'id_candidato' => $candidato->id_candidato,
                'id_vacante' => $vacante->id_vacante,
                'fecha_postulacion' => now(),
                'estado' => 'RECIBIDA',
                'observaciones' => 'Postulacion registrada desde portal publico.',
                'token_test' => Str::random(48),
            ]);
        } catch (QueryException) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw ValidationException::withMessages([
                'correo' => 'No se pudo registrar la postulacion porque ya existen datos similares. Revise correo, cedula o vuelva a intentarlo.',
            ]);
        }

        if ($test && $test->preguntas->isNotEmpty()) {
            return redirect()->route('portal.test.show', [
                'postulacion' => $postulacion,
                'token' => $postulacion->token_test,
            ]);
        }

        return redirect()->route('portal.gracias')->with('status', 'Postulacion enviada. RRHH debe activar un test para completar el analisis IA.');
    }

    private function validatedCandidateForVacancy(array $data, Vacante $vacante): Candidato
    {
        $candidateByEmail = Candidato::where('correo', $data['correo'])->first();
        $candidateByDocument = empty($data['cedula'])
            ? null
            : Candidato::where('cedula', $data['cedula'])->first();

        if ($candidateByDocument && $candidateByEmail && $candidateByDocument->id_candidato !== $candidateByEmail->id_candidato) {
            throw ValidationException::withMessages([
                'cedula' => 'La cedula ingresada ya esta registrada con otro correo.',
                'correo' => 'El correo ingresado pertenece a otro candidato.',
            ]);
        }

        if ($candidateByDocument && ! $candidateByEmail) {
            throw ValidationException::withMessages([
                'cedula' => 'La cedula ingresada ya esta registrada. Use el mismo correo con el que se postulo anteriormente.',
            ]);
        }

        if ($candidateByEmail && $candidateByEmail->cedula && ! empty($data['cedula']) && $candidateByEmail->cedula !== $data['cedula']) {
            throw ValidationException::withMessages([
                'cedula' => 'La cedula no coincide con el correo registrado.',
            ]);
        }

        $candidate = $candidateByEmail ?? new Candidato(['correo' => $data['correo']]);

        if ($candidate->exists && Postulacion::where('id_candidato', $candidate->id_candidato)
            ->where('id_vacante', $vacante->id_vacante)
            ->whereNotIn('estado', ['RECHAZADA', 'rechazada'])
            ->exists()) {
            throw ValidationException::withMessages([
                'correo' => 'Ya existe una postulacion activa con este correo para esta vacante.',
            ]);
        }

        return $candidate;
    }

    public function showTest(Postulacion $postulacion, string $token)
    {
        $this->authorizeTestToken($postulacion, $token);

        $postulacion->load('candidato', 'vacante.testActivo.preguntas');
        $test = $postulacion->vacante?->testActivo;

        abort_if(! $test || $test->preguntas->isEmpty(), 404);

        return view('portal.test', compact('postulacion', 'test'));
    }

    public function submitTest(Request $request, Postulacion $postulacion, string $token)
    {
        $this->authorizeTestToken($postulacion, $token);

        $postulacion->load('candidato', 'vacante.testActivo.preguntas');
        $test = $postulacion->vacante?->testActivo;
        abort_if(! $test || $test->preguntas->isEmpty(), 404);

        $respuestas = $this->validatedTestResponses($request, $test);

        $puntajeObtenido = 0;
        $puntajeMaximo = 0;

        foreach ($respuestas as $idPregunta => $respuesta) {
            $pregunta = $test->preguntas->firstWhere('id_pregunta', $idPregunta);
            $esCorrecta = $pregunta && $respuesta === $pregunta->respuesta_correcta;
            $puntaje = $esCorrecta ? (float) $pregunta->puntaje_maximo : 0;
            $puntajeObtenido += $puntaje;
            $puntajeMaximo += (float) ($pregunta?->puntaje_maximo ?? 0);

            $postulacion->respuestasTest()->updateOrCreate(
                ['id_pregunta' => $idPregunta],
                [
                    'respuesta' => $respuesta,
                    'es_correcta' => $esCorrecta,
                    'puntaje_test' => $puntaje,
                    'observacion_ia' => null,
                ]
            );
        }

        $calificacion = $puntajeMaximo > 0 ? round(($puntajeObtenido / $puntajeMaximo) * 100, 2) : 0;

        $postulacion->update([
            'fecha_test' => now(),
            'observaciones' => 'Test completado desde portal publico. Pendiente de analisis IA por RRHH. Calificacion test: '.$calificacion.'/100.',
        ]);

        return redirect()->route('portal.gracias')->with('status', 'Test enviado correctamente. Tu calificacion fue '.$calificacion.'/100. RRHH revisara tu postulacion y decidira si la envia a analisis IA.');
    }

    public function gracias()
    {
        return view('portal.gracias');
    }

    private function validatedTestResponses(Request $request, $test): array
    {
        if (! $test || $test->preguntas->isEmpty()) {
            return [];
        }

        $answers = $request->input('respuestas', []);
        $validated = [];

        foreach ($test->preguntas as $pregunta) {
            $answer = trim((string) ($answers[$pregunta->id_pregunta] ?? ''));

            if ($answer === '') {
                throw ValidationException::withMessages([
                    'respuestas.'.$pregunta->id_pregunta => 'Debe responder todas las preguntas del test.',
                ]);
            }

            if (! in_array($answer, $pregunta->opciones ?? [], true)) {
                throw ValidationException::withMessages([
                    'respuestas.'.$pregunta->id_pregunta => 'Una respuesta del test no es valida.',
                ]);
            }

            $validated[$pregunta->id_pregunta] = $answer;
        }

        return $validated;
    }

    private function authorizeTestToken(Postulacion $postulacion, string $token): void
    {
        abort_if(! hash_equals((string) $postulacion->token_test, $token), 404);
    }
}
