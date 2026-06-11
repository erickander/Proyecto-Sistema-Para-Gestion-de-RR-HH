@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Administracion del Sistema</h1>
    <p>Gestion de usuarios, configuracion general y auditoria de acciones.</p>
</div>

<div class="cards-container">
    <div class="card"><h3>Usuarios</h3><h2>{{ $usuarios }}</h2></div>
    <div class="card"><h3>Usuarios activos</h3><h2>{{ $usuariosActivos }}</h2></div>
    <div class="card"><h3>Roles</h3><h2>{{ $roles }}</h2></div>
    <div class="card"><h3>Auditorias</h3><h2>{{ $auditorias }}</h2></div>
</div>

<div class="quick-actions">
    <a class="action-card" href="{{ route('usuarios.index') }}">
        <strong>Usuarios del sistema</strong>
        <span>Crear, actualizar y desactivar accesos.</span>
    </a>
    <a class="action-card" href="{{ route('sistema.index') }}">
        <strong>Opciones del sistema</strong>
        <span>Roles, departamentos y configuracion base.</span>
    </a>
    <a class="action-card" href="{{ route('auditoria.index') }}">
        <strong>Auditoria</strong>
        <span>Ver acciones realizadas dentro del sistema.</span>
    </a>
</div>  


<div class="table-container">
    <h3>Actividad reciente</h3>
    <table>
        <thead>
            <tr>
                <th>Modulo</th>
                <th>Accion</th>
                <th>Detalle</th>
                <th>IP</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($actividadReciente as $auditoria)
                <tr>
                    <td>{{ $auditoria->modulo }}</td>
                    <td>{{ $auditoria->accion }}</td>
                    <td>{{ $auditoria->detalle }}</td>
                    <td>{{ $auditoria->ip_address }}</td>
                    <td>{{ $auditoria->fecha_evento?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin actividad registrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
