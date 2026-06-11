@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Notificaciones</h1>
    <p>{{ in_array(auth()->user()?->role?->nombre_rol, ['ADMINISTRADOR', 'RRHH'], true) ? 'Envio de comunicaciones internas a empleados.' : 'Mensajes enviados por RRHH.' }}</p>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif

@if(in_array(auth()->user()?->role?->nombre_rol, ['ADMINISTRADOR', 'RRHH'], true))
    <div class="form-panel">
        <h3>Enviar notificacion</h3>
        <form class="form-grid" action="{{ route('notificaciones.store') }}" method="POST">
            @csrf
            <label>Usuario
                <select name="id_usuario" required>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id_usuario }}">{{ $usuario->nombre_usuario }} - {{ $usuario->role?->nombre_rol }}</option>
                    @endforeach
                </select>
            </label>
            <label>Tipo
                <select name="tipo">
                    <option value="RRHH">RRHH</option>
                    <option value="NOMINA">NOMINA</option>
                    <option value="VACACIONES">VACACIONES</option>
                    <option value="IA">IA</option>
                    <option value="SISTEMA">SISTEMA</option>
                </select>
            </label>
            <label>Titulo<input name="titulo" required></label>
            <label class="span-2">Mensaje<input name="mensaje" required></label>
            <button class="btn-primary" type="submit">Enviar</button>
        </form>
    </div>
@endif

<div class="table-container">
    <h3>Mensajes</h3>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Titulo</th>
                <th>Leida</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notificaciones as $notificacion)
                <tr>
                    <td>{{ $notificacion->usuario?->nombre_usuario }}</td>
                    <td>{{ $notificacion->tipo }}</td>
                    <td>{{ $notificacion->titulo }}</td>
                    <td>{{ $notificacion->leida ? 'Si' : 'No' }}</td>
                    <td>{{ $notificacion->fecha_envio?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin notificaciones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
