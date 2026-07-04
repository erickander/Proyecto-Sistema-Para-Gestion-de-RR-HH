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
            @if($errors->any())
                <div class="form-errors">
                    <strong>Revise los campos marcados para continuar.</strong>
                </div>
            @endif
            <label>
                Nombres
                <input class="@error('nombres') is-invalid @enderror" name="nombres" value="{{ old('nombres') }}" required aria-invalid="{{ $errors->has('nombres') ? 'true' : 'false' }}">
                @error('nombres') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                Apellidos
                <input class="@error('apellidos') is-invalid @enderror" name="apellidos" value="{{ old('apellidos') }}" required aria-invalid="{{ $errors->has('apellidos') ? 'true' : 'false' }}">
                @error('apellidos') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                Cedula
                <input class="@error('cedula') is-invalid @enderror" name="cedula" value="{{ old('cedula') }}" aria-invalid="{{ $errors->has('cedula') ? 'true' : 'false' }}">
                @error('cedula') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                Correo
                <input class="@error('correo') is-invalid @enderror" type="email" name="correo" value="{{ old('correo') }}" required aria-invalid="{{ $errors->has('correo') ? 'true' : 'false' }}">
                @error('correo') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                Telefono
                <input class="@error('telefono') is-invalid @enderror" name="telefono" value="{{ old('telefono') }}" aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}">
                @error('telefono') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                Direccion
                <input class="@error('direccion') is-invalid @enderror" name="direccion" value="{{ old('direccion') }}" aria-invalid="{{ $errors->has('direccion') ? 'true' : 'false' }}">
                @error('direccion') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <label>
                CV PDF
                <input class="@error('cv') is-invalid @enderror" type="file" name="cv" accept="application/pdf" required aria-invalid="{{ $errors->has('cv') ? 'true' : 'false' }}">
                @error('cv') <small class="field-error">{{ $message }}</small> @enderror
            </label>
            <div>
                <label class="consent-check">
                    <input type="checkbox" name="consentimiento_ia" value="1" {{ old('consentimiento_ia') ? 'checked' : '' }} required>
                    Autorizo que mi CV y el test de la vacante sean analizados con IA para evaluar mi postulacion.
                </label>
                @error('consentimiento_ia') <small class="field-error">{{ $message }}</small> @enderror
            </div>
            <button type="submit">Enviar postulacion y continuar al test</button>
        </form>
    </div>
</section>
@endsection
