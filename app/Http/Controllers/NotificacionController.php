<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Notificacion::with('usuario')->latest('fecha_envio');

        if ($user?->role?->nombre_rol === 'EMPLEADO') {
            $query->where('id_usuario', $user->id_usuario);
        }

        return view('modules.notificaciones.index', [
            'notificaciones' => $query->paginate(10),
            'usuarios' => User::where('estado', 'ACTIVO')->orderBy('nombre_usuario')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'exists:tbl_usuarios,id_usuario'],
            'titulo' => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string'],
            'tipo' => ['required', 'in:SISTEMA,RRHH,IA,NOMINA,VACACIONES'],
        ]);

        Notificacion::create($data + [
            'leida' => false,
            'fecha_envio' => now(),
        ]);

        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Notificaciones',
            'accion' => 'ENVIAR_NOTIFICACION',
            'detalle' => 'Notificacion enviada: '.$data['titulo'],
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);

        return back()->with('status', 'Notificacion enviada correctamente.');
    }
}
