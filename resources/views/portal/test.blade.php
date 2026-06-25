@extends('layouts.portal')

@section('content')
<section class="portal-section top">
    <div class="test-page">
        <div class="test-hero">
            <span>Test de postulacion</span>
            <h1>{{ $test->titulo }}</h1>
            <p>{{ $test->descripcion ?: 'Responde cada pregunta segun tu criterio profesional. Al enviar el test se ejecutara el analisis IA con tu CV y tus respuestas.' }}</p>
            <strong>{{ $postulacion->vacante?->titulo }}</strong>
        </div>

        <form class="forms-style" action="{{ route('portal.test.submit', ['postulacion' => $postulacion, 'token' => $postulacion->token_test]) }}" method="POST">
            @csrf
            @if($errors->any())
                <div class="form-errors">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @foreach($test->preguntas as $pregunta)
                <article class="forms-question">
                    <h3>{{ $pregunta->orden }}. {{ $pregunta->pregunta }}</h3>
                    <div class="option-list">
                        @foreach($pregunta->opciones ?? [] as $opcion)
                            <label>
                                <input type="radio" name="respuestas[{{ $pregunta->id_pregunta }}]" value="{{ $opcion }}" required>
                                <span>{{ $opcion }}</span>
                            </label>
                        @endforeach
                    </div>
                </article>
            @endforeach

            <button type="submit">Enviar test y analizar postulacion</button>
        </form>
    </div>
</section>
@endsection
