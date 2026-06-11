<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('modules.usuarios.index', [
            'usuarios' => User::with('role')->latest('id_usuario')->paginate(12),
            'roles' => Role::orderBy('nombre_rol')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_rol' => ['required', 'exists:tbl_roles,id_rol'],
            'nombre_usuario' => ['required', 'string', 'max:50', 'unique:tbl_usuarios,nombre_usuario'],
            'correo' => ['nullable', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $user = User::create([
            'id_rol' => $data['id_rol'],
            'nombre_usuario' => $data['nombre_usuario'],
            'correo' => $data['correo'],
            'password_hash' => Hash::make($data['password']),
            'estado' => $data['estado'],
        ]);

        $this->audit('Usuarios', 'CREAR_USUARIO', 'Usuario creado: '.$user->nombre_usuario, $request);

        return back()->with('status', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'id_rol' => ['required', 'exists:tbl_roles,id_rol'],
            'nombre_usuario' => ['required', 'string', 'max:50', Rule::unique('tbl_usuarios', 'nombre_usuario')->ignore($user->id_usuario, 'id_usuario')],
            'correo' => ['nullable', 'email', 'max:150'],
            'password' => ['nullable', 'string', 'min:6'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $payload = [
            'id_rol' => $data['id_rol'],
            'nombre_usuario' => $data['nombre_usuario'],
            'correo' => $data['correo'],
            'estado' => $data['estado'],
        ];

        if (! empty($data['password'])) {
            $payload['password_hash'] = Hash::make($data['password']);
        }

        $user->update($payload);

        $this->audit('Usuarios', 'ACTUALIZAR_USUARIO', 'Usuario actualizado: '.$user->nombre_usuario, $request);

        return back()->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id_usuario === auth()->id()) {
            return back()->with('error', 'No puede desactivar su propio usuario.');
        }

        $user->update(['estado' => 'INACTIVO']);

        $this->audit('Usuarios', 'DESACTIVAR_USUARIO', 'Usuario desactivado: '.$user->nombre_usuario, $request);

        return back()->with('status', 'Usuario desactivado correctamente.');
    }

    private function audit(string $module, string $action, string $detail, Request $request): void
    {
        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => $module,
            'accion' => $action,
            'detalle' => $detail,
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);
    }
}
