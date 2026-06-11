@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Reportes y Analitica</h1>
    <p>Indicadores listos para exportacion PDF, Excel y graficas avanzadas.</p>
</div>

<div class="cards-container">
    <div class="card"><h3>Empleados</h3><h2>{{ $totalEmpleados }}</h2></div>
    <div class="card"><h3>Total nomina</h3><h2>{{ number_format($totalNomina, 2) }}</h2></div>
    <div class="card"><h3>Postulaciones</h3><h2>{{ $postulaciones }}</h2></div>
</div>
@endsection
