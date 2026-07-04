<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user?->role?->nombre_rol;
        $isEmployee = $role === 'EMPLEADO';
        $filters = $request->only(['buscar', 'tipo', 'estado', 'id_usuario', 'fecha_inicio', 'fecha_fin']);
        $query = Notificacion::with('usuario.role')->latest('fecha_envio');

        if ($isEmployee) {
            $query->where('id_usuario', $user->id_usuario);
        } else {
            $this->applyFilters($query, $filters);
        }

        return view('modules.notificaciones.index', [
            'notificaciones' => $query->paginate(10)->withQueryString(),
            'usuarios' => User::with('role', 'empleado')
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre_usuario')
                ->get(),
            'tipos' => ['SISTEMA', 'RRHH', 'IA', 'NOMINA', 'VACACIONES'],
            'rolesDestino' => ['ADMINISTRADOR', 'RRHH', 'EMPLEADO'],
            'filters' => $filters,
            'unreadCount' => Notificacion::where('id_usuario', $user?->id_usuario)->where('leida', false)->count(),
            'isEmployee' => $isEmployee,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'destino' => ['required', 'in:usuario,rol,todos'],
            'id_usuario' => ['nullable', 'required_if:destino,usuario', 'exists:tbl_usuarios,id_usuario'],
            'rol_destino' => ['nullable', 'required_if:destino,rol', Rule::in(['ADMINISTRADOR', 'RRHH', 'EMPLEADO'])],
            'titulo' => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string'],
            'tipo' => ['required', 'in:SISTEMA,RRHH,IA,NOMINA,VACACIONES'],
        ]);

        $targets = $this->targetUsers($data);

        if ($targets->isEmpty()) {
            return back()->withErrors(['destino' => 'No existen usuarios activos para el destino seleccionado.'])->withInput();
        }

        foreach ($targets as $target) {
            Notificacion::create([
                'id_usuario' => $target->id_usuario,
                'titulo' => $data['titulo'],
                'mensaje' => $data['mensaje'],
                'tipo' => $data['tipo'],
                'leida' => false,
                'fecha_envio' => now(),
            ]);
        }

        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Notificaciones',
            'accion' => 'ENVIAR_NOTIFICACION',
            'detalle' => 'Notificacion enviada a '.$targets->count().' usuario(s): '.$data['titulo'],
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);

        return back()->with('status', $targets->count().' notificacion(es) enviada(s) correctamente.');
    }

    public function markRead(Request $request, Notificacion $notificacion)
    {
        $this->authorizeNotification($notificacion);

        $notificacion->update([
            'leida' => true,
            'fecha_lectura' => $notificacion->fecha_lectura ?? now(),
        ]);

        $this->audit('LEER_NOTIFICACION', 'Notificacion marcada como leida ID '.$notificacion->id_notificacion, $request);

        return back()->with('status', 'Notificacion marcada como leida.');
    }

    public function markUnread(Request $request, Notificacion $notificacion)
    {
        $this->authorizeNotification($notificacion);

        $notificacion->update([
            'leida' => false,
            'fecha_lectura' => null,
        ]);

        $this->audit('NO_LEIDA_NOTIFICACION', 'Notificacion marcada como no leida ID '.$notificacion->id_notificacion, $request);

        return back()->with('status', 'Notificacion marcada como no leida.');
    }

    public function markAllRead(Request $request)
    {
        $updated = Notificacion::where('id_usuario', auth()->id())
            ->where('leida', false)
            ->update([
                'leida' => true,
                'fecha_lectura' => now(),
            ]);

        $this->audit('LEER_TODAS_NOTIFICACIONES', 'Notificaciones marcadas como leidas: '.$updated, $request);

        return back()->with('status', $updated.' notificacion(es) marcada(s) como leidas.');
    }

    public function destroy(Request $request, Notificacion $notificacion)
    {
        $this->authorizeNotification($notificacion);
        $title = $notificacion->titulo;
        $notificacion->delete();

        $this->audit('ELIMINAR_NOTIFICACION', 'Notificacion eliminada: '.$title, $request);

        return back()->with('status', 'Notificacion eliminada correctamente.');
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['buscar'])) {
            $search = trim($filters['buscar']);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('titulo', 'like', '%'.$search.'%')
                    ->orWhere('mensaje', 'like', '%'.$search.'%')
                    ->orWhereHas('usuario', function ($userQuery) use ($search) {
                        $userQuery->where('nombre_usuario', 'like', '%'.$search.'%')
                            ->orWhere('correo', 'like', '%'.$search.'%');
                    });
            });
        }

        if (! empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (($filters['estado'] ?? '') === 'leidas') {
            $query->where('leida', true);
        }

        if (($filters['estado'] ?? '') === 'pendientes') {
            $query->where('leida', false);
        }

        if (! empty($filters['id_usuario'])) {
            $query->where('id_usuario', $filters['id_usuario']);
        }

        if (! empty($filters['fecha_inicio'])) {
            $query->whereDate('fecha_envio', '>=', $filters['fecha_inicio']);
        }

        if (! empty($filters['fecha_fin'])) {
            $query->whereDate('fecha_envio', '<=', $filters['fecha_fin']);
        }
    }

    private function targetUsers(array $data)
    {
        return match ($data['destino']) {
            'usuario' => User::with('role')->where('estado', 'ACTIVO')->where('id_usuario', $data['id_usuario'])->get(),
            'rol' => User::with('role')
                ->where('estado', 'ACTIVO')
                ->whereHas('role', fn ($query) => $query->where('nombre_rol', $data['rol_destino']))
                ->get(),
            default => User::with('role')->where('estado', 'ACTIVO')->get(),
        };
    }

    private function authorizeNotification(Notificacion $notificacion): void
    {
        $role = auth()->user()?->role?->nombre_rol;

        abort_if($role === 'EMPLEADO' && $notificacion->id_usuario !== auth()->id(), 403);
    }

    private function audit(string $action, string $detail, Request $request): void
    {
        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Notificaciones',
            'accion' => $action,
            'detalle' => $detail,
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);
    }
}
