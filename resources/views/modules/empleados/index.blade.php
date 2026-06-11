@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Gestion de Empleados</h1>
    <p>CRUD laboral de RRHH con eliminacion logica mediante estado TERMINADO.</p>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif

<div class="form-panel">
    <h3>Crear empleado</h3>
    @if($usuariosDisponibles->isEmpty())
        <div class="empty-state">
            No hay usuarios con rol EMPLEADO disponibles. El administrador debe crear el usuario primero o RRHH debe contratar un postulante desde IA.
        </div>
    @else
        <form class="form-grid" action="{{ route('empleados.store') }}" method="POST">
            @csrf
            <label>Usuario existente
                <select name="id_usuario" required>
                    <option value="">Seleccione usuario</option>
                    @foreach($usuariosDisponibles as $usuario)
                        <option value="{{ $usuario->id_usuario }}">{{ $usuario->nombre_usuario }} {{ $usuario->correo ? '- '.$usuario->correo : '' }}</option>
                    @endforeach
                </select>
            </label>
            <label>Cedula<input name="cedula" required></label>
            <label>Nombres<input name="nombres" required></label>
            <label>Apellidos<input name="apellidos" required></label>
            <label>Correo<input type="email" name="correo" required></label>
            <label>Telefono<input name="telefono"></label>
            <label>Cargo<input name="cargo" required></label>
            <label>Salario base<input type="number" step="0.01" name="salario_base" required></label>
            <label>Fecha ingreso<input type="date" name="fecha_ingreso" required></label>
            <label>Departamento
                <select name="id_departamento">
                    <option value="">Sin departamento</option>
                    @foreach($departamentos as $departamento)
                        <option value="{{ $departamento->id_departamento }}">{{ $departamento->nombre_departamento }}</option>
                    @endforeach
                </select>
            </label>
            <label>Estado
                <select name="estado">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                    <option value="VACACIONES">VACACIONES</option>
                </select>
            </label>
            <button class="btn-primary" type="submit">Crear empleado</button>
        </form>
    @endif
</div>

<div class="table-container">
    <h3>Empleados activos</h3>
    <table>
        <thead>
            <tr>
                <th>Cedula</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Usuario</th>
                <th>Departamento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empleados as $empleado)
                <tr>
                    <td>{{ $empleado->cedula }}</td>
                    <td>{{ $empleado->nombres }} {{ $empleado->apellidos }}</td>
                    <td>{{ $empleado->cargo }}</td>
                    <td>{{ $empleado->usuario?->nombre_usuario ?? 'Sin usuario' }}</td>
                    <td>{{ $empleado->departamento?->nombre_departamento ?? 'Sin departamento' }}</td>
                    <td><span class="status-pill">{{ $empleado->estado }}</span></td>
                    <td class="actions-cell">
                        <details class="action-drawer">
                            <summary>Editar</summary>
                            <form class="action-form wide" action="{{ route('empleados.update', $empleado) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <label>Usuario
                                    <select name="id_usuario" required>
                                        @foreach($usuariosEmpleado as $usuario)
                                            @if(! $usuario->empleado || $usuario->id_usuario === $empleado->id_usuario)
                                                <option value="{{ $usuario->id_usuario }}" @selected($empleado->id_usuario === $usuario->id_usuario)>
                                                    {{ $usuario->nombre_usuario }} {{ $usuario->correo ? '- '.$usuario->correo : '' }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </label>
                                <label>Cedula<input name="cedula" value="{{ $empleado->cedula }}" required></label>
                                <label>Nombres<input name="nombres" value="{{ $empleado->nombres }}" required></label>
                                <label>Apellidos<input name="apellidos" value="{{ $empleado->apellidos }}" required></label>
                                <label>Correo<input type="email" name="correo" value="{{ $empleado->correo }}" required></label>
                                <label>Telefono<input name="telefono" value="{{ $empleado->telefono }}"></label>
                                <label>Cargo<input name="cargo" value="{{ $empleado->cargo }}" required></label>
                                <label>Salario<input type="number" step="0.01" name="salario_base" value="{{ $empleado->salario_base }}" required></label>
                                <label>Ingreso<input type="date" name="fecha_ingreso" value="{{ $empleado->fecha_ingreso?->format('Y-m-d') }}" required></label>
                                <label>Departamento
                                    <select name="id_departamento">
                                        <option value="">Sin departamento</option>
                                        @foreach($departamentos as $departamento)
                                            <option value="{{ $departamento->id_departamento }}" @selected($empleado->id_departamento === $departamento->id_departamento)>{{ $departamento->nombre_departamento }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>Estado
                                    <select name="estado">
                                        @foreach(['ACTIVO','INACTIVO','VACACIONES','TERMINADO'] as $estado)
                                            <option value="{{ $estado }}" @selected($empleado->estado === $estado)>{{ $estado }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <button class="btn-secondary" type="submit">Actualizar</button>
                            </form>
                        </details>
                        <form class="action-mini" action="{{ route('empleados.destroy', $empleado) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn-danger" type="submit">Eliminar logico</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Sin empleados registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
