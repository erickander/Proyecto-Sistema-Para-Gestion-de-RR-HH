@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>Reclutamiento y Vacantes</h1>
    <p>Publica vacantes en el portal publico y revisa postulaciones recientes.</p>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif

<div class="cards-container">
    <div class="card"><h3>Vacantes creadas</h3><h2>{{ $vacantes->total() }}</h2></div>
    <div class="card"><h3>Postulaciones recientes</h3><h2>{{ $postulaciones->count() }}</h2></div>
</div>

<div class="form-panel">
    <h3>Publicar nueva vacante</h3>
    <form class="form-grid" action="{{ route('reclutamiento.vacantes.store') }}" method="POST">
        @csrf
        <label>Titulo<input name="titulo" required></label>
        <label>Departamento
            <select name="id_departamento">
                <option value="">General</option>
                @foreach($departamentos as $departamento)
                    <option value="{{ $departamento->id_departamento }}">{{ $departamento->nombre_departamento }}</option>
                @endforeach
            </select>
        </label>
        <label>Contrato<input name="tipo_contrato" placeholder="Tiempo completo"></label>
        <label>Salario<input type="number" step="0.01" name="salario_ofrecido"></label>
        <label>Estado
            <select name="estado">
                <option value="ABIERTA">ABIERTA</option>
                <option value="PAUSADA">PAUSADA</option>
                <option value="CERRADA">CERRADA</option>
            </select>
        </label>
        <label class="span-2">Descripcion<input name="descripcion" required></label>
        <label class="span-2">Requisitos<input name="requisitos"></label>
        <button class="btn-primary" type="submit">Publicar vacante</button>
    </form>
</div>

<div class="table-container">
    <h3>Vacantes creadas anteriormente</h3>
    <table>
        <thead>
            <tr>
                <th>Titulo</th>
                <th>Departamento</th>
                <th>Estado</th>
                <th>Postulaciones</th>
                <th>Editar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vacantes as $vacante)
                <tr>
                    <td>{{ $vacante->titulo }}</td>
                    <td>{{ $vacante->departamento?->nombre_departamento ?? 'General' }}</td>
                    <td><span class="status-pill">{{ $vacante->estado }}</span></td>
                    <td>{{ $vacante->postulaciones_count }}</td>
                    <td class="actions-cell">
                        <details class="action-drawer">
                            <summary>Editar vacante</summary>
                            <form class="action-form wide" action="{{ route('reclutamiento.vacantes.update', $vacante) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <label>Titulo<input name="titulo" value="{{ $vacante->titulo }}" required></label>
                                <label>Contrato<input name="tipo_contrato" value="{{ $vacante->tipo_contrato }}"></label>
                                <label>Salario<input type="number" step="0.01" name="salario_ofrecido" value="{{ $vacante->salario_ofrecido }}"></label>
                                <label>Departamento
                                    <select name="id_departamento">
                                        <option value="">General</option>
                                        @foreach($departamentos as $departamento)
                                            <option value="{{ $departamento->id_departamento }}" @selected($vacante->id_departamento === $departamento->id_departamento)>{{ $departamento->nombre_departamento }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>Estado
                                    <select name="estado">
                                        @foreach(['ABIERTA','PAUSADA','CERRADA'] as $estado)
                                            <option value="{{ $estado }}" @selected($vacante->estado === $estado)>{{ $estado }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>Descripcion<input name="descripcion" value="{{ $vacante->descripcion }}" required></label>
                                <label>Requisitos<input name="requisitos" value="{{ $vacante->requisitos }}"></label>
                                <button class="btn-secondary" type="submit">Actualizar</button>
                            </form>
                        </details>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Sin vacantes registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-container">
    <h3>Ultimas postulaciones</h3>
    <table>
        <thead>
            <tr>
                <th>Candidato</th>
                <th>Vacante</th>
                <th>Estado</th>
                <th>Puntaje IA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($postulaciones as $postulacion)
                <tr>
                    <td>{{ $postulacion->candidato?->nombres }} {{ $postulacion->candidato?->apellidos }}</td>
                    <td>{{ $postulacion->vacante?->titulo }}</td>
                    <td>{{ $postulacion->estado }}</td>
                    <td>{{ $postulacion->analisisIa?->puntuacion_general ?? 'Pendiente' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin postulaciones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
