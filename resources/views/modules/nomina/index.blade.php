@extends('layouts.app')

@section('content')
<div class="dashboard-header split-header">
    <div>
        <h1>Nomina</h1>
        <p>Genera pagos desde el salario del empleado, controla estados y analiza pagos por periodo.</p>
    </div>
    <button class="btn-primary js-open-modal" type="button" data-modal-target="modal-generar-nomina">Generar nomina</button>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif
@if($errors->any())
    <div class="alert-error">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="cards-container">
    <div class="card"><h3>Total filtrado</h3><h2>${{ number_format($totalNomina, 2) }}</h2></div>
    <div class="card"><h3>Pendientes</h3><h2>{{ $pendientes }}</h2></div>
    <div class="card"><h3>Pagadas</h3><h2>{{ $pagadas }}</h2></div>
</div>

<div class="analysis-modal app-modal" id="modal-generar-nomina" hidden>
    <div class="analysis-modal__backdrop" data-modal-close></div>
    <section class="analysis-modal__panel" role="dialog" aria-modal="true" aria-labelledby="modal-generar-nomina-title">
        <header class="analysis-modal__header">
            <div>
                <span>Nomina</span>
                <h2 id="modal-generar-nomina-title">Generar nomina mensual</h2>
                <p>El salario base se toma directamente del empleado seleccionado.</p>
            </div>
            <button class="modal-close" type="button" aria-label="Cerrar" data-modal-close>&times;</button>
        </header>
        <div class="analysis-modal__body">
            <form class="modal-form" action="{{ route('nomina.store') }}" method="POST">
                @csrf
                <label>Empleado
                    <select name="id_empleado" required>
                        <option value="">Seleccione empleado</option>
                        @foreach($empleados as $empleado)
                            <option value="{{ $empleado->id_empleado }}">
                                {{ $empleado->apellidos }} {{ $empleado->nombres }} - ${{ number_format($empleado->salario_base, 2) }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label>Mes<input type="month" name="mes" value="{{ now()->format('Y-m') }}" required></label>
                <label>Horas extra<input type="number" step="0.01" name="horas_extra" value="0"></label>
                <label>Monto horas extra<input type="number" step="0.01" name="monto_horas_extra" value="0"></label>
                <label>Descuentos<input type="number" step="0.01" name="descuentos" value="0"></label>
                <label>Estado
                    <select name="estado" required>
                        <option value="PENDIENTE">PENDIENTE</option>
                        <option value="BORRADOR">BORRADOR</option>
                        <option value="APROBADA">APROBADA</option>
                        <option value="PAGADA">PAGADA</option>
                    </select>
                </label>
                <div class="modal-actions">
                    <button class="btn-light" type="button" data-modal-close>Cancelar</button>
                    <button class="btn-primary" type="submit">Generar nomina</button>
                </div>
            </form>
        </div>
    </section>
</div>

<div class="form-panel">
    <h3>Buscar nominas</h3>
    <form class="form-grid" method="GET" action="{{ route('nomina.index') }}">
        <label>Mes<input type="month" name="mes" value="{{ $filters['mes'] ?? '' }}"></label>
        <label>Empleado<input name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Nombre, apellido o cedula"></label>
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
        <button class="btn-primary" type="submit">Buscar</button>
        <a class="btn-light" href="{{ route('nomina.index') }}">Limpiar</a>
    </form>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>Total pagado por mes</h3>
        <canvas id="nominaMensualChart"></canvas>
    </div>
    <div class="chart-card">
        <h3>Nominas por estado</h3>
        <canvas id="nominaEstadoChart"></canvas>
    </div>
</div>

<div class="table-container">
    <h3>Historial de nominas</h3>
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
                <th>Accion</th>
            </tr>
        </thead>
        <tbody>
            @forelse($nominas as $nomina)
                <tr>
                    <td>
                        <strong>{{ $nomina->empleado?->nombres }} {{ $nomina->empleado?->apellidos }}</strong><br>
                        <span class="muted-text">{{ $nomina->empleado?->cargo }}</span>
                    </td>
                    <td>{{ $nomina->empleado?->departamento?->nombre_departamento ?? 'General' }}</td>
                    <td>{{ $nomina->periodo_inicio?->format('Y-m') }}</td>
                    <td>${{ number_format($nomina->salario_base, 2) }}</td>
                    <td>${{ number_format($nomina->monto_horas_extra, 2) }}</td>
                    <td>${{ number_format($nomina->descuentos, 2) }}</td>
                    <td><span class="score-badge">${{ number_format($nomina->total_pagar, 2) }}</span></td>
                    <td><span class="status-pill">{{ $nomina->estado }}</span></td>
                    <td class="actions-cell">
                        <details class="action-drawer">
                            <summary>Cambiar estado</summary>
                            <form class="action-form" action="{{ route('nomina.estado', $nomina) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label>Estado
                                    <select name="estado" required>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado }}" @selected($nomina->estado === $estado)>{{ $estado }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <button class="btn-secondary" type="submit">Actualizar estado</button>
                            </form>
                        </details>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">Sin nominas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $nominas->links() }}
</div>

<script>
    window.nominaMensualLabels = @json($monthlyLabels);
    window.nominaMensualData = @json($monthlyTotals);
    window.nominaEstadoLabels = @json($statusLabels);
    window.nominaEstadoData = @json($statusTotals);
</script>
@endsection
