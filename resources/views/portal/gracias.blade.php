@extends('layouts.portal')

@section('content')
<section class="portal-hero compact">
    <div>
        <span>Postulacion recibida</span>
        <h1>Gracias por postular</h1>
        <p>{{ session('status') ?? 'Tu informacion fue registrada y sera revisada por RRHH.' }}</p>
        <a href="{{ route('portal.vacantes') }}">Ver mas vacantes</a>
    </div>
</section>
@endsection
