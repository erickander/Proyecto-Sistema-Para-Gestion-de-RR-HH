@extends('layouts.app')

@section('content')
@php($canManage = ! $isEmployee)

<div class="dashboard-header split-header">
    <div>
        <h1>Notificaciones</h1>
        <p>{{ $canManage ? 'Comunicaciones internas, avisos de nomina y respuestas de vacaciones.' : 'Mensajes enviados por RRHH y el sistema.' }}</p>
    </div>
    <div class="header-actions">
        @if($unreadCount > 0)
            <form action="{{ route('notificaciones.read-all') }}" method="POST">
                @csrf
                @method('PATCH')
                <button class="btn-light" type="submit">Marcar mis leidas</button>
            </form>
        @endif

        @if($canManage)
            <button class="btn-primary js-open-modal" type="button" data-modal-target="modal-enviar-notificacion">Nueva notificacion</button>
        @endif
    </div>
</div>

@if(session('status')) <div class="alert-success">{{ session('status') }}</div> @endif
@if($errors->any())
    <div class="alert-error">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@if($canManage)
    <div class="analysis-modal app-modal" id="modal-enviar-notificacion" hidden>
        <div class="analysis-modal__backdrop" data-modal-close></div>
        <section class="analysis-modal__panel" role="dialog" aria-modal="true" aria-labelledby="modal-enviar-notificacion-title">
            <header class="analysis-modal__header">
                <div>
                    <span>Notificaciones</span>
                    <h2 id="modal-enviar-notificacion-title">Enviar comunicacion</h2>
                    <p>Seleccione destinatarios, tipo y contenido del mensaje.</p>
                </div>
                <button class="modal-close" type="button" aria-label="Cerrar" data-modal-close>&times;</button>
            </header>
            <div class="analysis-modal__body">
                <form class="modal-form" action="{{ route('notificaciones.store') }}" method="POST">
                    @csrf
                    <label>Destino
                        <select name="destino" required>
                            <option value="usuario" @selected(old('destino') === 'usuario')>Usuario especifico</option>
                            <option value="rol" @selected(old('destino') === 'rol')>Rol completo</option>
                            <option value="todos" @selected(old('destino') === 'todos')>Todos los usuarios activos</option>
                        </select>
                    </label>
                    <label>Usuario
                        <select name="id_usuario">
                            <option value="">Seleccione usuario</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id_usuario }}" @selected(old('id_usuario') == $usuario->id_usuario)>
                                    {{ $usuario->nombre_usuario }} - {{ $usuario->role?->nombre_rol }} {{ $usuario->empleado ? '- '.$usuario->empleado->nombres.' '.$usuario->empleado->apellidos : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>Rol
                        <select name="rol_destino">
                            <option value="">Seleccione rol</option>
                            @foreach($rolesDestino as $rol)
                                <option value="{{ $rol }}" @selected(old('rol_destino') === $rol)>{{ $rol }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Tipo
                        <select name="tipo" required>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo }}" @selected(old('tipo', 'RRHH') === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Titulo<input name="titulo" value="{{ old('titulo') }}" maxlength="150" required></label>
                    <label class="span-2">Mensaje<textarea name="mensaje" required>{{ old('mensaje') }}</textarea></label>
                    <div class="modal-actions">
                        <button class="btn-light" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-primary" type="submit">Enviar notificacion</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endif

@if($canManage)
    <div class="form-panel">
        <h3>Filtrar notificaciones</h3>
        <form class="form-grid" method="GET" action="{{ route('notificaciones.index') }}">
            <label>Buscar<input name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Titulo, mensaje, usuario"></label>
            <label>Tipo
                <select name="tipo">
                    <option value="">Todos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo }}" @selected(($filters['tipo'] ?? '') === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </label>
            <label>Estado
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="pendientes" @selected(($filters['estado'] ?? '') === 'pendientes')>No leidas</option>
                    <option value="leidas" @selected(($filters['estado'] ?? '') === 'leidas')>Leidas</option>
                </select>
            </label>
            <label>Usuario
                <select name="id_usuario">
                    <option value="">Todos</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id_usuario }}" @selected(($filters['id_usuario'] ?? '') == $usuario->id_usuario)>{{ $usuario->nombre_usuario }}</option>
                    @endforeach
                </select>
            </label>
            <label>Desde<input type="date" name="fecha_inicio" value="{{ $filters['fecha_inicio'] ?? '' }}"></label>
            <label>Hasta<input type="date" name="fecha_fin" value="{{ $filters['fecha_fin'] ?? '' }}"></label>
            <button class="btn-primary" type="submit">Filtrar</button>
            <a class="btn-light" href="{{ route('notificaciones.index') }}">Limpiar</a>
        </form>
    </div>
