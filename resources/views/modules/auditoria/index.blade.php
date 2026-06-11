@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Auditoria y Seguridad</h1>
    <p>Logs, accesos, acciones y cambios del sistema.</p>
</div>

<div class="table-container">
    <h3>Registros</h3>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Accion</th>
                <th>IP</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($auditorias as $auditoria)
                <tr>
                    <td>{{ $auditoria->usuario?->nombre_usuario ?? 'Sistema' }}</td>
                    <td>{{ $auditoria->accion }}</td>
                    <td>{{ $auditoria->ip_address }}</td>
                    <td>{{ $auditoria->fecha_evento?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin auditorias registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
