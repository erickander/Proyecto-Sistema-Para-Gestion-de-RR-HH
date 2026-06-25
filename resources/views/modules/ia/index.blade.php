@extends('layouts.app')

@section('content')
<div class="dashboard-header">
    <h1>IA y Analisis de Candidatos</h1>
    <p>Ranking de candidatos analizados por Gemini con CV, test por vacante y decision de contratacion.</p>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif

<div class="chart-card">
    <h3>Comparativa de mejores postulantes</h3>
    <canvas id="rankingIaChart"></canvas>
</div>

<div class="table-container">
    <h3>Ranking de candidatos</h3>
    <table>
        <thead>
            <tr>
                <th>Candidato</th>
                <th>Vacante</th>
                <th>Final</th>
                <th>CV</th>
                <th>Test</th>
                <th>Recomendacion</th>
                <th>Analisis</th>
                <th>Decision</th>
            </tr>
        </thead>
        <tbody>
            @forelse($analisis as $item)
                @php($postulacion = $item->postulacion)
                @php($candidato = $postulacion?->candidato)
                @php($modalId = 'analysis-modal-'.$item->id_analisis)
                <tr>
                    <td>
                        <strong>{{ $candidato?->nombres }} {{ $candidato?->apellidos }}</strong><br>
                        {{ $candidato?->correo }}
                    </td>
                    <td>{{ $postulacion?->vacante?->titulo }}</td>
                    <td><span class="score-badge">{{ number_format($item->puntuacion_general, 2) }}</span></td>
                    <td>{{ number_format($item->puntaje_cv ?? 0, 2) }}</td>
                    <td>{{ number_format($item->puntaje_test ?? 0, 2) }}</td>
                    <td><span class="status-pill">{{ $item->recomendacion }}</span></td>
                    <td>
                        <div class="analysis-preview">
                            <span>{{ $item->fecha_analisis ? $item->fecha_analisis->format('d/m/Y H:i') : 'Sin fecha' }}</span>
                            <strong>{{ \Illuminate\Support\Str::limit($item->observaciones ?: 'Analisis disponible para revisar.', 88) }}</strong>
                            <button class="btn-light js-open-modal" type="button" data-modal-target="{{ $modalId }}">
                                Ver analisis
                            </button>
                        </div>
                        <div class="analysis-modal" id="{{ $modalId }}" hidden>
                            <div class="analysis-modal__backdrop" data-modal-close></div>
                            <section class="analysis-modal__panel" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
                                <header class="analysis-modal__header">
                                    <div>
                                        <span>Analisis IA</span>
                                        <h2 id="{{ $modalId }}-title">{{ $candidato?->nombres }} {{ $candidato?->apellidos }}</h2>
                                        <p>{{ $postulacion?->vacante?->titulo }} - Puntaje final {{ number_format($item->puntuacion_general, 2) }}</p>
                                    </div>
                                    <button class="modal-close" type="button" aria-label="Cerrar analisis" data-modal-close>&times;</button>
                                </header>

                                <div class="analysis-modal__body">
                                    <div class="analysis-kpis">
                                        <div>
                                            <span>Puntaje final</span>
                                            <strong>{{ number_format($item->puntuacion_general, 2) }}</strong>
                                        </div>
                                        <div>
                                            <span>Puntaje CV</span>
                                            <strong>{{ number_format($item->puntaje_cv ?? 0, 2) }}</strong>
                                        </div>
                                        <div>
                                            <span>Puntaje test</span>
                                            <strong>{{ number_format($item->puntaje_test ?? 0, 2) }}</strong>
                                        </div>
                                        <div>
                                            <span>Recomendacion</span>
                                            <strong>{{ $item->recomendacion }}</strong>
                                        </div>
                                        <div>
                                            <span>Compatibilidad</span>
                                            <strong>{{ $item->compatibilidad !== null ? number_format($item->compatibilidad, 2) : 'Sin detalle' }}</strong>
                                        </div>
                                    </div>

                                    <div class="analysis-grid">
                                        <article class="analysis-card">
                                            <span>Habilidades</span>
                                            <p>{{ $item->habilidades_detectadas ?: 'No detectadas' }}</p>
                                        </article>
                                        <article class="analysis-card">
                                            <span>Estudios</span>
                                            <p>{{ $item->estudios_detectados ?: 'No detectados' }}</p>
                                        </article>
                                        <article class="analysis-card">
                                            <span>Experiencia</span>
                                            <p>{{ $item->experiencia_detectada ?: 'No detectada' }}</p>
                                        </article>
                                        <article class="analysis-card">
                                            <span>Fortalezas</span>
                                            <p>{{ $item->fortalezas ?: 'Sin fortalezas registradas' }}</p>
                                        </article>
                                        <article class="analysis-card">
                                            <span>Debilidades</span>
                                            <p>{{ $item->debilidades ?: 'Sin debilidades registradas' }}</p>
                                        </article>
                                        <article class="analysis-card analysis-card--wide">
                                            <span>Analisis del test</span>
                                            <p>{{ $item->analisis_test ?: 'Sin analisis de test registrado' }}</p>
                                        </article>
                                        @if($postulacion?->respuestasTest?->isNotEmpty())
                                            <article class="analysis-card analysis-card--wide">
                                                <span>Respuestas del test</span>
                                                <div class="test-answer-list">
                                                    @foreach($postulacion->respuestasTest as $respuesta)
                                                        <div>
                                                            <strong>{{ $respuesta->pregunta?->pregunta }}</strong>
                                                            <p>{{ $respuesta->respuesta }}</p>
                                                            <small>Puntaje IA: {{ $respuesta->puntaje_ia !== null ? number_format($respuesta->puntaje_ia, 2) : 'Pendiente' }} - {{ $respuesta->observacion_ia }}</small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </article>
                                        @endif
                                        <article class="analysis-card analysis-card--wide">
                                            <span>Observaciones IA</span>
                                            <p>{{ $item->observaciones ?: 'Sin observaciones registradas' }}</p>
                                        </article>
                                    </div>

                                    <div class="analysis-modal__footer">
                                        @if($candidato?->cv_url)
                                            <a class="btn-light" href="{{ asset('storage/'.$candidato->cv_url) }}" target="_blank">Ver CV PDF</a>
                                        @endif
                                        <button class="btn-primary" type="button" data-modal-close>Cerrar</button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </td>
                    <td class="actions-cell">
                        <details class="action-drawer">
                            <summary>Reanalizar CV y test</summary>
                            <form class="action-form" action="{{ route('ia.reanalyze', $postulacion) }}" method="POST">
                                @csrf
                                <label class="check-label">
                                    <input type="checkbox" name="consentimiento" value="1" required>
                                    Confirmo que existe autorizacion para enviar este CV y respuestas del test a Gemini.
                                </label>
                                <button class="btn-primary" type="submit">Ejecutar reanalisis</button>
                            </form>
                        </details>
                        @if(in_array($postulacion?->estado, ['APROBADA', 'RECHAZADA'], true))
                            <span class="status-pill">{{ $postulacion->estado }}</span>
                        @else
                            <details class="action-drawer">
                                <summary>Contratar</summary>
                                <form class="action-form" action="{{ route('ia.hire', $postulacion) }}" method="POST">
                                    @csrf
                                    <label>Cargo<input name="cargo" placeholder="Cargo" value="{{ $postulacion?->vacante?->titulo }}" required></label>
                                    <label>Salario<input type="number" step="0.01" name="salario_base" placeholder="Salario" value="{{ $postulacion?->vacante?->salario_ofrecido }}" required></label>
                                    <label>Ingreso<input type="date" name="fecha_ingreso" value="{{ now()->format('Y-m-d') }}" required></label>
                                    <label>Departamento
                                        <select name="id_departamento">
                                            <option value="">Departamento</option>
                                            @foreach($departamentos as $departamento)
                                                <option value="{{ $departamento->id_departamento }}">{{ $departamento->nombre_departamento }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <button class="btn-secondary" type="submit">Confirmar contratacion</button>
                                </form>
                            </details>
                            <form class="action-mini" action="{{ route('ia.reject', $postulacion) }}" method="POST">
                                @csrf
                                <button class="btn-danger" type="submit">Rechazar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">Sin analisis registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    window.rankingIaLabels = @json($rankingLabels);
    window.rankingIaFinalData = @json($rankingFinalData);
    window.rankingIaCvData = @json($rankingCvData);
    window.rankingIaTestData = @json($rankingTestData);
</script>
@endsection