@endif

<div class="cards-container">
    <div class="card"><h3>No leidas</h3><h2>{{ $unreadCount }}</h2></div>
    <div class="card"><h3>Mostradas</h3><h2>{{ $notificaciones->total() }}</h2></div>
</div>

<div class="table-container">
    <h3>Mensajes</h3>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Mensaje</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notificaciones as $notificacion)
                @php($modalId = 'notification-modal-'.$notificacion->id_notificacion)
                <tr>
                    <td>
                        <strong>{{ $notificacion->usuario?->nombre_usuario }}</strong><br>
                        <span class="muted-text">{{ $notificacion->usuario?->role?->nombre_rol }}</span>
                    </td>
                    <td><span class="status-pill">{{ $notificacion->tipo }}</span></td>
                    <td>
                        <strong>{{ $notificacion->titulo }}</strong><br>
                        <span class="muted-text">{{ \Illuminate\Support\Str::limit($notificacion->mensaje, 90) }}</span>
                    </td>
                    <td>{{ $notificacion->leida ? 'Leida' : 'No leida' }}</td>
                    <td>{{ $notificacion->fecha_envio?->format('Y-m-d H:i') }}</td>
                    <td class="actions-cell">
                        <div class="decision-actions">
                            <button class="btn-light js-open-modal" type="button" data-modal-target="{{ $modalId }}">Ver</button>

                            @if(! $notificacion->leida)
                                <form class="action-mini" action="{{ route('notificaciones.read', $notificacion) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-primary" type="submit">Leida</button>
                                </form>
                            @elseif($canManage)
                                <form class="action-mini" action="{{ route('notificaciones.unread', $notificacion) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-light" type="submit">No leida</button>
                                </form>
                            @endif

                            <form class="action-mini" action="{{ route('notificaciones.destroy', $notificacion) }}" method="POST" data-confirm="Eliminar esta notificacion?">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger btn-danger-soft" type="submit">Eliminar</button>
                            </form>
                        </div>

                        <div class="analysis-modal app-modal" id="{{ $modalId }}" hidden>
                            <div class="analysis-modal__backdrop" data-modal-close></div>
                            <section class="analysis-modal__panel" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
                                <header class="analysis-modal__header">
                                    <div>
                                        <span>{{ $notificacion->tipo }}</span>
                                        <h2 id="{{ $modalId }}-title">{{ $notificacion->titulo }}</h2>
                                        <p>{{ $notificacion->fecha_envio?->format('Y-m-d H:i') }} - {{ $notificacion->leida ? 'Leida' : 'No leida' }}</p>
                                    </div>
                                    <button class="modal-close" type="button" aria-label="Cerrar" data-modal-close>&times;</button>
                                </header>
                                <div class="analysis-modal__body">
                                    <article class="analysis-card analysis-card--wide">
                                        <span>Mensaje</span>
                                        <p>{{ $notificacion->mensaje }}</p>
                                    </article>
                                    <div class="analysis-modal__footer">
                                        <button class="btn-primary" type="button" data-modal-close>Cerrar</button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Sin notificaciones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $notificaciones->links() }}
</div>
@endsection
