@extends('layouts.app')

@section('content')
<div class="dashboard-header split-header">
    <div>
        <h1>Reportes y Analitica</h1>
        <p>Reportes filtrados por periodo, departamento, estado y busqueda.</p>
    </div>
    <a class="btn-primary" href="{{ route('reportes.export', request()->query()) }}">Exportar CSV</a>
</div>

<div class="form-panel">
    <h3>Filtros del reporte</h3>
    <form class="form-grid" method="GET" action="{{ route('reportes.index') }}">
        <label>Reporte
            <select name="modulo">
                @foreach($modulos as $key => $label)
                    <option value="{{ $key }}" @selected($filters['modulo'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>Periodo
            <select name="periodo">
                @foreach($periodos as $key => $label)
                    <option value="{{ $key }}" @selected($filters['periodo'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>Semana<input type="date" name="semana_fecha" value="{{ $filters['semana_fecha'] }}"></label>
        <label>Mes<input type="month" name="mes" value="{{ $filters['mes'] }}"></label>
        <label>Año<input type="number" min="2000" max="2100" name="anio" value="{{ $filters['anio'] }}"></label>
        <label>Trimestre
            <select name="trimestre">
                @for($i = 1; $i <= 4; $i++)
                    <option value="{{ $i }}" @selected((int) $filters['trimestre'] === $i)>T{{ $i }}</option>
                @endfor
            </select>
        </label>
        <label>Desde<input type="date" name="fecha_inicio" value="{{ $filters['fecha_inicio'] }}"></label>
        <label>Hasta<input type="date" name="fecha_fin" value="{{ $filters['fecha_fin'] }}"></label>
        <label>Departamento
            <select name="id_departamento">
                <option value="">Todos</option>
                @foreach($departamentos as $departamento)
                    <option value="{{ $departamento->id_departamento }}" @selected(($filters['id_departamento'] ?? '') == $departamento->id_departamento)>
                        {{ $departamento->nombre_departamento }}
                    </option>
                @endforeach
            </select>
        </label>
        <label>Estado
            <select name="estado">
                <option value="">Todos</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado }}" @selected(($filters['estado'] ?? '') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </label>
        <label class="span-2">Buscar<input name="buscar" value="{{ $filters['buscar'] }}" placeholder="Empleado, candidato, cedula, cargo o vacante"></label>
        <button class="btn-primary" type="submit">Generar reporte</button>
        <a class="btn-light" href="{{ route('reportes.index') }}">Limpiar</a>
    </form>
</div>

<div class="cards-container">
    <div class="card"><h3>Periodo</h3><h2>{{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</h2></div>
    <div class="card"><h3>Empleados ingresados</h3><h2>{{ $summary['empleados_ingresados'] }}</h2></div>
    <div class="card"><h3>Total nomina</h3><h2>${{ number_format($summary['total_nomina'], 2) }}</h2></div>
    <div class="card"><h3>Postulaciones</h3><h2>{{ $summary['postulaciones'] }}</h2></div>
    <div class="card"><h3>Solicitudes</h3><h2>{{ $summary['solicitudes'] }}</h2></div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>Tendencia del reporte</h3>
        <canvas id="reportTrendChart"></canvas>
    </div>
    <div class="chart-card">
        <h3>Resumen del periodo</h3>
        <canvas id="reportSummaryChart"></canvas>
    </div>
</div>

<div class="table-container">
    <h3>Detalle: {{ $modulos[$filters['modulo']] }}</h3>

    @if($filters['modulo'] === 'resumen')
        <table>
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Empleados ingresados</td><td>{{ $summary['empleados_ingresados'] }}</td></tr>
                <tr><td>Nominas generadas</td><td>{{ $summary['nominas_generadas'] }}</td></tr>
                <tr><td>Total nomina</td><td>${{ number_format($summary['total_nomina'], 2) }}</td></tr>
                <tr><td>Postulaciones</td><td>{{ $summary['postulaciones'] }}</td></tr>
                <tr><td>Solicitudes vacaciones/permisos</td><td>{{ $summary['solicitudes'] }}</td></tr>
                <tr><td>Vacaciones aprobadas</td><td>{{ $summary['vacaciones_aprobadas'] }}</td></tr>
            </tbody>
        </table>
    @elseif($filters['modulo'] === 'empleados')
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Cedula</th>
                    <th>Departamento</th>
                    <th>Cargo</th>
                    <th>Salario</th>
                    <th>Ingreso</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detail as $empleado)
                    <tr>
                        <td><strong>{{ $empleado->nombres }} {{ $empleado->apellidos }}</strong></td>
                        <td>{{ $empleado->cedula }}</td>
                        <td>{{ $empleado->departamento?->nombre_departamento ?? 'General' }}</td>
                        <td>{{ $empleado->cargo }}</td>
                        <td>${{ number_format($empleado->salario_base, 2) }}</td>
                        <td>{{ $empleado->fecha_ingreso?->format('Y-m-d') }}</td>
                        <td><span class="status-pill">{{ $empleado->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7">Sin empleados para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $detail->links() }}
    @elseif($filters['modulo'] === 'nomina')
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Departamento</th>
                    <th>Periodo</th>
                    <th>Salario</th>
                    <th>Extras</th>
                    <th>Descuentos</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detail as $nomina)
                    <tr>
                        <td><strong>{{ $nomina->empleado?->nombres }} {{ $nomina->empleado?->apellidos }}</strong></td>
                        <td>{{ $nomina->empleado?->departamento?->nombre_departamento ?? 'General' }}</td>
                        <td>{{ $nomina->periodo_inicio?->format('Y-m') }}</td>
                        <td>${{ number_format($nomina->salario_base, 2) }}</td>
                        <td>${{ number_format($nomina->monto_horas_extra, 2) }}</td>
                        <td>${{ number_format($nomina->descuentos, 2) }}</td>
                        <td><span class="score-badge">${{ number_format($nomina->total_pagar, 2) }}</span></td>
                        <td><span class="status-pill">{{ $nomina->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8">Sin nominas para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $detail->links() }}
    @elseif($filters['modulo'] === 'postulaciones')
        <table>
            <thead>
                <tr>
                    <th>Candidato</th>
                    <th>Correo</th>
                    <th>Vacante</th>
                    <th>Departamento</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detail as $postulacion)
                    <tr>
                        <td><strong>{{ $postulacion->candidato?->nombres }} {{ $postulacion->candidato?->apellidos }}</strong></td>
                        <td>{{ $postulacion->candidato?->correo }}</td>
                        <td>{{ $postulacion->vacante?->titulo }}</td>
                        <td>{{ $postulacion->vacante?->departamento?->nombre_departamento ?? 'General' }}</td>
                        <td>{{ $postulacion->fecha_postulacion?->format('Y-m-d') }}</td>
                        <td><span class="status-pill">{{ $postulacion->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Sin postulaciones para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $detail->links() }}
    @else
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Departamento</th>
                    <th>Tipo</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Solicitud</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detail as $solicitud)
                    <tr>
                        <td><strong>{{ $solicitud->empleado?->nombres }} {{ $solicitud->empleado?->apellidos }}</strong></td>
                        <td>{{ $solicitud->empleado?->departamento?->nombre_departamento ?? 'General' }}</td>
                        <td>{{ $solicitud->tipo_solicitud }}</td>
                        <td>{{ $solicitud->fecha_inicio?->format('Y-m-d') }}</td>
                        <td>{{ $solicitud->fecha_fin?->format('Y-m-d') }}</td>
                        <td>{{ $solicitud->fecha_solicitud?->format('Y-m-d H:i') }}</td>
                        <td><span class="status-pill">{{ $solicitud->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7">Sin solicitudes para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $detail->links() }}
    @endif
</div>

<script>
    window.reportTrendLabels = @json($trendLabels);
    window.reportTrendData = @json($trendData);
    window.reportTrendLabel = @json($trendLabel);
    window.reportSummaryLabels = @json($summaryLabels);
    window.reportSummaryData = @json($summaryData);
</script>
@endsection
