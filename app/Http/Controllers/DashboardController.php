<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Empleado;
use App\Models\Notificacion;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\SolicitudVacacion;
use App\Models\Vacante;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return match (auth()->user()?->role?->nombre_rol) {
            'ADMINISTRADOR' => redirect()->route('dashboard.admin'),
            'RRHH' => redirect()->route('dashboard.rrhh'),
            'EMPLEADO' => redirect()->route('dashboard.empleado'),
            default => redirect()->route('login'),
        };
    }

    public function admin()
    {
        return view('dashboard.admin', [
            'usuarios' => User::count(),
            'usuariosActivos' => User::where('estado', 'ACTIVO')->count(),
            'roles' => DB::table('tbl_roles')->count(),
            'departamentos' => DB::table('tbl_departamentos')->count(),
            'auditorias' => Auditoria::count(),
            'actividadReciente' => Auditoria::latest('fecha_evento')->take(8)->get(),
        ]);
    }

    public function rrhh()
    {
        $departamentos = DB::table('tbl_empleados')
            ->join('tbl_departamentos', 'tbl_empleados.id_departamento', '=', 'tbl_departamentos.id_departamento')
            ->select('tbl_departamentos.nombre_departamento', DB::raw('COUNT(tbl_empleados.id_empleado) as total'))
            ->groupBy('tbl_departamentos.nombre_departamento')
            ->get(); 

        $contrataciones = DB::table('tbl_empleados')
            ->select(DB::raw('MONTH(fecha_ingreso) as mes'), DB::raw('COUNT(id_empleado) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return view('dashboard.rrhh', [
            'empleados' => Empleado::where('estado', '!=', 'TERMINADO')->count(),
            'vacantes' => Vacante::count(),
            'solicitudes' => SolicitudVacacion::count(),
            'postulaciones' => Postulacion::count(),
            'ultimosEmpleados' => Empleado::with('departamento')->where('estado', '!=', 'TERMINADO')->latest('id_empleado')->take(5)->get(),
            'departamentos' => $departamentos,
            'contrataciones' => $contrataciones,
        ]);
    }

    public function empleado()
    {
        $user = auth()->user();
        $empleado = $user?->empleado;

        return view('dashboard.empleado', [
            'empleado' => $empleado,
            'nominas' => $empleado ? $empleado->nominas()->latest('fecha_generacion')->take(6)->get() : collect(),
            'notificaciones' => Notificacion::where('id_usuario', $user?->id_usuario)->latest('fecha_envio')->take(5)->get(),
            'solicitudes' => $empleado
                ? SolicitudVacacion::where('id_empleado', $empleado->id_empleado)->latest('fecha_solicitud')->take(5)->get()
                : collect(),
        ]);
    }
}
