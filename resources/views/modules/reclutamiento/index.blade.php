@extends('layouts.app')

@section('content')
<div class="dashboard-header split-header">
    <div>
        <h1>Reclutamiento y Vacantes</h1>
        <p>Publica vacantes, configura tests de opcion multiple y revisa postulaciones recientes.</p>
    </div>
    <button class="btn-primary js-open-modal" type="button" data-modal-target="modal-create-vacante">Nueva vacante</button>
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
    <div class="card"><h3>Vacantes creadas</h3><h2>{{ $vacantes->total() }}</h2></div>
    <div class="card"><h3>Postulaciones recientes</h3><h2>{{ $postulaciones->count() }}</h2></div>
</div>

<div class="analysis-modal app-modal" id="modal-create-vacante" hidden>
    <div class="analysis-modal__backdrop" data-modal-close></div>
    <section class="analysis-modal__panel" role="dialog" aria-modal="true" aria-labelledby="modal-create-vacante-title">
        <header class="analysis-modal__header">
            <div>
                <span>Vacantes</span>
                <h2 id="modal-create-vacante-title">Publicar nueva vacante</h2>
                <p>La vacante se mostrara en el portal publico cuando este abierta.</p>
            </div>
            <button class="modal-close" type="button" aria-label="Cerrar" data-modal-close>&times;</button>
        </header>
        <div class="analysis-modal__body">
            <form class="modal-form" action="{{ route('reclutamiento.vacantes.store') }}" method="POST">
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
                <label class="span-2">Descripcion<textarea name="descripcion" required></textarea></label>
                <label class="span-2">Requisitos<textarea name="requisitos"></textarea></label>
                <div class="modal-actions">
                    <button class="btn-light" type="button" data-modal-close>Cancelar</button>
                    <button class="btn-primary" type="submit">Publicar vacante</button>
                </div>
            </form>
        </div>
    </section>
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
                <th>Test</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vacantes as $vacante)
                @php($test = $vacante->testActivo)
                <tr>
                    <td>
                        <strong>{{ $vacante->titulo }}</strong><br>
                        <span class="muted-text">{{ $vacante->tipo_contrato ?: 'Contrato no definido' }}</span>
                    </td>
                    <td>{{ $vacante->departamento?->nombre_departamento ?? 'General' }}</td>
                    <td><span class="status-pill">{{ $vacante->estado }}</span></td>
                    <td>{{ $vacante->postulaciones_count }}</td>
                    <td>
                        @if($test)
                            <div class="test-summary">
                                <strong>{{ $test->titulo }}</strong>
                                <span>{{ $test->preguntas->count() }} preguntas</span>
                            </div>
                        @else
                            <span class="status-pill">Sin test</span>
                        @endif
                    </td>
                    <td class="actions-cell compact-actions">
                        <button class="btn-light js-open-modal" type="button" data-modal-target="modal-edit-vacante-{{ $vacante->id_vacante }}">Editar</button>
                        <button class="btn-light js-open-modal" type="button" data-modal-target="modal-test-{{ $vacante->id_vacante }}">{{ $test ? 'Test' : 'Crear test' }}</button>
                    </td>
                </tr>

                <tr class="table-modal-row">
                    <td colspan="6">
                <div class="analysis-modal app-modal" id="modal-edit-vacante-{{ $vacante->id_vacante }}" hidden>
                    <div class="analysis-modal__backdrop" data-modal-close></div>
                    <section class="analysis-modal__panel" role="dialog" aria-modal="true" aria-labelledby="modal-edit-vacante-title-{{ $vacante->id_vacante }}">
                        <header class="analysis-modal__header">
                            <div>
                                <span>Vacantes</span>
                                <h2 id="modal-edit-vacante-title-{{ $vacante->id_vacante }}">Editar vacante</h2>
                                <p>{{ $vacante->titulo }}</p>
                            </div>
                            <button class="modal-close" type="button" aria-label="Cerrar" data-modal-close>&times;</button>
                        </header>
                        <div class="analysis-modal__body">
                            <form class="modal-form" action="{{ route('reclutamiento.vacantes.update', $vacante) }}" method="POST">
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
                                <label class="span-2">Descripcion<textarea name="descripcion" required>{{ $vacante->descripcion }}</textarea></label>
                                <label class="span-2">Requisitos<textarea name="requisitos">{{ $vacante->requisitos }}</textarea></label>
                                <div class="modal-actions">
                                    <button class="btn-light" type="button" data-modal-close>Cancelar</button>
                                    <button class="btn-secondary" type="submit">Actualizar vacante</button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="analysis-modal app-modal" id="modal-test-{{ $vacante->id_vacante }}" hidden>
                    <div class="analysis-modal__backdrop" data-modal-close></div>
                    <section class="analysis-modal__panel modal-panel-wide" role="dialog" aria-modal="true" aria-labelledby="modal-test-title-{{ $vacante->id_vacante }}">
                        <header class="analysis-modal__header">
                            <div>
                                <span>Test por vacante</span>
                                <h2 id="modal-test-title-{{ $vacante->id_vacante }}">{{ $test ? 'Gestionar test' : 'Crear test' }}</h2>
                                <p>{{ $vacante->titulo }}</p>
                            </div>
                            <button class="modal-close" type="button" aria-label="Cerrar" data-modal-close>&times;</button>
                        </header>
                        <div class="analysis-modal__body">
                            @if($test)
                                <form class="modal-form" action="{{ route('reclutamiento.tests.update', $test) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <label>Titulo<input name="titulo" value="{{ $test->titulo }}" required></label>
                                    <label>Estado
                                        <select name="estado">
                                            <option value="ACTIVO" @selected($test->estado === 'ACTIVO')>ACTIVO</option>
                                            <option value="INACTIVO" @selected($test->estado === 'INACTIVO')>INACTIVO</option>
                                        </select>
                                    </label>
                                    <label class="span-2">Descripcion<textarea name="descripcion">{{ $test->descripcion }}</textarea></label>
                                    <div class="modal-actions">
                                        <button class="btn-secondary" type="submit">Actualizar test</button>
                                    </div>
                                </form>

                                <div class="forms-builder">
                                    <div class="builder-heading">
                                        <div>
                                            <h3>Preguntas de opcion multiple</h3>
                                            <p>Cada pregunta debe tener al menos dos opciones.</p>
                                        </div>
                                    </div>

                                    <form class="forms-question-builder multi-question-builder" action="{{ route('reclutamiento.preguntas.store', $test) }}" method="POST" data-question-builder>
                                        @csrf
                                        <div class="question-builder-list" data-question-list data-base-order="{{ $test->preguntas->count() + 1 }}">
                                            <section class="question-builder-card" data-question-card data-question-index="0">
                                                <div class="question-builder-card__header">
                                                    <strong>Pregunta 1</strong>
                                                    <button class="btn-light" type="button" data-remove-question hidden>Eliminar</button>
                                                </div>
                                                <label>Pregunta<textarea name="preguntas[0][pregunta]" required placeholder="Escribe la pregunta para el postulante"></textarea></label>
                                                <div class="builder-row">
                                                    <label>Puntaje maximo<input type="number" step="0.01" name="preguntas[0][puntaje_maximo]" value="100" required></label>
                                                    <label>Orden<input type="number" name="preguntas[0][orden]" value="{{ $test->preguntas->count() + 1 }}" min="1" required></label>
                                                </div>
                                                <div class="option-builder" data-option-builder data-option-prefix="preguntas[0][opciones]">
                                                    <span>Opciones de respuesta</span>
                                                    <div data-options-list>
                                                        <label class="option-row">
                                                            <input type="radio" name="preguntas[0][respuesta_correcta]" value="0" required>
                                                            <input name="preguntas[0][opciones][]" required placeholder="Opcion 1">
                                                            <span>Correcta</span>
                                                        </label>
                                                        <label class="option-row">
                                                            <input type="radio" name="preguntas[0][respuesta_correcta]" value="1" required>
                                                            <input name="preguntas[0][opciones][]" required placeholder="Opcion 2">
                                                            <span>Correcta</span>
                                                        </label>
                                                    </div>
                                                    <button class="btn-light" type="button" data-add-option>Agregar opcion</button>
                                                </div>
                                            </section>
                                        </div>
                                        <div class="builder-actions">
                                            <button class="btn-light" type="button" data-add-question>Agregar otra pregunta</button>
                                            <button class="btn-primary" type="submit">Guardar preguntas</button>
                                        </div>

                                        <template data-question-template>
                                            <section class="question-builder-card" data-question-card>
                                                <div class="question-builder-card__header">
                                                    <strong>Pregunta __NUMBER__</strong>
                                                    <button class="btn-light" type="button" data-remove-question>Eliminar</button>
                                                </div>
                                                <label>Pregunta<textarea name="preguntas[__INDEX__][pregunta]" required placeholder="Escribe la pregunta para el postulante"></textarea></label>
                                                <div class="builder-row">
                                                    <label>Puntaje maximo<input type="number" step="0.01" name="preguntas[__INDEX__][puntaje_maximo]" value="100" required></label>
                                                    <label>Orden<input type="number" name="preguntas[__INDEX__][orden]" value="__ORDER__" min="1" required></label>
                                                </div>
                                                <div class="option-builder" data-option-builder data-option-prefix="preguntas[__INDEX__][opciones]">
                                                    <span>Opciones de respuesta</span>
                                                    <div data-options-list>
                                                        <label class="option-row">
                                                            <input type="radio" name="preguntas[__INDEX__][respuesta_correcta]" value="0" required>
                                                            <input name="preguntas[__INDEX__][opciones][]" required placeholder="Opcion 1">
                                                            <span>Correcta</span>
                                                        </label>
                                                        <label class="option-row">
                                                            <input type="radio" name="preguntas[__INDEX__][respuesta_correcta]" value="1" required>
                                                            <input name="preguntas[__INDEX__][opciones][]" required placeholder="Opcion 2">
                                                            <span>Correcta</span>
                                                        </label>
                                                    </div>
                                                    <button class="btn-light" type="button" data-add-option>Agregar opcion</button>
                                                </div>
                                            </section>
                                        </template>
                                    </form>

                                    @if($test->preguntas->isNotEmpty())
                                        <div class="question-list">
                                            @foreach($test->preguntas as $pregunta)
                                                <article class="question-item-card">
                                                    <div>
                                                        <strong>{{ $pregunta->orden }}. {{ $pregunta->pregunta }}</strong>
                                                        <span>Correcta: {{ $pregunta->respuesta_correcta ?: 'Sin definir' }}</span>
                                                        <span>{{ count($pregunta->opciones ?? []) }} opciones · {{ number_format($pregunta->puntaje_maximo, 2) }} pts</span>
                                                    </div>
                                                    <div class="inline-actions">
                                                        <button class="btn-light js-open-modal" type="button" data-modal-target="modal-question-{{ $pregunta->id_pregunta }}">Editar</button>
                                                        <form class="action-mini" action="{{ route('reclutamiento.preguntas.destroy', $pregunta) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn-danger" type="submit">Eliminar</button>
                                                        </form>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                <form class="modal-form" action="{{ route('reclutamiento.tests.store', $vacante) }}" method="POST">
                                    @csrf
                                    <label>Titulo<input name="titulo" value="Test para {{ $vacante->titulo }}" required></label>
                                    <label>Estado
                                        <select name="estado">
                                            <option value="ACTIVO">ACTIVO</option>
                                            <option value="INACTIVO">INACTIVO</option>
                                        </select>
                                    </label>
                                    <label class="span-2">Descripcion<textarea name="descripcion" placeholder="Objetivo del test y criterios de evaluacion"></textarea></label>
                                    <div class="modal-actions">
                                        <button class="btn-light" type="button" data-modal-close>Cancelar</button>
                                        <button class="btn-primary" type="submit">Crear test</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </section>
                </div>

                @if($test)
                    @foreach($test->preguntas as $pregunta)
                        <div class="analysis-modal app-modal" id="modal-question-{{ $pregunta->id_pregunta }}" hidden>
                            <div class="analysis-modal__backdrop" data-modal-close></div>
                            <section class="analysis-modal__panel" role="dialog" aria-modal="true" aria-labelledby="modal-question-title-{{ $pregunta->id_pregunta }}">
                                <header class="analysis-modal__header">
                                    <div>
                                        <span>Pregunta</span>
                                        <h2 id="modal-question-title-{{ $pregunta->id_pregunta }}">Editar pregunta</h2>
                                        <p>{{ $test->titulo }}</p>
                                    </div>
                                    <button class="modal-close" type="button" aria-label="Cerrar" data-modal-close>&times;</button>
                                </header>
                                <div class="analysis-modal__body">
                                    <form class="forms-question-builder" action="{{ route('reclutamiento.preguntas.update', $pregunta) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <label>Pregunta<textarea name="pregunta" required>{{ $pregunta->pregunta }}</textarea></label>
                                        <div class="builder-row">
                                            <label>Puntaje maximo<input type="number" step="0.01" name="puntaje_maximo" value="{{ $pregunta->puntaje_maximo }}" required></label>
                                            <label>Orden<input type="number" name="orden" value="{{ $pregunta->orden }}" min="1" required></label>
                                        </div>
                                        <div class="option-builder" data-option-builder>
                                            <span>Opciones de respuesta</span>
                                            <div data-options-list>
                                                @foreach($pregunta->opciones ?? [] as $opcion)
                                                    <label class="option-row">
                                                        <input type="radio" name="respuesta_correcta" value="{{ $loop->index }}" @checked($pregunta->respuesta_correcta === $opcion) required>
                                                        <input name="opciones[]" value="{{ $opcion }}" required>
                                                        <span>Correcta</span>
                                                    </label>
                                                @endforeach
                                                @if(count($pregunta->opciones ?? []) < 2)
                                                    <label class="option-row">
                                                        <input type="radio" name="respuesta_correcta" value="0" required>
                                                        <input name="opciones[]" required placeholder="Opcion 1">
                                                        <span>Correcta</span>
                                                    </label>
                                                    <label class="option-row">
                                                        <input type="radio" name="respuesta_correcta" value="1" required>
                                                        <input name="opciones[]" required placeholder="Opcion 2">
                                                        <span>Correcta</span>
                                                    </label>
                                                @endif
                                            </div>
                                            <button class="btn-light" type="button" data-add-option>Agregar opcion</button>
                                        </div>
                                        <div class="modal-actions">
                                            <button class="btn-light" type="button" data-modal-close>Cancelar</button>
                                            <button class="btn-secondary" type="submit">Actualizar pregunta</button>
                                        </div>
                                    </form>
                                </div>
                            </section>
                        </div>
                    @endforeach
                @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Sin vacantes registradas.</td></tr>
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
                <th>Test</th>
                <th>Puntaje IA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($postulaciones as $postulacion)
                <tr>
                    <td>{{ $postulacion->candidato?->nombres }} {{ $postulacion->candidato?->apellidos }}</td>
                    <td>{{ $postulacion->vacante?->titulo }}</td>
                    <td>{{ $postulacion->estado }}</td>
                    <td>{{ $postulacion->fecha_test ? 'Completado' : 'Pendiente' }}</td>
                    <td>{{ $postulacion->analisisIa?->puntuacion_general ?? 'Pendiente' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin postulaciones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
