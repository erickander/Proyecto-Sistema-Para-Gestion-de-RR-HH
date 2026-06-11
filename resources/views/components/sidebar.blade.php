@php($rol = auth()->user()?->role?->nombre_rol)

<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">RH</div>
        <div>
            <h2>Gestion RR-HH</h2>
            <span>ERP Empresarial</span>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Menu principal">
        <p class="nav-section">Principal</p>

        <a class="nav-item {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <span class="nav-icon">D</span>
            <span>Dashboard</span>
        </a>

        @if($rol === 'ADMINISTRADOR')
            <p class="nav-section">Sistema</p>

            <a class="nav-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                <span class="nav-icon">U</span>
                <span>Usuarios</span>
            </a>

            <a class="nav-item {{ request()->routeIs('sistema.*') ? 'active' : '' }}" href="{{ route('sistema.index') }}">
                <span class="nav-icon">S</span>
                <span>Opciones</span>
            </a>

            <a class="nav-item {{ request()->routeIs('auditoria.*') ? 'active' : '' }}" href="{{ route('auditoria.index') }}">
                <span class="nav-icon">A</span>
                <span>Auditoria</span>
            </a>
        @endif

        @if(in_array($rol, ['ADMINISTRADOR', 'RRHH'], true))
            <p class="nav-section">Gestion RRHH</p>

            <a class="nav-item {{ request()->routeIs('empleados.*') ? 'active' : '' }}" href="{{ route('empleados.index') }}">
                <span class="nav-icon">E</span>
                <span>Empleados</span>
            </a>

            <a class="nav-item {{ request()->routeIs('reclutamiento.*') ? 'active' : '' }}" href="{{ route('reclutamiento.index') }}">
                <span class="nav-icon">R</span>
                <span>Reclutamiento</span>
            </a>

            <a class="nav-item {{ request()->routeIs('nomina.*') ? 'active' : '' }}" href="{{ route('nomina.index') }}">
                <span class="nav-icon">N</span>
                <span>Nomina</span>
            </a>

            <a class="nav-item {{ request()->routeIs('vacaciones.*') ? 'active' : '' }}" href="{{ route('vacaciones.index') }}">
                <span class="nav-icon">V</span>
                <span>Vacaciones</span>
            </a>

            <a class="nav-item {{ request()->routeIs('notificaciones.*') ? 'active' : '' }}" href="{{ route('notificaciones.index') }}">
                <span class="nav-icon">M</span>
                <span>Notificaciones</span>
            </a>

            <p class="nav-section">Analitica</p>

            <a class="nav-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}">
                <span class="nav-icon">P</span>
                <span>Reportes</span>
            </a>

            <a class="nav-item {{ request()->routeIs('ia.*') ? 'active' : '' }}" href="{{ route('ia.index') }}">
                <span class="nav-icon">IA</span>
                <span>Inteligencia IA</span>
            </a>
        @endif

        @if($rol === 'EMPLEADO')
            <p class="nav-section">Autoservicio</p>

            <a class="nav-item {{ request()->routeIs('vacaciones.*') ? 'active' : '' }}" href="{{ route('vacaciones.index') }}">
                <span class="nav-icon">V</span>
                <span>Mis vacaciones</span>
            </a>

            <a class="nav-item {{ request()->routeIs('notificaciones.*') ? 'active' : '' }}" href="{{ route('notificaciones.index') }}">
                <span class="nav-icon">M</span>
                <span>Mis notificaciones</span>
            </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="role-card">
            <span>{{ $rol }}</span>
            <strong>{{ auth()->user()?->nombre_usuario }}</strong>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-button">Cerrar sesion</button>
        </form>
    </div>
</aside>
