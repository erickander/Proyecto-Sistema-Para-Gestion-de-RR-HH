@extends('layouts.portal')

@section('content')
<section class="portal-hero">
    <div>
        <span>Portal publico de empleo</span>
        <h1>Encuentra tu siguiente oportunidad profesional</h1>
        <p>Postula a vacantes abiertas y permite que nuestro equipo de RRHH revise tu perfil con apoyo de IA.</p>
        <a href="{{ route('portal.vacantes') }}">Ver vacantes disponibles</a>
    </div>
</section>

<section class="portal-section">
    <div class="section-heading">
        <h2>Vacantes destacadas</h2>
        <p>Posiciones abiertas para nuevos talentos.</p>
    </div>
    <div class="vacancy-grid">
        @forelse($vacantes as $vacante)
            <article class="vacancy-card">
                <span>{{ $vacante->departamento?->nombre_departamento ?? 'General' }}</span>
                <h3>{{ $vacante->titulo }}</h3>
                <p>{{ \Illuminate\Support\Str::limit($vacante->descripcion, 130) }}</p>
                <strong>{{ $vacante->salario_ofrecido ? '$'.number_format($vacante->salario_ofrecido, 2) : 'Salario a convenir' }}</strong>
                <a href="{{ route('portal.vacantes.show', $vacante) }}">Postular</a>
            </article>
        @empty
            <p>No hay vacantes disponibles por el momento.</p>
        @endforelse
    </div>
</section>

<section id="empresa" class="portal-band">
    <h2>Informacion Empresa</h2>
    <p>Somos una organizacion enfocada en talento, crecimiento profesional y procesos de seleccion transparentes.</p>
</section>

<section id="contacto" class="portal-section">
    <div class="section-heading">
        <h2>Contacto</h2>
        <p>Para consultas sobre postulaciones, escribe a talento@empresa.local.</p>
    </div>
</section>
@endsection
