<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Services\GeminiCvAnalyzer;
use Illuminate\Http\UploadedFile;
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
            'token_test' => Str::random(48),
        ]);

        if ($test && $test->preguntas->isNotEmpty()) {
            return redirect()->route('portal.test.show', [
                'postulacion' => $postulacion,
                'token' => $postulacion->token_test,
            ]);
        }

        return redirect()->route('portal.gracias')->with('status', 'Postulacion enviada. RRHH debe activar un test para completar el analisis IA.');
    }

    public function showTest(Postulacion $postulacion, string $token)
    {
        $this->authorizeTestToken($postulacion, $token);

        $postulacion->load('candidato', 'vacante.testActivo.preguntas');
        $test = $postulacion->vacante?->testActivo;

        abort_if(! $test || $test->preguntas->isEmpty(), 404);

        return view('portal.test', compact('postulacion', 'test'));
    }

    public function submitTest(Request $request, Postulacion $postulacion, string $token, GeminiCvAnalyzer $analyzer)
    {
        $this->authorizeTestToken($postulacion, $token);

        $postulacion->load('candidato', 'vacante.testActivo.preguntas');
        $test = $postulacion->vacante?->testActivo;
        abort_if(! $test || $test->preguntas->isEmpty(), 404);

        $respuestas = $this->validatedTestResponses($request, $test);

        foreach ($respuestas as $idPregunta => $respuesta) {
            $postulacion->respuestasTest()->updateOrCreate(
                ['id_pregunta' => $idPregunta],
                ['respuesta' => $respuesta]
            );
        }

        $cvPath = $postulacion->candidato?->cv_url;

        if (! $cvPath || ! Storage::disk('public')->exists($cvPath)) {
            return back()->withErrors(['cv' => 'No se encontro el CV asociado a la postulacion.']);
        }

        $absolutePath = Storage::disk('public')->path($cvPath);
        $file = new UploadedFile($absolutePath, basename($absolutePath), 'application/pdf', null, true);

        $postulacion->update([
            'fecha_test' => now(),
            'observaciones' => 'Test completado desde portal publico. Analisis IA ejecutado.',
        ]);

        $analyzer->analyze($postulacion->load('vacante', 'respuestasTest.pregunta'), $file);

        return redirect()->route('portal.gracias')->with('status', 'Test enviado correctamente. RRHH revisara el resultado del analisis IA.');
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
