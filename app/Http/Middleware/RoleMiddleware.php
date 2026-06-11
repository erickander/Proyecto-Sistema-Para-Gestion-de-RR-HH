<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $normalizedRoles = array_map(function (string $role): string {
            return match (strtolower($role)) {
                'admin' => 'ADMINISTRADOR',
                'rrhh' => 'RRHH',
                'empleado' => 'EMPLEADO',
                default => strtoupper($role),
            };
        }, $roles);

        if (! $user || ! $user->role || ! in_array(strtoupper($user->role->nombre_rol), $normalizedRoles, true)) {
            abort(403, 'No tiene permisos para acceder a este modulo.');
        }

        return $next($request);
    }
}
