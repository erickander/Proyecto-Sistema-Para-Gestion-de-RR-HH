<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;

class PerfilEmpleadoController extends Controller
{
    public function update(Request $request)
    {
        $empleado = auth()->user()?->empleado;

        if (! $empleado) {
            return back()->with('error', 'Su usuario no tiene empleado vinculado.');
        }

        $data = $request->validate([
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $empleado->update($data);

        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Perfil empleado',
            'accion' => 'ACTUALIZAR_PERFIL',
            'detalle' => 'Empleado actualizo sus datos de contacto.',
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);

        return back()->with('status', 'Perfil actualizado correctamente.');
    }
}
