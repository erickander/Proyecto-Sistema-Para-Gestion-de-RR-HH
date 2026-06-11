<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contrasena</title>
    @vite('resources/css/login.css')
</head>
<body>
<div class="container">
    <div class="login-card">
        <div class="logo-area">
            <h1>Recuperar contrasena</h1>
            <p>Ingrese su correo empresarial.</p>
        </div>

        @if(session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="input-group">
                <label>Correo</label>
                <input type="email" name="correo" placeholder="correo@empresa.com" required>
            </div>
            <button type="submit">Generar token</button>
        </form>
    </div>
</div>
</body>
</html>
