@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Opciones del Sistema</h1>
    <p>Catalogos y parametros base usados por el ERP.</p>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif

<div class="cards-container">
    <div class="card"><h3>Roles</h3><h2>{{ $roles->count() }}</h2></div>
    <div class="card"><h3>Departamentos</h3><h2>{{ $departamentos->count() }}</h2></div>
    <div class="card"><h3>Respaldos</h3><h2>SQL</h2></div>
</div>

<div class="quick-actions">
    <form class="action-card backup-card" action="{{ route('sistema.respaldos.store') }}" method="POST">
        @csrf
        <strong>Generar respaldo</strong>
        <span>Descarga un archivo SQL con las tablas principales del sistema.</span>
        <button class="btn-primary" type="submit">Descargar respaldo</button>
    </form>
</div>

<div class="form-panel">
    <h3>Crear departamento</h3>
    <form class="form-grid" action="{{ route('sistema.departamentos.store') }}" method="POST">
        @csrf
        <label>Nombre<input name="nombre_departamento" required></label>
        <label>Descripcion<input name="descripcion"></label>
        <button class="btn-primary" type="submit">Guardar</button>
    </form>
</div>

<div class="table-container">
    <h3>Departamentos</h3>
    <table>
        <thead><tr><th>Nombre</th><th>Descripcion</th></tr></thead>
        <tbody>
            @foreach($departamentos as $departamento)
                <tr>
                    <td>{{ $departamento->nombre_departamento }}</td>
                    <td>{{ $departamento->descripcion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
