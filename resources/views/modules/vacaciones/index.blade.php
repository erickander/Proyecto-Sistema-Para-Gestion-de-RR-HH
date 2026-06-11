@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Vacaciones y Permisos</h1>
    <p>{{ in_array(auth()->user()?->role?->nombre_rol, ['ADMINISTRADOR', 'RRHH'], true) ? 'Aprobacion y seguimiento de solicitudes.' : 'Solicitud y seguimiento de sus permisos.' }}</p>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif
@if(session('error')) <div class="alert-error">{{ session('error') }}</div> @endif

@if(auth()->user()?->role?->nombre_rol === 'EMPLEADO')
    <div class="form-panel">
        <h3>Nueva solicitud</h3>
        <form class="form-grid" action="{{ route('vacaciones.store') }}" method="POST">
            @csrf
            <label>Tipo
                <select name="tipo_solicitud">
                    <option value="VACACIONES">VACACIONES</option>
                    <option value="PERMISO">PERMISO</option>
                </select>
            </label>
            <label>Fecha inicio<input type="date" name="fecha_inicio" required></label>
            <label>Fecha fin<input type="date" name="fecha_fin" required></label>
            <label class="span-2">Motivo<input name="motivo" required></label>
            <button class="btn-primary" type="submit">Enviar solicitud</button>
        </form>
    </div>
@endif

<div class="table-container">
    <h3>Solicitudes</h3>
    <table>
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Tipo</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($solicitudes as $solicitud)
                <tr>
                    <td>{{ $solicitud->empleado?->nombres }} {{ $solicitud->empleado?->apellidos }}</td>
                    <td>{{ $solicitud->tipo_solicitud }}</td>
                    <td>{{ $solicitud->fecha_inicio?->format('Y-m-d') }}</td>
                    <td>{{ $solicitud->fecha_fin?->format('Y-m-d') }}</td>
                    <td><span class="status-pill">{{ $solicitud->estado }}</span></td>
                    <td>
                        @if(in_array(auth()->user()?->role?->nombre_rol, ['ADMINISTRADOR', 'RRHH'], true) && $solicitud->estado === 'PENDIENTE')
                            <form class="inline-actions" action="{{ route('vacaciones.approve', $solicitud) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input name="observaciones" placeholder="Observaciones">
                                <button class="btn-secondary" type="submit">Aprobar</button>
                            </form>
                            <form action="{{ route('vacaciones.reject', $solicitud) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn-danger" type="submit">Rechazar</button>
                            </form>
                        @else
                            {{ $solicitud->observaciones ?? 'Sin acciones' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Sin solicitudes registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
