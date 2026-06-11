@extends('layouts.portal')

@section('content')
<section class="portal-section top">
    <div class="section-heading">
        <h1>Vacantes Disponibles</h1>
        <p>Selecciona una vacante y completa tu postulacion.</p>
    </div>

    <div class="vacancy-grid">
        @forelse($vacantes as $vacante)
            <article class="vacancy-card">
                <span>{{ $vacante->estado }}</span>
                <h3>{{ $vacante->titulo }}</h3>
                <p>{{ \Illuminate\Support\Str::limit($vacante->descripcion, 150) }}</p>
                <strong>{{ $vacante->salario_ofrecido ? '$'.number_format($vacante->salario_ofrecido, 2) : 'Salario a convenir' }}</strong>
                <a href="{{ route('portal.vacantes.show', $vacante) }}">Completar formulario</a>
            </article>
        @empty
            <p>No hay vacantes abiertas actualmente.</p>
        @endforelse
    </div>
</section>
@endsection
