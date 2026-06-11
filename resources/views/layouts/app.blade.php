<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema RRHH</title>

    @vite([
        'resources/css/dashboard.css',
        'resources/js/dashboard.js'
    ])

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>

<div class="main-container">

    {{-- SIDEBAR --}}
    @include('components.sidebar')

    <div class="content-area">

        {{-- NAVBAR --}}
        @include('components.navbar')

        <main class="main-content">
            @yield('content')
        </main>

    </div>

</div>

</body>
</html>