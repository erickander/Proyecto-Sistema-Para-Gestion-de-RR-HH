<?php

namespace App\Http\Controllers;

use App\Models\AnalisisIa;
use App\Models\Auditoria;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Postulacion;
use App\Models\Role;
use App\Models\User;
use App\Services\GeminiCvAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IaController extends Controller
{
    public function index()
    {
        $ranking = AnalisisIa::with('postulacion.candidato', 'postulacion.vacante')
            ->orderByDesc('puntuacion_general')
            ->take(8)
            ->get();

        return view('modules.ia.index', [
            'analisis' => AnalisisIa::with('postulacion.candidato', 'postulacion.vacante', 'postulacion.respuestasTest.pregunta')
                ->orderByDesc('puntuacion_general')
                ->latest('fecha_analisis')
                ->paginate(10),
            'departamentos' => Departamento::orderBy('nombre_departamento')->get(),
            'rankingLabels' => $ranking->map(fn ($item) => trim(($item->postulacion?->candidato?->nombres ?? '').' '.($item->postulacion?->candidato?->apellidos ?? '')))->values(),
            'rankingFinalData' => $ranking->pluck('puntuacion_general')->map(fn ($score) => (float) $score)->values(),
            'rankingCvData' => $ranking->pluck('puntaje_cv')->map(fn ($score) => (float) $score)->values(),
            'rankingTestData' => $ranking->pluck('puntaje_test')->map(fn ($score) => (float) $score)->values(),
        ]);
    }

    public function reanalyze(Request $request, Postulacion $postulacion, GeminiCvAnalyzer $analyzer)
    {
        $request->validate([
            'consentimiento' => ['accepted'],
        ]);

        $postulacion->load('candidato', 'vacante', 'respuestasTest.pregunta');
        $cvPath = $postulacion->candidato?->cv_url;

        if (! $cvPath || ! Storage::disk('public')->exists($cvPath)) {
            return back()->with('status', 'No se encontro el CV para reanalizar.');
        }

        $absolutePath = Storage::disk('public')->path($cvPath);
        $file = new UploadedFile($absolutePath, basename($absolutePath), 'application/pdf', null, true);

        $analyzer->analyze($postulacion, $file);

        $this->audit('REANALIZAR_CV', 'CV reanalizado para postulacion ID '.$postulacion->id_postulacion, $request);

        return back()->with('status', 'CV reanalizado con Gemini.');
    }

    public function hire(Request $request, Postulacion $postulacion)
    {
        $data = $request->validate([
            'id_departamento' => ['nullable', 'exists:tbl_departamentos,id_departamento'],
            'cargo' => ['required', 'string', 'max:100'],
            'salario_base' => ['required', 'numeric', 'min:0'],
            'fecha_ingreso' => ['required', 'date'],
        ]);

        $postulacion->load('candidato', 'vacante');
        $candidate = $postulacion->candidato;
        $password = Str::password(10);
        $username = $this->uniqueUsername(Str::slug($candidate->nombres.'.'.$candidate->apellidos, '.'));
        $role = Role::where('nombre_rol', 'EMPLEADO')->firstOrFail();

        $user = User::create([
            'id_rol' => $role->id_rol,
            'nombre_usuario' => $username,
            'correo' => $candidate->correo,
            'password_hash' => Hash::make($password),
            'estado' => 'ACTIVO',
        ]);

        Empleado::create([
            'id_usuario' => $user->id_usuario,
            'id_departamento' => $data['id_departamento'],
            'cedula' => $candidate->cedula ?? 'CAND-'.$candidate->id_candidato,
            'nombres' => $candidate->nombres,
            'apellidos' => $candidate->apellidos,
            'telefono' => $candidate->telefono,
            'direccion' => $candidate->direccion,
            'correo' => $candidate->correo,
            'cargo' => $data['cargo'],
            'salario_base' => $data['salario_base'],
            'fecha_ingreso' => $data['fecha_ingreso'],
            'estado' => 'ACTIVO',
        ]);

        $candidate->update(['estado' => 'CONTRATADO']);
        $postulacion->update(['estado' => 'APROBADA', 'observaciones' => 'Contratado desde modulo IA.']);

        Mail::raw(
            "Felicidades {$candidate->nombres}, has sido contratado.\n\nUsuario: {$username}\nContrasena: {$password}\nAcceso: ".route('login'),
            fn ($message) => $message->to($candidate->correo)->subject('Resultado de postulacion - Contratado')
        );

        $this->audit('CONTRATAR_CANDIDATO', 'Candidato contratado: '.$candidate->correo, $request);

        return back()->with('status', 'Candidato contratado. Usuario creado y correo enviado.');
    }

    public function reject(Request $request, Postulacion $postulacion)
    {
        $postulacion->load('candidato');
        $candidate = $postulacion->candidato;

        $postulacion->update(['estado' => 'RECHAZADA', 'observaciones' => $request->input('observaciones', 'Rechazado desde modulo IA.')]);
        $candidate?->update(['estado' => 'DESCARTADO']);

        if ($candidate?->correo) {
            Mail::raw(
                "Hola {$candidate->nombres}, gracias por postular. En esta ocasion no continuaremos con tu candidatura.",
                fn ($message) => $message->to($candidate->correo)->subject('Resultado de postulacion')
            );
        }

        $this->audit('RECHAZAR_CANDIDATO', 'Candidato rechazado: '.$candidate?->correo, $request);

        return back()->with('status', 'Candidato rechazado y correo enviado.');
    }

    private function uniqueUsername(string $base): string
    {
        $base = $base ?: 'empleado';
        $username = $base;
        $counter = 1;

        while (User::where('nombre_usuario', $username)->exists()) {
            $username = $base.'.'.$counter++;
        }

        return $username;
    }

    private function audit(string $action, string $detail, Request $request): void
    {
        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'IA',
            'accion' => $action,
            'detalle' => $detail,
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);
    }
}
