<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Nomina;
use App\Models\Postulacion;
use App\Models\SolicitudVacacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReporteController extends Controller
{
    private const MODULOS = [
        'resumen' => 'Resumen general',
        'empleados' => 'Empleados',
        'nomina' => 'Nomina',
        'postulaciones' => 'Postulaciones',
        'vacaciones' => 'Vacaciones',
    ];

    private const PERIODOS = [
        'semanal' => 'Semanal',
        'mensual' => 'Mensual',
        'trimestral' => 'Trimestral',
        'anual' => 'Anual',
        'personalizado' => 'Personalizado',
    ];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        [$start, $end] = $this->dateRange($filters);
        $module = $filters['modulo'];
        $summary = $this->summary($filters, $start, $end);
        $detail = $module === 'resumen'
            ? collect()
            : $this->buildQuery($module, $filters, $start, $end)->paginate(12)->withQueryString();
        [$trendLabels, $trendData, $trendLabel] = $this->trendData($module, $filters, $start, $end);

        return view('modules.reportes.index', [
            'filters' => $filters,
            'periodos' => self::PERIODOS,
            'modulos' => self::MODULOS,
            'estados' => $this->states(),
            'departamentos' => Departamento::orderBy('nombre_departamento')->get(),
            'start' => $start,
            'end' => $end,
            'summary' => $summary,
            'detail' => $detail,
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
            'trendLabel' => $trendLabel,
            'summaryLabels' => ['Empleados', 'Nominas', 'Postulaciones', 'Solicitudes'],
            'summaryData' => [
                $summary['empleados_ingresados'],
                $summary['nominas_generadas'],
                $summary['postulaciones'],
                $summary['solicitudes'],
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        [$start, $end] = $this->dateRange($filters);
        $module = $filters['modulo'];
        $filename = 'reporte_'.$module.'_'.$start->format('Ymd').'_al_'.$end->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($module, $filters, $start, $end) {
            echo "\xEF\xBB\xBF";
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->csvHeaders($module));

            foreach ($this->csvRows($module, $filters, $start, $end) as $row) {
                fputcsv($handle, array_values($row));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filters(Request $request): array
    {
        $module = $request->input('modulo', 'resumen');
        $period = $request->input('periodo', 'mensual');
        $year = (int) $request->input('anio', now()->year);
        $quarter = (int) $request->input('trimestre', now()->quarter);
        $month = $request->input('mes', now()->format('Y-m'));

        return [
            'modulo' => array_key_exists($module, self::MODULOS) ? $module : 'resumen',
            'periodo' => array_key_exists($period, self::PERIODOS) ? $period : 'mensual',
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'semana_fecha' => $request->input('semana_fecha', now()->toDateString()),
            'mes' => preg_match('/^\d{4}-\d{2}$/', (string) $month) ? $month : now()->format('Y-m'),
            'anio' => $year >= 2000 && $year <= 2100 ? $year : now()->year,
            'trimestre' => max(1, min(4, $quarter)),
            'id_departamento' => $request->input('id_departamento'),
            'estado' => $request->input('estado'),
            'buscar' => trim((string) $request->input('buscar')),
        ];
    }

    private function dateRange(array $filters): array
    {
        return match ($filters['periodo']) {
            'semanal' => [
                Carbon::parse($filters['semana_fecha'])->startOfWeek(Carbon::MONDAY)->startOfDay(),
                Carbon::parse($filters['semana_fecha'])->endOfWeek(Carbon::SUNDAY)->endOfDay(),
            ],
            'trimestral' => [
                Carbon::create($filters['anio'], (($filters['trimestre'] - 1) * 3) + 1, 1)->startOfDay(),
                Carbon::create($filters['anio'], (($filters['trimestre'] - 1) * 3) + 1, 1)->addMonths(2)->endOfMonth()->endOfDay(),
            ],
            'anual' => [
                Carbon::create($filters['anio'], 1, 1)->startOfDay(),
                Carbon::create($filters['anio'], 12, 31)->endOfDay(),
            ],
            'personalizado' => [
                Carbon::parse($filters['fecha_inicio'] ?: now()->startOfMonth())->startOfDay(),
                Carbon::parse($filters['fecha_fin'] ?: now()->endOfMonth())->endOfDay(),
            ],
            default => [
                Carbon::createFromFormat('Y-m', $filters['mes'])->startOfMonth()->startOfDay(),
                Carbon::createFromFormat('Y-m', $filters['mes'])->endOfMonth()->endOfDay(),
            ],
        };
    }

    private function summary(array $filters, Carbon $start, Carbon $end): array
    {
        $employees = $this->buildQuery('empleados', $filters, $start, $end);
        $payroll = $this->buildQuery('nomina', $filters, $start, $end);
        $applications = $this->buildQuery('postulaciones', $filters, $start, $end);
        $requests = $this->buildQuery('vacaciones', $filters, $start, $end);

        return [
            'empleados_ingresados' => (clone $employees)->count(),
            'nominas_generadas' => (clone $payroll)->count(),
            'total_nomina' => (float) (clone $payroll)->sum('total_pagar'),
            'postulaciones' => (clone $applications)->count(),
            'solicitudes' => (clone $requests)->count(),
            'vacaciones_aprobadas' => (clone $requests)->where('estado', 'APROBADA')->count(),
        ];
    }

    private function buildQuery(string $module, array $filters, Carbon $start, Carbon $end)
    {
        return match ($module) {
            'empleados' => $this->employeesQuery($filters, $start, $end),
            'nomina' => $this->payrollQuery($filters, $start, $end),
            'postulaciones' => $this->applicationsQuery($filters, $start, $end),
            'vacaciones' => $this->vacationQuery($filters, $start, $end),
            default => $this->employeesQuery($filters, $start, $end),
        };
    }

    private function employeesQuery(array $filters, Carbon $start, Carbon $end)
    {
        $query = Empleado::with('departamento')
            ->whereBetween('fecha_ingreso', [$start->toDateString(), $end->toDateString()])
            ->latest('fecha_ingreso');

        if (! empty($filters['id_departamento'])) {
            $query->where('id_departamento', $filters['id_departamento']);
        }

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if ($filters['buscar'] !== '') {
            $search = $filters['buscar'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombres', 'like', '%'.$search.'%')
                    ->orWhere('apellidos', 'like', '%'.$search.'%')
                    ->orWhere('cedula', 'like', '%'.$search.'%')
                    ->orWhere('cargo', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }

    private function payrollQuery(array $filters, Carbon $start, Carbon $end)
    {
        $query = Nomina::with('empleado.departamento')
            ->whereBetween('periodo_inicio', [$start->toDateString(), $end->toDateString()])
            ->latest('periodo_inicio');

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        $this->employeeRelationFilters($query, $filters);

        return $query;
    }

    private function applicationsQuery(array $filters, Carbon $start, Carbon $end)
    {
        $query = Postulacion::with('candidato', 'vacante.departamento')
            ->whereBetween('fecha_postulacion', [$start->toDateString(), $end->toDateString()])
            ->latest('fecha_postulacion');

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (! empty($filters['id_departamento'])) {
            $query->whereHas('vacante', fn ($vacanteQuery) => $vacanteQuery->where('id_departamento', $filters['id_departamento']));
        }

        if ($filters['buscar'] !== '') {
            $search = $filters['buscar'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('candidato', function ($candidateQuery) use ($search) {
                    $candidateQuery->where('nombres', 'like', '%'.$search.'%')
                        ->orWhere('apellidos', 'like', '%'.$search.'%')
                        ->orWhere('correo', 'like', '%'.$search.'%');
                })->orWhereHas('vacante', fn ($vacanteQuery) => $vacanteQuery->where('titulo', 'like', '%'.$search.'%'));
            });
        }

        return $query;
    }

    private function vacationQuery(array $filters, Carbon $start, Carbon $end)
    {
        $query = SolicitudVacacion::with('empleado.departamento')
            ->whereBetween('fecha_solicitud', [$start, $end])
            ->latest('fecha_solicitud');

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        $this->employeeRelationFilters($query, $filters);

        return $query;
    }

    private function employeeRelationFilters($query, array $filters): void
    {
        if (! empty($filters['id_departamento'])) {
            $query->whereHas('empleado', fn ($employeeQuery) => $employeeQuery->where('id_departamento', $filters['id_departamento']));
        }

        if ($filters['buscar'] !== '') {
            $search = $filters['buscar'];
            $query->whereHas('empleado', function ($employeeQuery) use ($search) {
                $employeeQuery->where('nombres', 'like', '%'.$search.'%')
                    ->orWhere('apellidos', 'like', '%'.$search.'%')
                    ->orWhere('cedula', 'like', '%'.$search.'%')
                    ->orWhere('cargo', 'like', '%'.$search.'%');
            });
        }
    }

    private function trendData(string $module, array $filters, Carbon $start, Carbon $end): array
    {
        if ($module === 'resumen') {
            $summary = $this->summary($filters, $start, $end);

            return [
                ['Empleados', 'Nominas', 'Postulaciones', 'Solicitudes'],
                [
                    $summary['empleados_ingresados'],
                    $summary['nominas_generadas'],
                    $summary['postulaciones'],
                    $summary['solicitudes'],
                ],
                'Cantidad',
            ];
        }

        $format = $start->diffInDays($end) > 45 ? 'Y-m' : 'Y-m-d';
        $items = $this->buildQuery($module, $filters, $start, $end)->get();
        $groups = $items->groupBy(fn ($item) => $this->dateForModule($module, $item)?->format($format) ?? 'Sin fecha')->sortKeys();

        return [
            $groups->keys()->values(),
            $groups->map(fn ($rows) => $module === 'nomina' ? (float) $rows->sum('total_pagar') : $rows->count())->values(),
            $module === 'nomina' ? 'Total nomina' : 'Registros',
        ];
    }

    private function dateForModule(string $module, $item): ?Carbon
    {
        return match ($module) {
            'empleados' => $item->fecha_ingreso,
            'nomina' => $item->periodo_inicio,
            'postulaciones' => $item->fecha_postulacion,
            'vacaciones' => $item->fecha_solicitud,
            default => null,
        };
    }

    private function csvRows(string $module, array $filters, Carbon $start, Carbon $end): Collection
    {
        if ($module === 'resumen') {
            $summary = $this->summary($filters, $start, $end);

            return collect([
                ['Indicador' => 'Empleados ingresados', 'Valor' => $summary['empleados_ingresados']],
                ['Indicador' => 'Nominas generadas', 'Valor' => $summary['nominas_generadas']],
                ['Indicador' => 'Total nomina', 'Valor' => number_format($summary['total_nomina'], 2, '.', '')],
                ['Indicador' => 'Postulaciones', 'Valor' => $summary['postulaciones']],
                ['Indicador' => 'Solicitudes vacaciones/permisos', 'Valor' => $summary['solicitudes']],
                ['Indicador' => 'Vacaciones aprobadas', 'Valor' => $summary['vacaciones_aprobadas']],
            ]);
        }

        return $this->buildQuery($module, $filters, $start, $end)->get()->map(fn ($item) => $this->csvRow($module, $item));
    }

    private function csvHeaders(string $module): array
    {
        return match ($module) {
            'empleados' => ['Empleado', 'Cedula', 'Departamento', 'Cargo', 'Salario', 'Ingreso', 'Estado'],
            'nomina' => ['Empleado', 'Departamento', 'Periodo', 'Salario', 'Extras', 'Descuentos', 'Total', 'Estado'],
            'postulaciones' => ['Candidato', 'Correo', 'Vacante', 'Departamento', 'Fecha', 'Estado'],
            'vacaciones' => ['Empleado', 'Departamento', 'Tipo', 'Inicio', 'Fin', 'Solicitud', 'Estado'],
            default => ['Indicador', 'Valor'],
        };
    }

    private function csvRow(string $module, $item): array
    {
        return match ($module) {
            'empleados' => [
                'Empleado' => $item->nombres.' '.$item->apellidos,
                'Cedula' => $item->cedula,
                'Departamento' => $item->departamento?->nombre_departamento,
                'Cargo' => $item->cargo,
                'Salario' => number_format((float) $item->salario_base, 2, '.', ''),
                'Ingreso' => $item->fecha_ingreso?->format('Y-m-d'),
                'Estado' => $item->estado,
            ],
            'nomina' => [
                'Empleado' => $item->empleado?->nombres.' '.$item->empleado?->apellidos,
                'Departamento' => $item->empleado?->departamento?->nombre_departamento,
                'Periodo' => $item->periodo_inicio?->format('Y-m'),
                'Salario' => number_format((float) $item->salario_base, 2, '.', ''),
                'Extras' => number_format((float) $item->monto_horas_extra, 2, '.', ''),
                'Descuentos' => number_format((float) $item->descuentos, 2, '.', ''),
                'Total' => number_format((float) $item->total_pagar, 2, '.', ''),
                'Estado' => $item->estado,
            ],
            'postulaciones' => [
                'Candidato' => $item->candidato?->nombres.' '.$item->candidato?->apellidos,
                'Correo' => $item->candidato?->correo,
                'Vacante' => $item->vacante?->titulo,
                'Departamento' => $item->vacante?->departamento?->nombre_departamento,
                'Fecha' => $item->fecha_postulacion?->format('Y-m-d'),
                'Estado' => $item->estado,
            ],
            default => [
                'Empleado' => $item->empleado?->nombres.' '.$item->empleado?->apellidos,
                'Departamento' => $item->empleado?->departamento?->nombre_departamento,
                'Tipo' => $item->tipo_solicitud,
                'Inicio' => $item->fecha_inicio?->format('Y-m-d'),
                'Fin' => $item->fecha_fin?->format('Y-m-d'),
                'Solicitud' => $item->fecha_solicitud?->format('Y-m-d H:i'),
                'Estado' => $item->estado,
            ],
        };
    }

    private function states(): array
    {
        return [
            'ACTIVO',
            'INACTIVO',
            'BORRADOR',
            'PENDIENTE',
            'APROBADA',
            'PAGADA',
            'RECIBIDA',
            'RECHAZADA',
            'CONTRATADO',
            'DESCARTADO',
        ];
    }
}
