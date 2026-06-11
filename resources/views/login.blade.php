<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login RRHH</title>
    @vite('resources/css/login.css')
</head>
<body>
<div class="container">
    <div class="login-card">
        <div class="logo-area">
            <h1>Gestion RR-HH</h1>
            <p>Sistema de Gestion Empresarial</p>
        </div>

        @if(session('error'))
            <div class="alert">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST">
            @csrf

            <div class="input-group">
                <label>Usuario</label>
                <input type="text" name="usuario" value="{{ old('usuario') }}" placeholder="Nombre de usuario" required>
            </div>

            <div class="input-group">
                <label>Contrasena</label>
                <input type="password" name="password" placeholder="Ingrese su contrasena" required>
            </div>

            <button type="submit">Iniciar Sesion</button>

            <p class="login-link">
                <a href="{{ route('password.request') }}">Recuperar contrasena</a>
            </p>
        </form>
    </div>
</div>
</body>
</html>
