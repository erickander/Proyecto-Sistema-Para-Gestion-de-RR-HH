<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Notificacion;
use App\Models\SolicitudVacacion;
use Illuminate\Http\Request;

class VacacionesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = SolicitudVacacion::with('empleado')->latest('fecha_solicitud');

        if ($user?->role?->nombre_rol === 'EMPLEADO') {
            $query->where('id_empleado', $user->empleado?->id_empleado);
        }

        return view('modules.vacaciones.index', [
            'solicitudes' => $query->paginate(10),
            'empleado' => $user?->empleado,
        ]);
    }

    public function store(Request $request)
    {
        $empleado = auth()->user()?->empleado;

        if (! $empleado) {
            return back()->with('error', 'Su usuario no tiene empleado vinculado.');
        }

        $data = $request->validate([
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['required', 'string'],
            'tipo_solicitud' => ['required', 'in:VACACIONES,PERMISO'],
        ]);

        SolicitudVacacion::create([
            'id_empleado' => $empleado->id_empleado,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'motivo' => $data['motivo'],
            'tipo_solicitud' => $data['tipo_solicitud'],
            'estado' => 'PENDIENTE',
            'fecha_solicitud' => now(),
        ]);

        $this->audit('SOLICITAR_VACACIONES', 'Solicitud creada por '.$empleado->nombres.' '.$empleado->apellidos, $request);

        return back()->with('status', 'Solicitud enviada correctamente.');
    }

    public function approve(Request $request, SolicitudVacacion $solicitud)
    {
        $solicitud->update([
            'estado' => 'APROBADA',
            'revisado_por' => auth()->id(),
            'fecha_revision' => now(),
            'observaciones' => $request->input('observaciones'),
        ]);

        $this->audit('APROBAR_SOLICITUD', 'Solicitud aprobada ID '.$solicitud->id_solicitud, $request);
        $this->notifyEmployee($solicitud, 'Solicitud aprobada', 'Su solicitud de '.$solicitud->tipo_solicitud.' del '.$solicitud->fecha_inicio?->format('Y-m-d').' al '.$solicitud->fecha_fin?->format('Y-m-d').' fue aprobada.');

        return back()->with('status', 'Solicitud aprobada.');
    }

    public function reject(Request $request, SolicitudVacacion $solicitud)
    {
        $solicitud->update([
            'estado' => 'RECHAZADA',
            'revisado_por' => auth()->id(),
            'fecha_revision' => now(),
            'observaciones' => $request->input('observaciones'),
        ]);

        $this->audit('RECHAZAR_SOLICITUD', 'Solicitud rechazada ID '.$solicitud->id_solicitud, $request);
        $this->notifyEmployee($solicitud, 'Solicitud rechazada', 'Su solicitud de '.$solicitud->tipo_solicitud.' del '.$solicitud->fecha_inicio?->format('Y-m-d').' al '.$solicitud->fecha_fin?->format('Y-m-d').' fue rechazada.');

        return back()->with('status', 'Solicitud rechazada.');
    }

    private function notifyEmployee(SolicitudVacacion $solicitud, string $title, string $message): void
    {
        $solicitud->loadMissing('empleado');
        $userId = $solicitud->empleado?->id_usuario;

        if (! $userId) {
            return;
        }

        Notificacion::create([
            'id_usuario' => $userId,
            'titulo' => $title,
            'mensaje' => $message,
            'tipo' => 'VACACIONES',
            'leida' => false,
            'fecha_envio' => now(),
        ]);
    }

    private function audit(string $action, string $detail, Request $request): void
    {
        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Vacaciones',
            'accion' => $action,
            'detalle' => $detail,
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);
    }
}
