@extends('layouts.app')

@section('content')

<div class="dashboard-header">
    <h1>Dashboard RRHH</h1>
    <p>Panel general del sistema de recursos humanos</p>
</div>

<div class="cards-container">

    <div class="card">
        <h3>Empleados</h3>
        <h2>{{ $empleados }}</h2>
    </div>

    <div class="card">
        <h3>Vacantes</h3>
        <h2>{{ $vacantes }}</h2>
    </div>

    <div class="card">
        <h3>Solicitudes</h3>
        <h2>{{ $solicitudes }}</h2>
    </div>
 
    <div class="card">
        <h3>Postulaciones</h3>
        <h2>{{ $postulaciones }}</h2>
    </div>

</div> 

<div class="charts-container">

    <div class="chart-card">
        <h3>Empleados por Departamento</h3>
        <canvas id="departamentosChart"></canvas>
    </div>

    <div class="chart-card">
        <h3>Contrataciones Mensuales</h3>
        <canvas id="contratacionesChart"></canvas>
    </div>

</div>

<div class="table-container">

    <h3>Ultimos empleados registrados</h3>

    <table>

        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Departamento</th>
            </tr>
        </thead>

       <tbody>

@foreach($ultimosEmpleados as $empleado)

<tr>
    <td>{{ $empleado->id_empleado }}</td>

    <td>
        {{ $empleado->nombres }}
        {{ $empleado->apellidos }}
    </td>

    <td>{{ $empleado->cargo }}</td>

    <td>
        {{ $empleado->departamento?->nombre_departamento ?? 'Sin departamento' }}
    </td>
</tr>

@endforeach

</tbody>

    </table>
    <script>

    window.departamentosLabels = @json(
        $departamentos->pluck('nombre_departamento')
    );

    window.departamentosData = @json(
        $departamentos->pluck('total')
    );

    window.contratacionesLabels = @json(
        $contrataciones->pluck('mes')
    );

    window.contratacionesData = @json(
        $contrataciones->pluck('total')
    );

</script>

</div>

@endsection
