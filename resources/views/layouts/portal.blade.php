<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Empleo RRHH</title>
    @vite('resources/css/portal.css')
</head>
<body>
    <header class="public-nav">
        <a class="public-brand" href="{{ route('portal.index') }}">Gestion RR-HH</a>
        <nav>
            <a href="{{ route('portal.index') }}">Inicio</a>
            <a href="{{ route('portal.vacantes') }}">Vacantes</a>
            <a href="{{ route('portal.index') }}#empresa">Empresa</a>
            <a href="{{ route('portal.index') }}#contacto">Contacto</a>
            <a class="login-cta" href="{{ route('login') }}">Login</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
