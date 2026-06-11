@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Usuarios del Sistema</h1>
    <p>Administracion de accesos, roles y estados de seguridad.</p>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif
@if(session('error')) <div class="alert-error">{{ session('error') }}</div> @endif

<div class="form-panel">
    <h3>Crear usuario</h3>
    <form class="form-grid" action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        <label>Usuario<input name="nombre_usuario" value="{{ old('nombre_usuario') }}" required></label>
        <label>Correo<input type="email" name="correo" value="{{ old('correo') }}"></label>
        <label>Rol
            <select name="id_rol" required>
                @foreach($roles as $role)
                    <option value="{{ $role->id_rol }}">{{ $role->nombre_rol }}</option>
                @endforeach
            </select>
        </label>
        <label>Estado
            <select name="estado" required>
                <option value="ACTIVO">ACTIVO</option>
                <option value="INACTIVO">INACTIVO</option>
            </select>
        </label>
        <label>Contrasena<input type="password" name="password" required></label>
        <button class="btn-primary" type="submit">Crear usuario</button>
    </form>
</div>

<div class="table-container">
    <h3>Usuarios registrados</h3>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Ultimo acceso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->nombre_usuario }}</td>
                    <td>{{ $usuario->role?->nombre_rol }}</td>
                    <td>{{ $usuario->correo ?? 'Sin correo' }}</td>
                    <td><span class="status-pill">{{ $usuario->estado }}</span></td>
                    <td>{{ $usuario->ultimo_acceso?->format('Y-m-d H:i') ?? 'Sin acceso' }}</td>
                    <td class="actions-cell">
                        <details class="action-drawer">
                            <summary>Editar</summary>
                            <form class="action-form" action="{{ route('usuarios.update', $usuario) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <label>Usuario<input name="nombre_usuario" value="{{ $usuario->nombre_usuario }}" required></label>
                                <label>Correo<input type="email" name="correo" value="{{ $usuario->correo }}" placeholder="Correo"></label>
                                <label>Rol
                                    <select name="id_rol">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id_rol }}" @selected($usuario->id_rol === $role->id_rol)>{{ $role->nombre_rol }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>Estado
                                    <select name="estado">
                                        <option value="ACTIVO" @selected($usuario->estado === 'ACTIVO')>ACTIVO</option>
                                        <option value="INACTIVO" @selected($usuario->estado === 'INACTIVO')>INACTIVO</option>
                                    </select>
                                </label>
                                <label>Contrasena<input type="password" name="password" placeholder="Nueva contrasena"></label>
                                <button class="btn-secondary" type="submit">Actualizar</button>
                            </form>
                        </details>
                        <form class="action-mini" action="{{ route('usuarios.destroy', $usuario) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn-danger" type="submit">Desactivar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Sin usuarios registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
