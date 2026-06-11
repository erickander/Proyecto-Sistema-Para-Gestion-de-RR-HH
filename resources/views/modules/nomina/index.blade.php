@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Nomina</h1>
    <p>Pagos, IESS, descuentos, bonificaciones e historial salarial.</p>
</div>

<div class="table-container">
    <h3>Historial de pagos</h3>
    <table>
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Salario base</th>
                <th>Horas extra</th>
                <th>Neto</th>
                <th>Generado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($nominas as $nomina)
                <tr>
                    <td>{{ $nomina->empleado?->nombres }} {{ $nomina->empleado?->apellidos }}</td>
                    <td>{{ $nomina->salario_base }}</td>
                    <td>{{ $nomina->horas_extra }}</td>
                    <td>{{ $nomina->total_pagar }}</td>
                    <td>{{ $nomina->fecha_generacion?->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin nominas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
