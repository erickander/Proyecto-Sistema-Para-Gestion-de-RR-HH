<header class="navbar">
    <div class="page-title">
        <span>{{ now()->format('d/m/Y') }}</span>
        <strong>Sistema de Recursos Humanos</strong>
    </div>

    <div class="navbar-actions">
        <label class="search-box">
            <span>Buscar</span>
            <input type="text" placeholder="Empleado, modulo, reporte...">
        </label>

        @if(auth()->user()?->role?->nombre_rol !== 'ADMINISTRADOR')
            <a class="notification-button" href="{{ route('notificaciones.index') }}" aria-label="Ver notificaciones">
                <span>Notif.</span>
                <strong>{{ auth()->user()?->notificaciones()->where('leida', false)->count() ?? 0 }}</strong>
            </a>
        @else
            <a class="notification-button" href="{{ route('auditoria.index') }}" aria-label="Ver auditoria">
                <span>Auditoria</span>
                <strong>A</strong>
            </a>
        @endif

        <div class="user-menu">
            <div class="avatar">{{ strtoupper(substr(auth()->user()?->nombre_usuario ?? 'U', 0, 1)) }}</div>
            <div>
                <strong>{{ auth()->user()?->nombre_usuario }}</strong>
                <span>{{ auth()->user()?->role?->nombre_rol }}</span>
            </div>
        </div>
    </div>
</header>
