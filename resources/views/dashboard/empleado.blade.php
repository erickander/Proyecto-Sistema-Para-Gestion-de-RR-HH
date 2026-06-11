@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Mi Panel de Empleado</h1>
    <p>Perfil, ingresos, solicitudes de vacaciones y notificaciones de RRHH.</p>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif
@if(session('error')) <div class="alert-error">{{ session('error') }}</div> @endif

<div class="cards-container">
    <div class="card"><h3>Cargo</h3><h2>{{ $empleado?->cargo ?? 'N/A' }}</h2></div>
    <div class="card"><h3>Estado</h3><h2>{{ $empleado?->estado ?? 'N/A' }}</h2></div>
    <div class="card"><h3>Salario base</h3><h2>{{ $empleado ? number_format($empleado->salario_base, 2) : '0.00' }}</h2></div>
    <div class="card"><h3>Solicitudes</h3><h2>{{ $solicitudes->count() }}</h2></div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>Historial de ingresos</h3>
        <canvas id="gananciasEmpleadoChart"></canvas>
    </div>

    <div class="form-panel no-margin">
        <h3>Actualizar perfil</h3>
        <form class="form-grid single" action="{{ route('perfil.update') }}" method="POST">
            @csrf
            @method('PUT')
            <label>Correo<input type="email" name="correo" value="{{ $empleado?->correo }}" required></label>
            <label>Telefono<input name="telefono" value="{{ $empleado?->telefono }}"></label>
            <button class="btn-primary" type="submit">Actualizar perfil</button>
        </form>
    </div>
</div>

<div class="form-panel">
    <h3>Solicitar vacaciones o permiso</h3>
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

<div class="table-container">
    <h3>Notificaciones recientes</h3>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Titulo</th>
                <th>Mensaje</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notificaciones as $notificacion)
                <tr>
                    <td>{{ $notificacion->tipo }}</td>
                    <td>{{ $notificacion->titulo }}</td>
                    <td>{{ $notificacion->mensaje }}</td>
                    <td>{{ $notificacion->fecha_envio?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin notificaciones.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    window.gananciasEmpleadoLabels = @json($nominas->pluck('periodo_fin')->map(fn($fecha) => optional($fecha)->format('Y-m-d'))->values());
    window.gananciasEmpleadoData = @json($nominas->pluck('total_pagar')->values());
</script>
@endsection
