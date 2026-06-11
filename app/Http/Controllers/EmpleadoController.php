<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    public function index()
    {
        return view('modules.empleados.index', [
            'empleados' => Empleado::with('departamento')
                ->where('estado', '!=', 'TERMINADO')
                ->latest('id_empleado')
                ->paginate(10),
            'departamentos' => Departamento::orderBy('nombre_departamento')->get(),
            'usuariosDisponibles' => User::with('role')
                ->where('estado', 'ACTIVO')
                ->whereHas('role', fn ($query) => $query->where('nombre_rol', 'EMPLEADO'))
                ->whereDoesntHave('empleado')
                ->orderBy('nombre_usuario')
                ->get(),
            'usuariosEmpleado' => User::with('role')
                ->where('estado', 'ACTIVO')
                ->whereHas('role', fn ($query) => $query->where('nombre_rol', 'EMPLEADO'))
                ->orderBy('nombre_usuario')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $empleado = Empleado::create($data);

        $this->audit('CREAR_EMPLEADO', 'Empleado creado: '.$empleado->nombres.' '.$empleado->apellidos, $request);

        return back()->with('status', 'Empleado creado correctamente.');
    }

    public function update(Request $request, Empleado $empleado)
    {
        $data = $request->validate($this->rules($empleado));
        $empleado->update($data);

        $this->audit('ACTUALIZAR_EMPLEADO', 'Empleado actualizado: '.$empleado->nombres.' '.$empleado->apellidos, $request);

        return back()->with('status', 'Empleado actualizado correctamente.');
    }

    public function destroy(Request $request, Empleado $empleado)
    {
        $empleado->update(['estado' => 'TERMINADO']);

        $this->audit('ELIMINAR_LOGICO_EMPLEADO', 'Empleado terminado: '.$empleado->nombres.' '.$empleado->apellidos, $request);

        return back()->with('status', 'Empleado eliminado logicamente.');
    }

    private function rules(?Empleado $empleado = null): array
    {
        return [
            'id_usuario' => [
                'required',
                'exists:tbl_usuarios,id_usuario',
                Rule::unique('tbl_empleados', 'id_usuario')->ignore($empleado?->id_empleado, 'id_empleado'),
            ],
            'id_departamento' => ['nullable', 'exists:tbl_departamentos,id_departamento'],
            'cedula' => ['required', 'string', 'max:20', Rule::unique('tbl_empleados', 'cedula')->ignore($empleado?->id_empleado, 'id_empleado')],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', Rule::unique('tbl_empleados', 'correo')->ignore($empleado?->id_empleado, 'id_empleado')],
            'telefono' => ['nullable', 'string', 'max:30'],
            'cargo' => ['required', 'string', 'max:100'],
            'salario_base' => ['required', 'numeric', 'min:0'],
            'fecha_ingreso' => ['required', 'date'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'VACACIONES', 'TERMINADO'])],
        ];
    }

    private function audit(string $action, string $detail, Request $request): void
    {
        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Empleados',
            'accion' => $action,
            'detalle' => $detail,
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);
    }
}
