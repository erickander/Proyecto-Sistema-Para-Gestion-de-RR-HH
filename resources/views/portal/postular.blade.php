@extends('layouts.portal')

@section('content')
<section class="portal-section top">
    <div class="apply-layout">
        <div class="job-detail">
            <span>{{ $vacante->estado }}</span>
            <h1>{{ $vacante->titulo }}</h1>
            <p>{{ $vacante->descripcion }}</p>
            <h3>Requisitos</h3>
            <p>{{ $vacante->requisitos ?? 'No especificados.' }}</p>
            <strong>{{ $vacante->salario_ofrecido ? '$'.number_format($vacante->salario_ofrecido, 2) : 'Salario a convenir' }}</strong>
        </div>

        <form class="apply-form" action="{{ route('portal.postular', $vacante) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h2>Postularse</h2>
            <label>Nombres<input name="nombres" required></label>
            <label>Apellidos<input name="apellidos" required></label>
            <label>Cedula<input name="cedula"></label>
            <label>Correo<input type="email" name="correo" required></label>
            <label>Telefono<input name="telefono"></label>
            <label>Direccion<input name="direccion"></label>
            <label>CV PDF<input type="file" name="cv" accept="application/pdf" required></label>
            <label class="consent-check">
                <input type="checkbox" name="consentimiento_ia" value="1" required>
                Autorizo que mi CV sea analizado con IA para evaluar mi postulacion.
            </label>
            <button type="submit">Enviar postulacion</button>
        </form>
    </div>
</section>
@endsection
